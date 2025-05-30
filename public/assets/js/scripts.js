document.addEventListener('DOMContentLoaded', function() {
    // Validación de formulario de entrada
    const formEntrada = document.querySelector('form');
    if (formEntrada) {
        formEntrada.addEventListener('submit', function(e) {
            const placa = document.getElementById('placa').value.trim();
            if (!placa) {
                e.preventDefault();
                alert('Por favor ingrese la placa del vehículo');
                return false;
            }
            return true;
        });
    }
    
    // Auto-mayúsculas para placas
    const placaInput = document.getElementById('placa');
    if (placaInput) {
        placaInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }
    
    // Mostrar/ocultar campos de cliente según DNI
    const dniCliente = document.getElementById('dni_cliente');
    if (dniCliente) {
        dniCliente.addEventListener('input', function() {
            const nombreCliente = document.getElementById('nombre_cliente');
            const apellidoCliente = document.getElementById('apellido_cliente');
            
            if (this.value.trim()) {
                nombreCliente.required = true;
                apellidoCliente.required = true;
            } else {
                nombreCliente.required = false;
                apellidoCliente.required = false;
            }
        });
    }
    
    document.getElementById('buscarPlaca')?.addEventListener('click', function() {
        const placa = document.getElementById('placa').value.trim();
        if (!placa) return alert('Ingrese una placa');
        fetch('ajax-buscar.php?placa=' + encodeURIComponent(placa))
            .then(r => r.json())
            .then(data => {
                // Para vehículo
                if (data && data.placa) {
                    document.getElementById('marca').value = data.marca;
                    document.getElementById('modelo').value = data.modelo;
                    document.getElementById('color').value = data.color;
                    document.getElementById('tipo').value = data.tipo;
                } else {
                    alert('Vehículo no encontrado');
                }
            });
    });
    
    document.getElementById('buscarDni')?.addEventListener('click', function() {
        const dni = document.getElementById('dni_cliente').value.trim();
        if (!dni) return alert('Ingrese un DNI');
        fetch('ajax-buscar.php?dni=' + encodeURIComponent(dni))
            .then(r => r.json())
            .then(data => {
                // Para cliente
                if (data && data.dni) {
                    document.getElementById('nombre_cliente').value = data.nombre;
                    document.getElementById('apellido_cliente').value = data.apellido;
                } else {
                    alert('Cliente no encontrado');
                }
            });
    });
});