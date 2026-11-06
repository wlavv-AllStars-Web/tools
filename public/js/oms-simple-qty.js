document.addEventListener('change', function (event) {
    var input = event.target;

    if (!input.matches('.js-order-qty')) {
        return;
    }

    // This is registered in capture phase so it runs before the legacy handler,
    // which reloads the page and loses invoice quantities typed in other rows.
    event.stopImmediatePropagation();

    var value = input.value.trim();
    var token = document.querySelector('input[name="_token"]');
    var row = input.closest('.oms-product-row');

    if (!/^\d+$/.test(value)) {
        input.classList.add('is-invalid');
        return;
    }

    input.disabled = true;

    fetch(input.dataset.url, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        credentials: 'same-origin',
        body: new URLSearchParams({
            _token: token ? token.value : '',
            _method: 'PATCH',
            qty_ordered: value
        }).toString()
    })
        .then(function (response) {
            return response.json().then(function (payload) {
                if (!response.ok) {
                    throw new Error(payload.message || 'Unable to update ordered quantity.');
                }

                return payload;
            });
        })
        .then(function (payload) {
            var line = payload.line || {};

            var icons = {
                complete: 'fa-check',
                partial: 'fa-minus',
                zero: 'fa-xmark'
            };
            var setState = function (element, state) {
                if (!element || !state) {
                    return;
                }

                element.className = 'state ' + state;
                element.innerHTML = '<i class="fa-solid ' + icons[state] + '"></i>';
            };

            input.disabled = false;
            input.value = line.ordered;
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
            row.dataset.complete = line.ordered === line.invoiced && line.invoiced === line.received ? '1' : '0';

            setState(row.querySelector('.line-state > .state'), line.states && line.states.line);
            setState(
                row.querySelector('.invoiced-qty').closest('.quantity').querySelector('.state'),
                line.states && line.states.invoice
            );
            setState(
                row.querySelector('.js-received-qty').closest('.quantity').querySelector('.state'),
                line.states && line.states.received
            );

            var invoiceQty = row.querySelector('.invoice-qty');
            if (invoiceQty) {
                invoiceQty.max = line.remaining;
            }
        })
        .catch(function (error) {
            input.disabled = false;
            input.classList.add('is-invalid');

            if (window.Swal) {
                Swal.fire({icon: 'error', text: error.message});
            } else {
                alert(error.message);
            }
        });
}, true);
