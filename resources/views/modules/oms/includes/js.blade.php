<script>

function generateBarcode(invoiced, received, id_product, id_attribute) {
    let item = prompt("Que quantidade pretende imprimir?", "Todas as etiquetas");

    if (item === null || item === "") {
        return;
    }

    const remaining = Math.max(0, Number(invoiced) - Number(received));

    if (item === 'Todas as etiquetas') {
        item = remaining > 0 ? remaining : invoiced;
    }

    item = parseInt(item, 10);

    if (Number.isNaN(item) || item <= 0) {
        alert('Quantidade inválida.');
        return;
    }

    window.open(
        '/barcode/product/print/' + id_product + '/' + (id_attribute || 0) + '/' + item,
        '_blank'
    );
}
</script>