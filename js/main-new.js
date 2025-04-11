document.addEventListener('DOMContentLoaded', function() {
    console.log('🔍 DOM načítaný, inicializujem komponenty...');
    console.log('📋 Window size:', window.innerWidth, 'x', window.innerHeight);
    console.log('📋 User agent:', navigator.userAgent);
    
    // Debug na elemente
    const btnDebug = document.getElementById('show-upload-form');
    console.log('📋 Tlačidlo - detaily:', btnDebug ? {
        id: btnDebug.id,
        tagName: btnDebug.tagName,
        className: btnDebug.className,
        innerHTML: btnDebug.innerHTML,
        outerHTML: btnDebug.outerHTML,
        style: btnDebug.style,
        computedStyle: window.getComputedStyle(btnDebug)
    } : 'NENÁJDENÉ');
    
    // Najskôr inicializujeme formulár až potom komponenty
    console.log('🔄 Spúšťam setupUploadForm()...');
    setupUploadForm();
    
    // Alternatívny prístup - priradenie pomocou inline atribútu
    try {
        const showBtn = document.getElementById('show-upload-form');
        const formContainer = document.getElementById('upload-form-container');
        
        if (showBtn && formContainer) {
            console.log('⚠️ Pridávam záložný event handler ako inline atribút...');
            
            // Vytvorenie globálnej funkcie pre onClick
            window.toggleUploadForm = function() {
                console.log('🖱️ INLINE CLICK HANDLER VOLANÝ!');
                const container = document.getElementById('upload-form-container');
                if (!container) {
                    console.error('❌ Kontajner nenájdený v inline handleri!');
                    return;
                }
                
                const isHidden = window.getComputedStyle(container).display === 'none';
                console.log('📋 Formulár skrytý (inline handler)?', isHidden);
                
                container.style.display = isHidden ? 'block' : 'none';
                document.getElementById('show-upload-form').textContent = isHidden ? 'Skryť formulár' : 'Pridať fotky';
                console.log('✅ Toggle dokončený (inline handler)');
                return false;
            };
            
            // Priame priradenie onclick cez atribút (najspoľahlivejší spôsob)
            showBtn.setAttribute('onclick', 'return toggleUploadForm();');
            console.log('✅ Inline handler priradený');
        } else {
            console.error('❌ Nemožno priradiť inline handler - elementy nenájdené');
        }
    } catch (error) {
        console.error('❌ Chyba pri priradení inline handlera:', error);
    }
    
    // Nastavíme explicitný onclick handler (druhý spôsob)
    try {
        const showBtn = document.getElementById('show-upload-form');
        if (showBtn) {
            console.log('⚠️ Pridávam explicitný onclick handler...');
            // Pridanie directClick funkcie
            showBtn.onclick = function(e) {
                console.log('🖱️ DIRECT ONCLICK HANDLER VOLANÝ!', e);
                console.log('📋 Event target:', e.target);
                console.log('📋 Current target:', e.currentTarget);
                toggleUploadForm();
                return false;
            };
            console.log('✅ Direct handler priradený');
        }
    } catch (error) {
        console.error('❌ Chyba pri priradení direct handlera:', error);
    }
    
    // Po nastavení základných eventov inicializujeme knižnice
    console.log('🔄 Inicializujem FilePond...');
    initializeFilePond();
    
    console.log('🔄 Inicializujem PhotoSwipe...');
    initializePhotoSwipe();
    
    console.log('🔄 Inicializujem Lazy Loading...');
    initializeLazyLoading();
    
    console.log('✅ Inicializácia dokončená.');
});

