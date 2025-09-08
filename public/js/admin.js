// Inicializar los tooltips de Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});

// Validación del formulario de alojamientos
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(event) {
            const priceInput = document.querySelector('#price');
            if (priceInput && priceInput.value) {
                const price = parseFloat(priceInput.value);
                if (isNaN(price) || price <= 0) {
                    event.preventDefault();
                    alert('El precio debe ser un número positivo');
                }
            }
        });
    }
});
