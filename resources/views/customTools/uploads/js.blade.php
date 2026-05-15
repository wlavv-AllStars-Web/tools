<script>

    Dropzone.autoDiscover = true;
(function() {
    let found = false;
    
    const observer = new MutationObserver((mutationsList, observer) => {
      const element = document.querySelector(".dz-success-mark");
    
      if (found && !element) found = false;
    
      if (element && !found) {
        executeFunction();
        observer.disconnect();
        found = true;
      }
      
    });
    
    observer.observe(document.body, { childList: true, subtree: true });
})();
</script>