// Nastavenie funkcií pre formulár
function setupUploadForm() {
    try {
        // Získame referencie na potrebné elementy
        const showFormButton = document.getElementById('show-upload-form');
        const formContainer = document.getElementById('upload-form-container');
        
        console.log('📋 Tlačidlo show-upload-form:', showFormButton);
        console.log('📋 Formulár container:', formContainer);
        
        // Skontrolujeme, či sme získali referencie
        if (!showFormButton) {
            console.error('❌ Tlačidlo "show-upload-form" nebolo nájdené v DOM!');
            return;
        }
        
        if (!formContainer) {
            console.error('❌ Kontajner formulára "upload-form-container" nebol nájdený v DOM!');
            return;
        }
        
        // Overíme inicializačný stav formulára
        console.log('📋 Inicializačný stav formulára - inline style:', formContainer.style.display);
        console.log('📋 Inicializačný stav formulára - computed style:', window.getComputedStyle(formContainer).display);
        console.log('📋 Tlačidlo text:', showFormButton.textContent);
        
        // Pridáme event listener na tlačidlo so zabezpečením proti chybám
        console.log('⚠️ Pridávam addEventListener na tlačidlo...');
        showFormButton.addEventListener('click', function clickHandler(e) {
            console.log('🖱️ CLICK EVENT HANDLER VOLANÝ!', e);
            console.log('📋 Event type:', e.type);
            console.log('📋 Event phase:', e.eventPhase);
            console.log('📋 Event target:', e.target);
            console.log('📋 Current target:', e.currentTarget);
            console.log('📋 Bubbles:', e.bubbles);
            console.log('📋 Cancelable:', e.cancelable);
            
            // Kontrola aktuálneho stavu (computed style, nie inline style.display)
            const isHidden = window.getComputedStyle(formContainer).display === 'none';
            console.log('📋 Je formulár skrytý?', isHidden);
            
            if (isHidden) {
                // Zobrazenie formulára
                console.log('🔄 Zobrazujem formulár...');
                formContainer.style.display = 'block';
                showFormButton.textContent = 'Skryť formulár';
            } else {
                // Skrytie formulára
                console.log('🔄 Skrývam formulár...');
                formContainer.style.display = 'none';
                showFormButton.textContent = 'Pridať fotky';
            }
            
            // Log o výslednom stave
            console.log('📋 Nový display inline style:', formContainer.style.display);
            console.log('📋 Nový computed style:', window.getComputedStyle(formContainer).display);
            
            // Zabráni prípadnému bubblingu eventu
            console.log('⚠️ Volám preventDefault a stopPropagation');
            try {
                e.preventDefault();
                e.stopPropagation();
            } catch (error) {
                console.error('❌ Chyba pri preventDefault/stopPropagation:', error);
            }
            console.log('✅ Click handler dokončený');
            return false;
        });
        
        console.log('✅ Event listener cez addEventListener bol úspešne pridaný.');
        
        // Test, či je tlačidlo event listener skutočne pripojený
        console.log('🔍 Test, či je listener pripojený - elementy majú getEventListeners?', 
                   !!showFormButton.getEventListeners);
    } catch (error) {
        console.error('❌ Chyba pri nastavovaní formulára:', error);
    }
}

// Inicializácia FilePond pre upload súborov
function initializeFilePond() {
    try {
        // Kontrola, či je FilePond načítaný
        if (typeof FilePond === 'undefined') {
            console.error('FilePond knižnica nie je načítaná!');
            return;
        }

        // Kontrola, či sú pluginy načítané
        if (typeof FilePondPluginFileValidateType === 'undefined' ||
            typeof FilePondPluginImagePreview === 'undefined' ||
            typeof FilePondPluginFileEncode === 'undefined') {
            console.error('FilePond pluginy nie sú načítané!');
            return;
        }

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
            credits: false,
            // Dôležité: server nastavíme na null, aby sme eliminovali automatické odosielanie na server
            server: null
        });
        
        // Inicializácia FilePond pre input element
        const inputElement = document.querySelector('input.filepond');
        if (!inputElement) {
            console.error('FilePond input element nebol nájdený!');
            return;
        }
        
        const pond = FilePond.create(inputElement);
        
        // Spracovanie formulára po odoslaní
        const uploadForm = document.getElementById('upload-form');
        const formContainer = document.getElementById('upload-form-container');
        const showFormButton = document.getElementById('show-upload-form');
        
        if (!uploadForm) {
            console.error('Upload formulár nebol nájdený!');
            return;
        }
        
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Zobrazenie indikátora nahrávania
            const uploadButton = document.getElementById('upload-button');
            if (!uploadButton) {
                console.error('Upload tlačidlo nebolo nájdené!');
                return;
            }
            
            const originalButtonText = uploadButton.textContent;
            uploadButton.textContent = 'Nahrávam...';
            uploadButton.disabled = true;
            
            // Získanie údajov z formulára
            const formData = new FormData(uploadForm);
            
            // Získanie mena používateľa
            const nameInput = document.getElementById('name');
            const userName = nameInput ? nameInput.value : '';
            
            // Vyčistenie predchádzajúcich súborov z formData
            for (const key of [...formData.keys()]) {
                if (key === 'photos[]') {
                    formData.delete(key);
                }
            }
            
            // Ak sú nejaké súbory v pond, použijeme ich
            const pondFiles = pond.getFiles();
            let filesAdded = false;
            
            if (pondFiles && pondFiles.length > 0) {
                // Pre každý súbor získaný z FilePond
                for (let i = 0; i < pondFiles.length; i++) {
                    const fileItem = pondFiles[i];
                    if (fileItem && fileItem.file) {
                        formData.append('photos[]', fileItem.file);
                        filesAdded = true;
                    }
                }
            }
            
            if (!filesAdded) {
                uploadButton.textContent = originalButtonText;
                uploadButton.disabled = false;
                alert('Prosím, vyberte aspoň jeden súbor na nahratie.');
                return;
            }
            
            // Pridanie mena používateľa
            formData.append('name', userName);
            
            // Odoslanie požiadavky na server
            fetch('upload.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Serverová odpoveď nebola v poriadku: ' + response.status);
                }
                return response.json();
            })
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
                alert('Nastala chyba pri nahrávaní fotiek: ' + error.message);
            });
        });
    } catch (error) {
        console.error('Chyba pri inicializácii FilePond:', error);
    }
}

