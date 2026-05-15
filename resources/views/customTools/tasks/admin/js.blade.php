<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function () {

    // ====== DataTables ======
    const table = $('#tasksTable').DataTable({
        order: [[0,'desc']],
        pageLength: 25
    });

    // ====== Year + Month Filter ======
    $('#yearFilter, #monthFilter').on('change', function () {
        const year  = $('#yearFilter').val();
        const month = $('#monthFilter').val();
        window.location = '?year=' + year + '&month=' + month;
    });

    // Helper: get row <tr>
    function getRowFromButton(btn){
        return $(btn).closest('tr');
    }

    // ====== VIEW MODAL (AJAX) ======
    $('.viewTaskBtn').on('click', function () {
        const row = getRowFromButton(this);
        const url = row.data('show-url');

        $('#viewTitle').text('Task #' + row.data('task-id') + ' — ' + row.data('title'));
        $('#viewBody').html('<div class="text-muted">Loading...</div>');
        $('#viewFiles').empty();
        $('#viewLogs').html('');

        $.get(url, function (res) {
            // body
            let html = '';
            html += `<p><strong>Team</strong><br>${escapeHtml(res.team || '')}</p>`;
            html += `<p><strong>Description</strong><br>${escapeHtml(res.description || '')}</p>`;

            if (res.observations_user) {
                html += `<p><strong>User notes</strong><br>${escapeHtml(res.observations_user)}</p>`;
            }
            if (res.observations_manager) {
                html += `<p><strong>Manager notes</strong><br>${escapeHtml(res.observations_manager)}</p>`;
            }
            if (res.observations_admin) {
                html += `<p><strong>Admin notes</strong><br>${escapeHtml(res.observations_admin)}</p>`;
            }

            html += `<p><strong>Status</strong><br>${escapeHtml((res.status_admin || '').toUpperCase())}</p>`;

            $('#viewBody').html(html);

            // files
            if (res.files && res.files.length) {
                res.files.forEach(f => {
                    $('#viewFiles').append(`<li>${escapeHtml(f.filename)} — <a href="${f.download_url}">download</a></li>`);
                });
            } else {
                $('#viewFiles').append('<li class="text-muted">No files</li>');
            }

            // logs
            if (res.logs && res.logs.length) {
                let logsHtml = '<div class="list-group">';
                res.logs.forEach(l => {
                    logsHtml += `
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <strong>${escapeHtml(l.user || '—')}</strong>
                                <span class="text-muted">${escapeHtml(l.created_at || '')}</span>
                            </div>
                            <div>${escapeHtml(l.comment || '')}</div>
                            <div class="text-muted small">
                                admin: ${escapeHtml(l.old_status_admin || '—')} → ${escapeHtml(l.new_status_admin || '—')}
                            </div>
                        </div>
                    `;
                });
                logsHtml += '</div>';
                $('#viewLogs').html(logsHtml);
            } else {
                $('#viewLogs').html('<div class="text-muted">No history</div>');
            }
        })
        .fail(function(){
            $('#viewBody').html('<div class="text-danger">Failed to load task details.</div>');
        });
    });

    // ====== EDIT MODAL (data attrs) ======
    $('.editTaskBtn').on('click', function () {
        const row = getRowFromButton(this);

        const taskId = row.data('task-id');
        const updateUrl = row.data('update-url');

        $('#editTaskForm').attr('action', updateUrl);

        $('#editTaskId').val(taskId);
        $('#editTitle').val(row.data('title'));
        $('#editTeam').val(row.data('team-id'));
        $('#editTime').val(row.data('time-allowed'));
        $('#editStatus').val((row.data('status-admin') || 'new').toString().toLowerCase());
        $('#editObservations').val(row.data('observations-admin'));

        // limpar hidden duplicação se já existia
        $('#editTaskForm input[name="duplicate_next_week"]').remove();
    });

    // ====== FAIL confirmation (no change) ======
    $(document).on('change', '#editStatus', function () {
        if ($(this).val() === 'fail') {
            Swal.fire({
                title: 'Duplicate task?',
                text: 'Do you want to automatically create this task for next week?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No'
            }).then(result => {
                $('#editTaskForm input[name="duplicate_next_week"]').remove();

                if (result.isConfirmed) {
                    $('<input>', { type: 'hidden', name: 'duplicate_next_week', value: 1 })
                        .appendTo($('#editTaskForm'));
                }
            });
        } else {
            $('#editTaskForm input[name="duplicate_next_week"]').remove();
        }
    });

    // ====== small helper ======
    function escapeHtml(str) {
        return String(str)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

});
</script>

