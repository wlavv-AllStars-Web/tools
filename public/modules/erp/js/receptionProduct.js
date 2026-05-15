var typingTimer;
var doneTypingInterval = 100;

function keyUpEvent(){
    clearTimeout(typingTimer);
    typingTimer = setTimeout(bareCode, doneTypingInterval);
}

function keyDownEvent(){
    clearTimeout(typingTimer);
}

function setError(){
    const input = document.getElementById('barecode');
    if (!input) return;
    input.style.color = 'rgb(255, 0, 0)';
    input.classList.add('is-invalid');
    input.value = (window.bmsReceptionConfig.errorMessage || 'Erro') + ' ' + input.value;
    const audio = document.getElementById('audio-wrong');
    if (audio) audio.play();
}

function setok(){
    const input = document.getElementById('barecode');
    if (!input) return;
    input.style.color = 'blue';
    input.classList.remove('is-invalid');
    input.classList.add('is-valid');
    const audio = document.getElementById('audio-correct');
    if (audio) audio.play();
}

function reset(){
    const input = document.getElementById('barecode');
    if (!input) return;
    input.style.color = 'rgb(85, 85, 85)';
    input.classList.remove('is-valid', 'is-invalid');
    input.value = '';
    input.focus();
}

function changeQte(qte, sens, id, max){
    qte = parseInt(qte, 10);
    max = parseInt(max, 10);
    var input = document.getElementById(id);
    if (!input) return false;
    var val = parseInt(input.value || '0', 10);
    if (isNaN(val)) val = 0;
    if (isNaN(max)) max = 0;
    if (isNaN(qte)) return false;

    if (qte === 1) {
        switch (sens) {
            case 'inc': input.value = Math.min(max, val + 1); break;
            case 'dec': input.value = Math.max(0, val - 1); break;
        }
    } else {
        input.value = Math.min(max, qte);
    }
}

function markRowState(id) {
    const input = document.getElementById(id);
    if (!input) return;
    const tr = input.closest('tr');
    if (!tr) return;
    const max = parseInt(input.dataset.qteMax || '0', 10);
    const value = parseInt(input.value || '0', 10);
    tr.classList.remove('is-complete', 'is-partial', 'is-pending');
    if (max === 0) {
        tr.classList.add('is-complete');
    } else if (value > 0) {
        tr.classList.add('is-partial');
    } else {
        tr.classList.add('is-pending');
    }
}

function dec(qte,id,max){
    changeQte(qte,'dec',id,max);
    markRowState(id);
    send(id);
}
function inc(qte,id,max){
    changeQte(qte,'inc',id,max);
    markRowState(id);
    send(id);
}

function send(id_po_product){
    fetch(window.bmsReceptionConfig.updateLineQtyUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.bmsReceptionConfig.csrf,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ line_id: id_po_product, value: document.getElementById(id_po_product).value })
    }).then(r => r.json()).then(data => {
        if (data.result) {
            document.getElementById(id_po_product).value = data.value;
            markRowState(id_po_product);
        }
    });
}

function bareCode(){
    const barcode = document.getElementById('barecode');
    if (!barcode || !barcode.value.trim()) return;
    fetch(window.bmsReceptionConfig.scanUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.bmsReceptionConfig.csrf,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ sku: barcode.value })
    }).then(r => r.json()).then(function(data){
        if (!data.result || !data.ip_pop) {
            setError();
        } else {
            setok();
            barcode.value = data.message;
            var max = parseInt(document.getElementById(data.ip_pop).dataset.qteMax || '0', 10);
            inc(1, String(data.ip_pop), max);
        }
        setTimeout(reset, 200);
    }).catch(function(){
        setError();
        setTimeout(reset, 500);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('barecode');
    if (input) input.focus();
    document.querySelectorAll("input[name^='lines'][type='text']").forEach(function(el){
        markRowState(el.id);
        el.addEventListener('change', function(){
            var val = parseInt(el.value, 10);
            if (isNaN(val) || val < 0) el.value = 0;
            markRowState(el.id);
            send(el.id);
        });
    });
});
