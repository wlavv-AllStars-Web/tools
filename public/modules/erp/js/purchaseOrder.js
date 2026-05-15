(function(){
    const saveUrl = window.location.pathname.replace(/\/$/, '') + '/line-price';
    document.querySelectorAll('.js-wholesale-price').forEach(function(input){
        let timeout;
        input.addEventListener('input', function(){
            input.classList.remove('is-valid','is-invalid');
            clearTimeout(timeout);
            timeout = setTimeout(function(){
                fetch(saveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type':'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({line_id: input.dataset.lineId, price: input.value})
                }).then(r => r.json()).then(function(resp){
                    input.classList.add(resp && resp.ok ? 'is-valid' : 'is-invalid');
                }).catch(function(){ input.classList.add('is-invalid'); });
            }, 350);
        });
    });
})();
