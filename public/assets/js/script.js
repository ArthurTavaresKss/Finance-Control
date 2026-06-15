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