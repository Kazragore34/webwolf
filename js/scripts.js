// Función para crear layout masonry
function createMasonryLayout() {
    const container = document.getElementById('masonry-container');
    const items = Array.from(container.children);
    
    // Calcula el ancho del contenedor
    const containerWidth = container.offsetWidth;
    const columnCount = Math.floor(containerWidth / 250); // Aproximadamente 250px por columna
    const columnWidth = containerWidth / columnCount;
    
    // Inicializa columnas
    const columns = [];
    for (let i = 0; i < columnCount; i++) {
        columns[i] = 0;
    }
    
    // Posiciona cada elemento
    items.forEach(item => {
        // Determina la orientación
        const isPortrait = item.classList.contains('portrait');
        const aspectRatio = isPortrait ? 3/4 : 4/3;
        
        // Calcula altura basada en la proporción
        const width = columnWidth - 15; // Restamos el gap
        const height = width / aspectRatio;
        
        // Encuentra la columna con menor altura
        let minColumnIndex = 0;
        let minHeight = columns[0];
        
        for (let i = 1; i < columns.length; i++) {
            if (columns[i] < minHeight) {
                minHeight = columns[i];
                minColumnIndex = i;
            }
        }
        
        // Aplica posición absoluta
        item.style.position = 'absolute';
        item.style.left = `${minColumnIndex * columnWidth}px`;
        item.style.top = `${columns[minColumnIndex]}px`;
        item.style.width = `${width}px`;
        item.style.height = `${height}px`;
        
        // Actualiza la altura de la columna
        columns[minColumnIndex] += height + 15; // Sumamos el gap
    });
    
    // Ajusta la altura del contenedor
    const maxHeight = Math.max(...columns);
    container.style.height = `${maxHeight}px`;
}

// Ejecuta el layout masonry cuando la página carga y cuando cambia de tamaño
window.addEventListener('load', createMasonryLayout);
window.addEventListener('resize', createMasonryLayout);

// Menú responsive
const hamburger = document.querySelector('.hamburger');
const navMenu = document.querySelector('.nav-menu');

hamburger.addEventListener('click', () => {
    hamburger.classList.toggle('active');
    navMenu.classList.toggle('active');
});

// Cerrar menú al hacer clic en un enlace
document.querySelectorAll('.nav-link').forEach(n => n.addEventListener('click', () => {
    hamburger.classList.remove('active');
    navMenu.classList.remove('active');
}));