function mostrarCampoNovaCategoria() {
    const select = document.getElementById('categoria');
    const inputNova = document.getElementById('nova_categoria');

    if (select.value === 'nova_categoria') {
        inputNova.style.display = 'block';
        inputNova.required = true;
        select.required = false;
    } else {
        inputNova.style.display = 'none';
        inputNova.required = false;
        select.required = true;
    }
}
function aplicarMascaraMesAno(idInput) {
    const inputData = document.getElementById(idInput);
    if (!inputData) return;

    inputData.addEventListener('input', function(e) {
        let v = e.target.value.replace(/\D/g, '');
        
        if (v.length > 2) {
            v = v.substring(0, 2) + '/' + v.substring(2, 6);
        } else if (v.length === 2 && e.inputType === 'deleteContentBackward') {
            v = v.substring(0, 2);
        } else if (v.length === 2) {
            v = v + '/';
        }

        e.target.value = v;
    });

    inputData.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace') {
            const start = this.selectionStart;
            if (start === 3) {
                e.preventDefault();
                this.value = this.value.substring(0, 1);
            }
        }
    });
}