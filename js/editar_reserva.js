document.addEventListener("DOMContentLoaded", function () {
    document.addEventListener("click", function (event) {
        const estadoIndicador = event.target.closest(".estado-indicador");

        if (estadoIndicador) {
            const idReserva = estadoIndicador.getAttribute("data-id-reserva");

            if (!idReserva) {
                console.error("❌ Error: No se encontró el ID de la reserva.");
                return;
            }

            console.log(`🔄 Redirigiendo a edición de reserva ID: ${idReserva}`);
            window.location.href = `editar_reserva.php?id_reserva=${idReserva}`;
        }
    });
});
