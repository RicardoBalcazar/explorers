// Obtener elementos del DOM
const hamburger = document.getElementById('hamburger');
const sidebar = document.getElementById('sidebar');

// Agregar evento al botón hamburguesa
hamburger.addEventListener('click', () => {
    // Alternar clase activa para el botón y el menú
    hamburger.classList.toggle('active');
    sidebar.classList.toggle('active');
});

// Cerrar el menú al hacer clic fuera de él
document.addEventListener('click', (event) => {
    if (!sidebar.contains(event.target) && !hamburger.contains(event.target)) {
        sidebar.classList.remove('active');
        hamburger.classList.remove('active');
    }
});