// Inicializácia PhotoSwipe pre galériu
function initializePhotoSwipe() {
    try {
        // Kontrola načítania PhotoSwipe knižnice
        if (typeof PhotoSwipeLightbox === 'undefined') {
            console.error('PhotoSwipeLightbox knižnica nie je načítaná!');
            return;
        }

        // Inicializácia PhotoSwipe Lightbox - správny spôsob pre PhotoSwipe 5
        const lightbox = new PhotoSwipeLightbox({
            gallery: '.pswp-gallery',
            children: 'a',
            loop: true,
            showHideAnimationType: 'fade',
            bgOpacity: 0.85,
            padding: { top: 20, bottom: 20, left: 20, right: 20 },
            wheelToZoom: true,
            initialZoomLevel: 'fit',
            secondaryZoomLevel: 2,
            maxZoomLevel: 4,
            // Dynamický import PhotoSwipe modulu
            pswpModule: () => Promise.resolve(window.PhotoSwipe)
        });
        
        // Event handler pre načítanie titulku
        lightbox.on('uiRegister', function() {
            // Pridať tlačidlo pre zdieľanie
            lightbox.pswp.ui.registerElement({
                name: 'share-button',
                order: 9,
                isButton: true,
                html: '<button class="pswp__button pswp__button--share" title="Zdieľať"></button>',
                onClick: () => {
                    const url = window.location.href;
                    if (navigator.share) {
                        navigator.share({
                            title: 'Svadobná fotografia',
                            text: 'Pozri si túto svadobnú fotografiu!',
                            url: url
                        }).catch(console.error);
                    } else {
                        const textArea = document.createElement('textarea');
                        textArea.value = url;
                        document.body.appendChild(textArea);
                        textArea.select();
                        document.execCommand('copy');
                        document.body.removeChild(textArea);
                        alert('Odkaz na stránku bol skopírovaný do schránky');
                    }
                }
            });
        });
        
        lightbox.init();
    } catch (error) {
        console.error('Chyba pri inicializácii PhotoSwipe:', error);
    }
}

// Inicializácia lazy loading pre obrázky
function initializeLazyLoading() {
    try {
        // Použitie Intersection Observer API pre lazy loading
        if ('IntersectionObserver' in window) {
            const lazyImageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        let lazyImage = entry.target;
                        
                        // Kontrola, či sú potrebné atribúty
                        if (lazyImage.dataset && lazyImage.dataset.src) {
                            // Nahradíme placeholder skutočným obrázkom
                            lazyImage.src = lazyImage.dataset.src;
                            lazyImage.classList.remove('lazy-image');
                            lazyImage.classList.add('loaded');
                            
                            // Prestaneme sledovať tento prvok
                            lazyImageObserver.unobserve(lazyImage);
                        }
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
                if (lazyImage.dataset && lazyImage.dataset.src) {
                    lazyImage.src = lazyImage.dataset.src;
                    lazyImage.classList.remove('lazy-image');
                    lazyImage.classList.add('loaded');
                }
            });
        }
    } catch (error) {
        console.error('Chyba pri inicializácii lazy loading:', error);
    }
}
