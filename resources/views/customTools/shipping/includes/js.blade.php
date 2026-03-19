<script>

    function addRow(element) {
        // Obtém os elementos
        let toClone = document.getElementById("toClone");
        let holderClone = document.getElementById("holderClone");
    
        // Clona o elemento e remove o ID (para evitar duplicação)
        let newRow = toClone.cloneNode(true);
        newRow.style.display = ""; // Torna visível
        newRow.id = ""; // Remove o ID para evitar conflitos
    
        // Substitui o holderClone pelo novo clone
        holderClone.replaceWith(newRow);
    
        // Cria um novo holderClone
        let newHolder = document.createElement("tr");
        newHolder.id = "holderClone";
        newHolder.style.display = "none";
        newHolder.innerHTML = "<td></td>";
    
        // Insere o novo holderClone imediatamente antes de #toClone
        toClone.parentNode.insertBefore(newHolder, toClone);
        
        element.remove();
        
    }
    
</script>