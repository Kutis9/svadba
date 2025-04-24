// Čakáme na načítanie DOM
document.addEventListener('DOMContentLoaded', function() {
    // Globálne premenné pre galériu a aktuálny index
    window.galleryDataGlobal = [];
    window.currentGalleryIndex = 0;
    
    // Funkcia pre otvorenie galérie na konkrétnom indexe
    function openGalleryAt(index) {
        const modal = document.getElementById('custom-gallery-modal');
        const mediaContainer = document.querySelector('.gallery-media-container');
        const counter = document.querySelector('.gallery-counter');
        const dateDisplay = document.querySelector('.gallery-date');
        
        if (!modal || !mediaContainer || index === undefined || !window.galleryDataGlobal) return;
        
        // Kontrola či index je v rozsahu
        if (index >= 0 && index < window.galleryDataGlobal.length) {
            window.currentGalleryIndex = index;
            
            // Vymazať existujúci obsah
            mediaContainer.innerHTML = '';
            
            // Načítať médium
            const item = window.galleryDataGlobal[index];
            
            if (item.type === 'image') {
                const img = document.createElement('img');
                img.src = item.path;
                img.alt = 'Fotka zo svadby';
                img.classList.add('gallery-image');
                
                // Zobraziť placeholder počas načítavania
                img.style.opacity = '0';
                
                const placeholder = document.createElement('div');
                placeholder.classList.add('image-loading-placeholder');
                placeholder.textContent = 'Načítavam...';
                mediaContainer.appendChild(placeholder);
                
                // Po načítaní obrázka odstrániť placeholder
                img.onload = function() {
                    if (placeholder.parentNode) {
                        mediaContainer.removeChild(placeholder);
                    }
                    img.style.opacity = '1';
                };
                
                mediaContainer.appendChild(img);
            } else if (item.type === 'video') {
                const video = document.createElement('video');
                video.src = item.path;
                video.controls = true;
                video.playsInline = true;
                video.autoplay = false;
                video.classList.add('gallery-video');
                
                // Nastavenia pre mobilné optimalizácie
                video.preload = 'metadata';
                
                // Zobraziť placeholder počas načítavania
                const placeholder = document.createElement('div');
                placeholder.classList.add('video-loading-placeholder');
                placeholder.textContent = 'Pripravujem video...';
                mediaContainer.appendChild(placeholder);
                
                // Po pripravení videa odstrániť placeholder
                video.addEventListener('loadedmetadata', function() {
                    if (placeholder.parentNode) {
                        mediaContainer.removeChild(placeholder);
                    }
                    
                    // Optimalizácia pre mobilné zariadenia
                    if (window.innerWidth < 768 && video.videoHeight > 720) {
                        video.setAttribute('height', 'auto');
                        video.setAttribute('width', '100%');
                    }
                });
                
                // Zachytenie chyby pri načítaní videa
                video.addEventListener('error', function() {
                    if (placeholder.parentNode) {
                        mediaContainer.removeChild(placeholder);
                    }
                    
                    const errorMsg = document.createElement('div');
                    errorMsg.classList.add('media-error');
                    errorMsg.textContent = 'Nepodarilo sa načítať video. Skúste to znova neskôr.';
                    mediaContainer.appendChild(errorMsg);
                });
                
                mediaContainer.appendChild(video);
            }
            
            // Aktualizovať informácie v modálnom okne
            counter.textContent = `${index + 1} / ${window.galleryDataGlobal.length}`;
            if (dateDisplay && item.date) {
                dateDisplay.textContent = item.date;
            }
            
            // Aktualizácia tlačidiel navigácie
            const prevBtn = document.querySelector('.gallery-prev');
            const nextBtn = document.querySelector('.gallery-next');
            
            if (prevBtn) {
                prevBtn.style.visibility = index > 0 ? 'visible' : 'hidden';
            }
            
            if (nextBtn) {
                nextBtn.style.visibility = index < window.galleryDataGlobal.length - 1 ? 'visible' : 'hidden';
            }
            
            // Otvoriť modálne okno
            modal.classList.add('open');
            document.body.style.overflow = 'hidden'; // Zabrániť skrolovaniu
        }
    }
    
    // Aktualizácia galérie po načítaní nových položiek
    function updateGalleryItems() {
        // Získať všetky položky galérie (vrátane nových)
        const galleryItems = document.querySelectorAll('.gallery-item');
        const galleryData = [];
        
        // Naplniť galleryData z HTML elementov
        galleryItems.forEach((item, index) => {
            // Aktualizujeme data-index atribút, aby zodpovedal novej pozícii
            item.setAttribute('data-index', index);
            
            galleryData.push({
                type: item.getAttribute('data-type'),
                path: item.getAttribute('data-path'),
                date: item.getAttribute('data-date'),
                index: index
            });
            
            // Pridať event listener pre novú položku
            item.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'));
                // Zavolať lokálnu funkciu pre otvorenie galérie na danom indexe
                openGalleryAt(index);
            });
        });
        
        // Uložiť aktualizované galleryData do globálnej premennej
        window.galleryDataGlobal = galleryData;
    }
    
    // Exportovať funkcie do globálneho scope, aby boli dostupné z index.php
    window.updateGalleryItems = updateGalleryItems;
    window.openGalleryAt = openGalleryAt;
});