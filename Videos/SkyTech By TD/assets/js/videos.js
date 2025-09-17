/**
 * JAVASCRIPT MINIMALISTA PARA VIDEOS - SkyTech By TD
 * Funciones básicas para módulos acordeón y modal de videos
 */

/**
 * Toggle de módulos acordeón
 * @param {number} moduleId - ID del módulo a expandir/contraer
 */
function toggleModule(moduleId) {
    const header = event.currentTarget;
    const content = document.getElementById('module-' + moduleId);
    
    header.classList.toggle('open');
    content.classList.toggle('open');
}

/**
 * Abrir modal con video
 * @param {string} url - URL del video de Vimeo
 * @param {string} title - Título del video
 */
function playVideo(url, title) {
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('videoFrame').src = url + '?autoplay=1';
    document.getElementById('videoModal').classList.add('active');
}

/**
 * Cerrar modal de video
 */
function closeModal() {
    document.getElementById('videoModal').classList.remove('active');
    document.getElementById('videoFrame').src = '';
}

/**
 * Inicialización de eventos
 */
document.addEventListener('DOMContentLoaded', function() {
    // Cerrar modal al hacer clic fuera del contenido
    document.getElementById('videoModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });

    // Cerrar modal con tecla ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });

    console.log('Sistema de videos minimalista inicializado');
});

/**
 * Función opcional para agregar nuevos videos (para uso futuro)
 * @param {number} moduleId - ID del módulo
 * @param {Object} videoData - Datos del video
 */
function addVideo(moduleId, videoData) {
    const moduleContent = document.getElementById('module-' + moduleId);
    const videoList = moduleContent.querySelector('.video-list');
    
    if (!videoList) return;
    
    const listItem = document.createElement('li');
    listItem.className = 'video-item';
    listItem.setAttribute('onclick', `playVideo('${videoData.url}', '${videoData.title}')`);
    
    listItem.innerHTML = `
        <div class="video-icon">▶</div>
        <div class="video-info">
            <div class="video-title">${videoData.title}</div>
            <div class="video-meta">${videoData.duration} • ${videoData.date}</div>
        </div>
    `;
    
    videoList.appendChild(listItem);
}