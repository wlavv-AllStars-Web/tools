<style>
    tr.quoted-row td { background-color: #d4edda !important; }
    .actions-col button { margin-right: 6px; }
    .dt-inline-input { width: 100%; box-sizing: border-box; }
    .sorting{ text-align: center !important; }
    .readonly-cell { opacity: 0.90; }
</style>

<script>
    $(function () {
        const csrf = $('meta[name="csrf-token"]').attr('content');

        const DATA_URL  = "{{ url('customTools/quotes/data') }}";
        const STORE_URL = "{{ url('customTools/quotes') }}";

        function esc(v) {
            if (v === null || v === undefined) return '';
            return String(v)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function formatPrice(v) {
            if (v === null || v === undefined || v === '') return '';
            const n = Number(v);
            if (Number.isNaN(n)) return '';
            return n.toFixed(2);
        }

        function isEmptyValue(v) {
            return v === null || v === undefined || String(v).trim() === '';
        }

        function viewCell(field, value) {
            return `<span class="cell-view" style="text-align: left !important;" data-field="${field}">${esc(value)}</span>`;
        }

        // NOVO: só cria input se o valor estiver vazio; se não, mostra texto (read-only)
        function editCellOnlyIfEmpty(field, value, type) {
            const empty = isEmptyValue(value);

            if (!empty) {
                // read-only (texto)
                return `<span class="cell-view readonly-cell" style="text-align: left !important;" data-field="${field}">${esc(value)}</span>`;
            }

            // vazio -> cria input editável
            if (type === 'textarea') {
                return `<textarea class="dt-inline-input" data-field="${field}" rows="2"></textarea>`;
            }

            // Para price deixamos vazio e o utilizador preenche
            const v = '';
            return `<input class="dt-inline-input" data-field="${field}" type="text" value="${esc(v)}">`;
        }

        function actionButtons(row) {
            const isQuoted = row.status === 'quoted';
            const delDisabled = isQuoted ? 'disabled' : '';

            return `
                <button class="btn btn-sm btn-primary js-edit" type="button"><i class="fa-solid fa-pen"></i></button>
                <button class="btn btn-sm btn-success js-save d-none" type="button"><i class="fa-regular fa-floppy-disk"></i></button>
                <button class="btn btn-sm btn-secondary js-cancel d-none" type="button"><i class="fa-solid fa-xmark"></i></button>
                <button class="btn btn-sm btn-danger js-delete" type="button" ${delDisabled}><i class="fa-solid fa-trash"></i></button>
            `;
        }

        function toggleButtons($tr, editing) {
            $tr.find('.js-edit').toggleClass('d-none', editing);
            $tr.find('.js-save').toggleClass('d-none', !editing);
            $tr.find('.js-cancel').toggleClass('d-none', !editing);
        }

        // NOVO: lê apenas os inputs/textarea (que só existem nos campos vazios)
        function readOnlyEmptyInputs($tr) {
            const obj = {};
            $tr.find('.dt-inline-input').each(function () {
                const field = this.getAttribute('data-field');
                const v = (this.value ?? '').trim();
                obj[field] = v === '' ? null : v;
            });
            return obj;
        }

        function swapToEditMode($tr, rowData) {
            const map = [
                ['id','text'],
                ['brand','text'],
                ['referencia','text'],
                ['notas_front','text'],
                ['price','text'],
                ['lead','text'],
                ['notas_back','text'],
            ];

            const tds = $tr.children('td');
            map.forEach((pair, idx) => {
                const [field, type] = pair;

                // valor atual
                let current = rowData[field];

                // formatação para price (quando existe)
                if (field === 'price') current = formatPrice(current);

                // aplica regra: só edita se estiver vazio
                tds.eq(idx).html(editCellOnlyIfEmpty(field, current ?? '', type));
            });

            toggleButtons($tr, true);
        }

        function swapToViewMode($tr, rowData) {
            const map = ['id', 'brand','referencia','notas_front','price','lead','notas_back'];
            const tds = $tr.children('td');

            map.forEach((field, idx) => {
                const v = (field === 'price') ? formatPrice(rowData[field]) : (rowData[field] ?? '');
                tds.eq(idx).html(viewCell(field, v));
            });

            tds.eq(7).html(actionButtons(rowData));

            if (rowData.status === 'quoted') $tr.addClass('quoted-row');
            else $tr.removeClass('quoted-row');

            toggleButtons($tr, false);
        }

        function extractAjaxError(xhr) {
            try {
                const r = xhr.responseJSON || {};
                if (r.message) return r.message;
                if (r.errors) {
                    const firstKey = Object.keys(r.errors)[0];
                    return r.errors[firstKey][0] || 'Erro de validação.';
                }
            } catch (_) {}
            return 'Ocorreu um erro inesperado.';
        }

        function clearCreateInputs() {
            $('#new_brand, #new_referencia, #new_notas_front, #new_price, #new_lead, #new_notas_back').val('');
        }

        // DataTables init (lista)
        const table = $('#quotesTable').DataTable({
            ajax: DATA_URL,
            processing: true,
            order: [[0, 'desc']],
            columns: [
                { data: 'id', render: (d,t,r) => viewCell('id', d) },
                { data: 'brand', render: (d,t,r) => viewCell('brand', d) },
                { data: 'referencia', render: (d,t,r) => viewCell('referencia', d) },
                { data: 'notas_front', render: (d,t,r) => viewCell('notas_front', d) },
                { data: 'price', render: (d,t,r) => viewCell('price', formatPrice(d)) },
                { data: 'lead', render: (d,t,r) => viewCell('lead', d) },
                { data: 'notas_back', render: (d,t,r) => viewCell('notas_back', d) },
                { data: null, orderable: false, searchable: false, render: (d,t,r) => actionButtons(r) },
            ],
            rowCallback: function (row, data) {
                if (data.status === 'quoted') row.classList.add('quoted-row');
                else row.classList.remove('quoted-row');
            }
        });

        // Create
        $(document).on('click', '#btnClear', function (e) {
            e.preventDefault();
            clearCreateInputs();
        });

        $(document).on('click', '#btnCreate', function (e) {
            e.preventDefault();

            const payload = {
                brand: ($('#new_brand').val() || '').trim(),
                referencia: ($('#new_referencia').val() || '').trim(),
                notas_front: ($('#new_notas_front').val() || '').trim(),
                price: ($('#new_price').val() || '').trim(),
                lead: ($('#new_lead').val() || '').trim(),
                notas_back: ($('#new_notas_back').val() || '').trim(),
            };

            if (!payload.referencia || !payload.brand) {
                Swal.fire('Validação', 'Referencia e Brand são obrigatórios.', 'warning');
                return;
            }

            if (payload.price === '') payload.price = null;

            $.ajax({
                url: STORE_URL,
                method: "POST",
                headers: { 'X-CSRF-TOKEN': csrf },
                data: payload
            })
            .done(function () {
                clearCreateInputs();
                table.ajax.reload(null, false);
            })
            .fail(function (xhr) {
                Swal.fire('Erro', extractAjaxError(xhr), 'error');
            });
        });

        // Edit (agora só deixa editar campos vazios)
        $('#quotesTable tbody').on('click', '.js-edit', function () {
            const $tr = $(this).closest('tr');
            const row = table.row($tr);
            const data = row.data();

            $tr.data('snapshot', JSON.parse(JSON.stringify(data)));
            swapToEditMode($tr, data);
        });

        // Cancel (volta ao snapshot)
        $('#quotesTable tbody').on('click', '.js-cancel', function () {
            const $tr = $(this).closest('tr');
            const snap = $tr.data('snapshot');
            if (!snap) return;

            table.row($tr).data(snap).invalidate();
            swapToViewMode($tr, snap);
        });

        // Save (merge: só preenche campos vazios que o user escreveu)
        $('#quotesTable tbody').on('click', '.js-save', function () {
            const $tr = $(this).closest('tr');
            const row = table.row($tr);
            const current = row.data();

            const editedEmptyFields = readOnlyEmptyInputs($tr);

            // MERGE: mantém valores existentes e aplica apenas os preenchidos agora
            const payload = {
                id: current.id,
                brand: current.brand,
                referencia: current.referencia,
                notas_front: current.notas_front,
                price: current.price,
                lead: current.lead,
                notas_back: current.notas_back,
            };

            Object.keys(editedEmptyFields).forEach(k => {
                if (!isEmptyValue(editedEmptyFields[k])) {
                    payload[k] = editedEmptyFields[k];
                }
            });

            if (isEmptyValue(payload.referencia) || isEmptyValue(payload.brand)) {
                Swal.fire('Validação', 'Referencia e Brand são obrigatórios.', 'warning');
                return;
            }

            $.ajax({
                url: STORE_URL + '/' + current.id,
                method: "PUT",
                headers: { 'X-CSRF-TOKEN': csrf },
                data: payload
            })
            .done(function (res) {
                const updated = res.quote ?? res;
                row.data(updated).invalidate();
                swapToViewMode($tr, updated);
            })
            .fail(function (xhr) {
                Swal.fire('Erro', extractAjaxError(xhr), 'error');
            });
        });

        // Delete
        $('#quotesTable tbody').on('click', '.js-delete', async function () {
            const $tr = $(this).closest('tr');
            const row = table.row($tr);
            const data = row.data();

            if (data.status === 'quoted') {
                Swal.fire('Bloqueado', 'Não é possível remover um pedido com estado quoted.', 'info');
                return;
            }

            const confirm = await Swal.fire({
                title: 'Confirmar remoção',
                text: 'Pretende remover esta linha?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sim, remover',
                cancelButtonText: 'Cancelar'
            });

            if (!confirm.isConfirmed) return;

            $.ajax({
                url: STORE_URL + '/' + data.id,
                method: "DELETE",
                headers: { 'X-CSRF-TOKEN': csrf }
            })
            .done(function () {
                row.remove().draw(false);
            })
            .fail(function (xhr) {
                Swal.fire('Erro', extractAjaxError(xhr), 'error');
            });
        });
    });
</script>
