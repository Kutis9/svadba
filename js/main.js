// Čakáme na načítanie DOM
document.addEventListener('DOMContentLoaded', function() {
    console.log('Aplikácia bola načítaná');
    
    // Pridanie ladiacich výpisov pre kontrolu
    console.log('Tlačidlo:', document.getElementById('show-upload-form'));
    console.log('Formulár:', document.getElementById('upload-form-container'));
    
    // Inicializácia formulára
    initializeUploadForm();
    
    // Inicializácia FilePond pre nahrávanie súborov
    initializeFilePond();
    
    // Inicializácia LightGallery pre zobrazenie galérie
    initializeLightGallery();
    
    // Inicializácia hlásení o úspechu/chybe
    handleAlerts();
});

/**
 * Inicializácia formulára pre nahrávanie súborov
 */
function initializeUploadForm() {
    const showFormButton = document.getElementById('show-upload-form');
    const formContainer = document.getElementById('upload-form-container');
    
    console.log('Inicializácia formulára - tlačidlo:', showFormButton);
    console.log('Inicializácia formulára - kontajner:', formContainer);
    
    if (showFormButton && formContainer) {
        // Nastavenie výslovne display na none
        formContainer.style.display = 'none';
        
        showFormButton.addEventListener('click', function(e) {
            console.log('Kliknutie na tlačidlo');
            e.preventDefault();
            
            // Výpis stavu pred zmenou
            console.log('Stav formulára pred zmenou:', formContainer.style.display);
            console.log('Computed style:', getComputedStyle(formContainer).display);
            
            // Prepínanie zobrazenia formulára - zjednodušená logika
            if (formContainer.style.display === 'none' || getComputedStyle(formContainer).display === 'none') {
                console.log('Zobrazujem formulár');
                formContainer.style.display = 'block';
                showFormButton.textContent = 'Skryť formulár';
            } else {
                console.log('Skrývam formulár');
                formContainer.style.display = 'none';
                showFormButton.textContent = 'Pridaj';
            }
            
            // Výpis stavu po zmene
            console.log('Stav formulára po zmene:', formContainer.style.display);
            
            // Skúste zobraziť formulár iným spôsobom, ak predošlý zlyhal
            setTimeout(function() {
                console.log('Kontrolujem stav po timeout:', formContainer.style.display);
                if (formContainer.style.display !== 'block' && getComputedStyle(formContainer).display !== 'block') {
                    console.log('Záložný spôsob zobrazenia');
                    formContainer.setAttribute('style', 'display: block !important');
                }
            }, 100);
        });
    } else {
        console.error('CHYBA: Tlačidlo alebo formulár sa nenašli!');
        if (!showFormButton) console.error('Tlačidlo nie je na stránke!');
        if (!formContainer) console.error('Formulárový kontajner nie je na stránke!');
    }
}

/**
 * Inicializácia FilePond pre nahrávanie súborov
 */
function initializeFilePond() {
    if (typeof FilePond !== 'undefined') {
        console.log('FilePond je dostupný, inicializujem...');
        
        // Registrácia pluginov
        FilePond.registerPlugin(
            FilePondPluginFileValidateType,
            FilePondPluginFileValidateSize,
            FilePondPluginImagePreview
        );
        
        // Nastavenie FilePond
        FilePond.setOptions({
            allowMultiple: true,
            maxFiles: 20,
            maxFileSize: '50MB',
            acceptedFileTypes: [
                'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
                'image/heic', 'image/heif', 'image/bmp', 'image/tiff', 'image/tif',
                'video/mp4', 'video/quicktime'
            ],
            labelIdle: 'Pretiahnite sem fotky/videá alebo <span class="filepond--label-action">kliknite pre výber</span>',
            labelFileTypeNotAllowed: 'Neplatný typ súboru',
            labelFileWaitingForSize: 'Čakanie na veľkosť',
            labelFileSizeNotAvailable: 'Veľkosť nie je dostupná',
            labelInvalidField: 'Pole obsahuje neplatné súbory',
            labelFileLoading: 'Načítavam',
            labelFileLoadError: 'Chyba pri načítaní',
            labelFileProcessing: 'Nahrávam',
            labelFileProcessingComplete: 'Nahrávanie dokončené',
            labelFileProcessingAborted: 'Nahrávanie zrušené',
            labelFileProcessingError: 'Chyba pri nahrávaní',
            labelTapToCancel: 'kliknutím zrušíte',
            labelTapToRetry: 'kliknutím to skúsite znova',
            labelTapToUndo: 'kliknutím vrátite späť',
            credits: false
        });
        
        // Vytvorenie FilePond inštancie
        const inputElement = document.querySelector('input.filepond');
        if (inputElement) {
            console.log('Našiel som input element pre FilePond');
            
            // Vytvorenie FilePond inštancie s priamym prístupom k elementu
            const pond = FilePond.create(inputElement);
            console.log('FilePond inicializovaný:', pond);
            
            // Pridanie handlera pre odoslanie formulára
            const form = document.getElementById('upload-form');
            if (form) {
                console.log('Pridávam event handler pre formulár');
                
                form.addEventListener('submit', function(e) {
                    console.log('Formulár sa odosielá');
                    
                    // Kontrola, či sú vybrané súbory
                    if (pond.getFiles().length === 0) {
                        e.preventDefault();
                        alert('Prosím, vyberte aspoň jeden súbor na nahratie.');
                        console.log('Žiadne súbory nie sú vybrané');
                        return false;
                    }
                    
                    console.log('Pokračujem s odoslaním formulára, počet súborov:', pond.getFiles().length);
                    
                    // Pre klasické odoslanie nepotrebujeme nič robiť, FilePond pridá súbory do FormData automaticky
                });
            } else {
                console.error('Formulár sa nenašiel!');
            }
        } else {
            console.error('Input element pre FilePond sa nenašiel!');
        }
    } else {
        console.error('FilePond nie je definovaný!');
    }
}

/**
 * Inicializácia LightGallery pre zobrazenie galérie
 */
function initializeLightGallery() {
    if (typeof lightGallery !== 'undefined') {
        const galleryContainer = document.getElementById('gallery-container');
        
        if (galleryContainer) {
            lightGallery(galleryContainer, {
                selector: 'a',
                plugins: [lgZoom, lgVideo],
                speed: 500,
                download: false,
                counter: true,
                mousewheel: true,
                backdropDuration: 400,
                mode: 'lg-fade',
                videojs: true,
                videojsOptions: {
                    controls: true,
                    preload: 'metadata',
                    fluid: true
                }
            });
        }
    } else {
        console.error('LightGallery nie je načítaný!');
    }
}

/**
 * Spracovanie hlásení o úspechu/chybe
 */
function handleAlerts() {
    // Automatické skrytie hlásení po 5 sekundách
    const alerts = document.querySelectorAll('.alert');
    
    if (alerts.length) {
        setTimeout(function() {
            alerts.forEach(function(alert) {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s';
                
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500);
            });
        }, 5000);
    }
    
    // Spracovanie URL parametrov
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.has('success') || urlParams.has('error')) {
        // Automatické skrolovanie k vrchu stránky pre zobrazenie hlásenia
        window.scrollTo(0, 0);
        
        // Vyčistenie URL
        if (history.pushState) {
            history.pushState(null, null, window.location.pathname);
        }
    }
} 