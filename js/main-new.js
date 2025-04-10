document.addEventListener('DOMContentLoaded', function() {
    // Inicializácia FilePond
    initializeFilePond();
    
    // Inicializácia PhotoSwipe
    initializePhotoSwipe();
    
    // Implementácia lazy loading pre obrázky
    initializeLazyLoading();
    
    // Tlačidlo na zobrazenie/skrytie formulára
    const showFormButton = document.getElementById('show-upload-form');
    const formContainer = document.getElementById('upload-form-container');
    
    // Zobraziť/skryť formulár na upload fotiek
    showFormButton.addEventListener('click', function() {
        if (formContainer.style.display === 'none') {
            formContainer.style.display = 'block';
            showFormButton.textContent = 'Skryť formulár';
        } else {
            formContainer.style.display = 'none';
            showFormButton.textContent = 'Pridať fotky';
        }
    });
});

// Inicializácia FilePond pre upload súborov
function initializeFilePond() {
    // Registrácia FilePond pluginov
    FilePond.registerPlugin(
        FilePondPluginFileValidateType,
        FilePondPluginImagePreview,
        FilePondPluginFileEncode
    );
    
    // Konfigurácia FilePond
    FilePond.setOptions({
        allowMultiple: true,
        maxFiles: 10,
        acceptedFileTypes: ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/heic', 'image/heif', 'image/bmp', 'image/tiff', 'image/tif'],
        labelIdle: 'Pretiahnite sem fotky alebo <span class="filepond--label-action">kliknite pre výber zo zariadenia</span>',
        labelFileTypeNotAllowed: 'Neplatný typ súboru, podporované sú iba obrázky',
        labelFileLoading: 'Načítavanie',
        labelFileLoadError: 'Chyba pri načítaní',
        labelFileProcessing: 'Nahrávanie',
        labelFileProcessingComplete: 'Nahrávanie dokončené',
        labelFileProcessingAborted: 'Nahrávanie zrušené',
        labelTapToCancel: 'kliknite pre zrušenie',
        labelTapToRetry: 'kliknite pre opakovanie',
        labelTapToUndo: 'kliknite pre odstránenie',
        credits: false
    });
    
    // Inicializácia FilePond pre input element
    const inputElement = document.querySelector('input.filepond');
    const pond = FilePond.create(inputElement);
    
    // Spracovanie formulára po odoslaní
    const uploadForm = document.getElementById('upload-form');
    const formContainer = document.getElementById('upload-form-container');
    const showFormButton = document.getElementById('show-upload-form');
    
    uploadForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Zobrazenie indikátora nahrávania
        const uploadButton = document.getElementById('upload-button');
        const originalButtonText = uploadButton.textContent;
        uploadButton.textContent = 'Nahrávam...';
        uploadButton.disabled = true;
        
        // Získanie údajov z formulára
        const formData = new FormData(uploadForm);
        
        // Získanie mena používateľa
        const nameInput = document.getElementById('name');
        const userName = nameInput.value;
        
        // Ak sú nejaké súbory v pond, použijeme ich
        if (pond.getFiles().length > 0) {
            // Pre každý súbor získaný z FilePond
            pond.getFiles().forEach(fileItem => {
                if (fileItem.file) {
                    formData.append('photos[]', fileItem.file);
                }
            });
        }
        
        // Pridanie mena používateľa
        formData.append('name', userName);
        
        // Odoslanie požiadavky na server
        fetch('upload.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Obnovenie tlačidla
            uploadButton.textContent = originalButtonText;
            uploadButton.disabled = false;
            
            // Zobrazenie správy o výsledku
            alert(data.message);
            
            if (data.success) {
                // Vyčistenie formulára
                uploadForm.reset();
                pond.removeFiles();
                
                // Skrytie formulára
                formContainer.style.display = 'none';
                showFormButton.textContent = 'Pridať fotky';
                
                // Obnovenie galérie - jednoduchý spôsob je načítať stránku znova
                location.reload();
            }
        })
        .catch(error => {
            console.error('Chyba pri nahrávaní:', error);
            uploadButton.textContent = originalButtonText;
            uploadButton.disabled = false;
            alert('Nastala chyba pri nahrávaní fotiek. Skúste to prosím znova.');
        });
    });
}

// Inicializácia PhotoSwipe pre galériu
function initializePhotoSwipe() {
    // Inicializácia PhotoSwipe s galérii
    const container = document.querySelector('.pswp-gallery');
    if (!container) return;
    
    // Definícia options
    const options = {
        gallery: '.pswp-gallery',
        children: 'a',
        loop: true,
        showHideAnimationType: 'fade',
        pswpModule: PhotoSwipe,
        mainClass: 'pswp--custom-bg',
        paddingFn: () => {
            return {
                top: 30,
                bottom: 30,
                left: 0,
                right: 0
            }
        },
        bgOpacity: 0.9,
        initialZoomLevel: 'fit',
        secondaryZoomLevel: 2,
        maxZoomLevel: 4,
        preloaderDelay: 1000, // ms
        closeOnVerticalDrag: true
    };
    
    // Pridanie event listenera na kliknutie na obrázky
    container.querySelectorAll('a').forEach(el => {
        el.addEventListener('click', function(e) {
            e.preventDefault();
            const gallery = new PhotoSwipe(options);
            gallery.init();
        });
    });
}

// Inicializácia lazy loading pre obrázky
function initializeLazyLoading() {
    // Použitie Intersection Observer API pre lazy loading
    if ('IntersectionObserver' in window) {
        const lazyImageObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    let lazyImage = entry.target;
                    
                    // Nahradíme placeholder skutočným obrázkom
                    lazyImage.src = lazyImage.dataset.src;
                    lazyImage.classList.remove('lazy-image');
                    lazyImage.classList.add('loaded');
                    
                    // Prestaneme sledovať tento prvok
                    lazyImageObserver.unobserve(lazyImage);
                }
            });
        }, {
            rootMargin: '0px 0px 200px 0px' // Načítava obrázky trochu skôr pred vstupom do viewportu
        });

        // Sledujeme všetky lazy-image elementy
        const lazyImages = document.querySelectorAll('.lazy-image');
        lazyImages.forEach(function(lazyImage) {
            lazyImageObserver.observe(lazyImage);
        });
    } else {
        // Fallback pre staré prehliadače
        const lazyImages = document.querySelectorAll('.lazy-image');
        lazyImages.forEach(function(lazyImage) {
            lazyImage.src = lazyImage.dataset.src;
            lazyImage.classList.remove('lazy-image');
            lazyImage.classList.add('loaded');
        });
    }
}
