<?php
// Nastavenie PHP 8.4 kompatibility
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kontrola existencie adresára uploads
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
    chmod('uploads', 0777);
}

// Funkcia na získanie zoznamu súborov z adresára uploads
function getGalleryFiles() {
    $dir = 'uploads/';
    $files = [];
    
    if (is_dir($dir)) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov', 'heic', 'heif'];
        
        foreach (scandir($dir) as $file) {
            if ($file !== '.' && $file !== '..' && !strstr($file, '.txt')) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, $allowedExtensions)) {
                    $files[] = [
                        'name' => $file,
                        'path' => $dir . $file,
                        'date' => filemtime($dir . $file),
                        'type' => in_array($ext, ['mp4', 'mov']) ? 'video' : 'image'
                    ];
                }
            }
        }
        
        // Zoradenie podľa dátumu - najnovšie najprv
        usort($files, function($a, $b) {
            return $b['date'] - $a['date'];
        });
    }
    
    return $files;
}

// Získanie zoznamu súborov pre galériu
$galleryFiles = getGalleryFiles();

// Cache busting parameter pre súbory
$cacheBusting = time();
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Miška a Luky - Zdieľajte s nami spomienky</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="grafikaSvadba/Designer-removebg-preview.png">
    
    <!-- Základné CSS -->
    <link rel="stylesheet" href="css/style.css?v=<?php echo $cacheBusting; ?>">
</head>
<body>
    <div class="upload-spinner-container">
        <div class="upload-spinner"></div>
        <div class="upload-progress-text">Nahrávanie súborov... Prosím, počkajte.</div>
    </div>
    
    <!-- Modal galéria -->
    <div id="custom-gallery-modal" class="custom-gallery-modal">
        <span class="gallery-close" style="position: fixed; top: 20px; right: 20px; color: white; font-size: 35px; font-weight: bold; cursor: pointer; z-index: 1100; text-shadow: 0 0 10px rgba(0, 0, 0, 0.7); width: 45px; height: 45px; border-radius: 50%; display: flex; justify-content: center; align-items: center; line-height: 0.8;">&times;</span>
        
        <div class="gallery-modal-content">
            <div class="gallery-navigation">
                <button class="gallery-prev">&lt;</button>
                <button class="gallery-next">&gt;</button>
            </div>
            <div class="gallery-main-container">
                <div class="gallery-media-container">
                    <!-- Tu sa bude zobrazovať fotka alebo video -->
                </div>
                <div class="gallery-caption">
                    <span class="gallery-counter"></span>
                    <span class="gallery-date"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="logo">
            <img src="grafikaSvadba/Designer-removebg-preview.png" alt="Svadobné logo">
        </div>
        
        <header>
            <h1>Naša Svadobná Galéria</h1>
            <p>Ďakujeme, že ste s nami oslávili náš výnimočný deň. Zdieľajte s nami svoje zábery a zážitky!</p>
        </header>

        <div id="status-message" class="alert" style="display: none;"></div>
        
        <?php
        if (isset($_GET['success'])) {
            $count = intval($_GET['success']);
            echo '<div class="alert alert-success">';
            echo "Úspešne nahraných $count " . ($count == 1 ? "súbor" : ($count < 5 ? "súbory" : "súborov"));
            echo '</div>';
        }
        if (isset($_GET['error'])) {
            echo '<div class="alert alert-error">';
            echo htmlspecialchars($_GET['error']);
            echo '</div>';
        }
        ?>
        
        <section id="upload-section">
            <button id="show-upload-form" class="btn-primary">Pridaj</button>
            
            <div id="upload-form-container" style="display: none;">
                <form id="upload-form" action="upload.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="message">Správa (nepovinné):</label>
                        <textarea id="message" name="message" placeholder="Napíšte niečo o tejto fotke alebo videu..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="author">Vaše meno (nepovinné):</label>
                        <input type="text" id="author" name="author" placeholder="Vaše meno">
                    </div>
                    
                    <div class="form-group upload-container">
                        <label for="media">Vyberte fotky alebo videá:</label>
                        <input type="file" id="media" name="media[]" multiple 
                               accept=".jpg,.jpeg,.png,.gif,.webp,.mp4,.mov,.heic,.heif">
                        <div id="selected-files" class="selected-files-container"></div>
                        <small class="form-help">
                            Podporované formáty: JPG, PNG, GIF, WEBP, MP4, MOV, HEIC a ďalšie. <br>
                            Môžete nahrať viac súborov naraz.
                        </small>
                    </div>
                    
                    <button type="submit" class="btn-submit">Nahrať</button>
                </form>
            </div>
        </section>
        
        <section id="gallery">
            <h2>Spoločné zážitky</h2>
            
            <?php if (empty($galleryFiles)): ?>
                <div class="no-content">
                    <p>Zatiaľ tu nie sú žiadne fotky ani videá. Buďte prví, kto niečo pridá!</p>
                </div>
            <?php else: ?>
                <div id="gallery-container" class="custom-gallery-container">
                    <?php foreach ($galleryFiles as $index => $file): ?>
                        <?php 
                        $isVideo = $file['type'] === 'video';
                        $thumbnailPath = $file['path'];
                        ?>
                        
                        <div class="gallery-item" data-type="<?php echo $file['type']; ?>" data-index="<?php echo $index; ?>" 
                             data-path="<?php echo $file['path']; ?>" 
                             data-date="<?php echo date('d.m.Y H:i', $file['date']); ?>">
                            
                            <?php if ($isVideo): ?>
                                <div class="video-thumbnail">
                                    <video class="video-preview" preload="metadata" src="<?php echo $thumbnailPath; ?>#t=0.5" style="display:none;"></video>
                                    <canvas class="video-thumbnail-canvas" width="300" height="300"></canvas>
                                    <div class="video-placeholder">Video</div>
                                    <div class="play-icon"></div>
                                </div>
                            <?php else: ?>
                                <img class="lazy-image" 
                                     data-src="<?php echo $thumbnailPath; ?>" 
                                     src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 3 4'%3E%3C/svg%3E"
                                     alt="Fotka zo svadby">
                            <?php endif; ?>
                            
                            <div class="item-info">
                                <span class="item-date"><?php echo date('d.m.Y H:i', $file['date']); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> Svadba Mišky a Lukáša. Ďakujeme.</p>
        </footer>
    </div>
    
    <!-- JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Aplikácia bola načítaná');
        
        // Inicializácia formulára
        initializeUploadForm();
        
        // Inicializácia vlastnej galérie
        initializeCustomGallery();
        
        // Automatické skrytie hlásení
        handleAlerts();
        
        // Zobrazenie vybraných súborov
        initializeFileInput();
        
        // Inicializácia lazy loading pre obrázky
        initializeLazyLoading();
    });
    
    /**
     * Inicializácia formulára pre nahrávanie súborov
     */
    function initializeUploadForm() {
        const showFormButton = document.getElementById('show-upload-form');
        const formContainer = document.getElementById('upload-form-container');
        
        if (showFormButton && formContainer) {
            showFormButton.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (formContainer.style.display === 'none' || getComputedStyle(formContainer).display === 'none') {
                    formContainer.style.display = 'block';
                    showFormButton.textContent = 'Skryť formulár';
                } else {
                    formContainer.style.display = 'none';
                    showFormButton.textContent = 'Pridaj';
                }
            });
        }
    }
    
    /**
     * Inicializácia vstupu súborov a zobrazenie vybraných súborov
     */
    function initializeFileInput() {
        const fileInput = document.getElementById('media');
        const selectedFilesContainer = document.getElementById('selected-files');
        const uploadForm = document.getElementById('upload-form');
        
        // Limity veľkosti súborov v bajtoch
        const MAX_IMAGE_SIZE = 50 * 1024 * 1024; // 50 MB pre obrázky
        const MAX_VIDEO_SIZE = 300 * 1024 * 1024; // 300 MB pre videá
        
        if (fileInput && selectedFilesContainer) {
            fileInput.addEventListener('change', function() {
                selectedFilesContainer.innerHTML = '';
                let hasOversizedFiles = false;
                let errorMessage = '';
                
                if (this.files.length > 0) {
                    const fileList = document.createElement('ul');
                    fileList.className = 'file-list';
                    
                    for (let i = 0; i < this.files.length; i++) {
                        const file = this.files[i];
                        const fileItem = document.createElement('li');
                        fileItem.className = 'file-item';
                        
                        // Kontrola veľkosti súboru
                        const isVideo = file.type.startsWith('video/');
                        const maxSize = isVideo ? MAX_VIDEO_SIZE : MAX_IMAGE_SIZE;
                        const maxSizeText = isVideo ? '300 MB' : '50 MB';
                        
                        let isOversized = false;
                        if (file.size > maxSize) {
                            isOversized = true;
                            hasOversizedFiles = true;
                            fileItem.classList.add('file-error');
                            errorMessage += `Súbor "${file.name}" je príliš veľký (${formatFileSize(file.size)}). Maximálna veľkosť je ${maxSizeText}.<br>`;
                        }
                        
                        // Náhľad pre obrázky
                        if (file.type.startsWith('image/')) {
                            const img = document.createElement('img');
                            img.className = 'file-preview';
                            img.src = URL.createObjectURL(file);
                            fileItem.appendChild(img);
                        } else if (file.type.startsWith('video/')) {
                            const videoIcon = document.createElement('div');
                            videoIcon.className = 'video-icon';
                            videoIcon.textContent = '🎬'; // Emoji pre video
                            fileItem.appendChild(videoIcon);
                        }
                        
                        const fileInfo = document.createElement('div');
                        fileInfo.className = 'file-info';
                        let sizeClass = isOversized ? 'file-size-error' : '';
                        fileInfo.innerHTML = `${file.name} <span class="${sizeClass}">(${formatFileSize(file.size)}${isOversized ? ' - príliš veľký' : ''})</span>`;
                        fileItem.appendChild(fileInfo);
                        
                        fileList.appendChild(fileItem);
                    }
                    
                    selectedFilesContainer.appendChild(fileList);
                    
                    // Zobraziť chybovú správu ak sú príliš veľké súbory
                    if (hasOversizedFiles) {
                        showStatusMessage(errorMessage, 'error');
                    }
                }
            });
        }
        
        if (uploadForm) {
            uploadForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Kontrola či sú vybrané súbory
                const fileInput = document.getElementById('media');
                if (!fileInput || fileInput.files.length === 0) {
                    showStatusMessage('Prosím, vyberte aspoň jeden súbor na nahratie.', 'error');
                    return;
                }
                
                // Kontrola veľkosti všetkých súborov
                let hasOversizedFiles = false;
                let errorMessage = '';
                
                for (let i = 0; i < fileInput.files.length; i++) {
                    const file = fileInput.files[i];
                    const isVideo = file.type.startsWith('video/');
                    const maxSize = isVideo ? MAX_VIDEO_SIZE : MAX_IMAGE_SIZE;
                    const maxSizeText = isVideo ? '300 MB' : '50 MB';
                    
                    if (file.size > maxSize) {
                        hasOversizedFiles = true;
                        errorMessage += `Súbor "${file.name}" je príliš veľký (${formatFileSize(file.size)}). Maximálna veľkosť je ${maxSizeText}.<br>`;
                    }
                }
                
                if (hasOversizedFiles) {
                    showStatusMessage(errorMessage, 'error');
                    return; // Zastaviť odoslanie formulára
                }
                
                // Zobraziť stav nahrávania
                showStatusMessage('Nahrávanie súborov... Prosím, počkajte.', 'info');
                
                // Zobraziť spinner s animáciou
                showUploadSpinner();
                
                // Použitie FormData pre AJAX nahrávanie
                const formData = new FormData(uploadForm);
                
                // AJAX požiadavka na nahratie súborov
                const xhr = new XMLHttpRequest();
                xhr.open('POST', uploadForm.getAttribute('action'), true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                
                xhr.onload = function() {
                    // Skryť spinner
                    hideUploadSpinner();
                    
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            
                            if (response.success) {
                                // Úspešné nahratie
                                showStatusMessage(response.message, 'success');
                                
                                // Skryť formulár pre nahrávanie
                                const formContainer = document.getElementById('upload-form-container');
                                if (formContainer) {
                                    formContainer.style.display = 'none';
                                    const showFormButton = document.getElementById('show-upload-form');
                                    if (showFormButton) {
                                        showFormButton.textContent = 'Pridaj';
                                    }
                                }
                                
                                // Vymazať formulár
                                uploadForm.reset();
                                if (selectedFilesContainer) {
                                    selectedFilesContainer.innerHTML = '';
                                }
                                
                                // Obnoviť stránku po 2 sekundách pre zobrazenie nových súborov
                                setTimeout(function() {
                                    location.reload();
                                }, 2000);
                            } else {
                                // Chyba pri nahrávaní
                                if (response.errors && response.errors.length) {
                                    showStatusMessage(response.errors.join('<br>'), 'error');
                                } else {
                                    showStatusMessage('Nastala chyba pri nahrávaní súborov.', 'error');
                                }
                            }
                        } catch (e) {
                            showStatusMessage('Nastala chyba pri spracovaní odpovede zo servera.', 'error');
                            console.error('Error parsing response:', e);
                        }
                    } else {
                        showStatusMessage('Nastala chyba pri komunikácii so serverom.', 'error');
                    }
                };
                
                xhr.onerror = function() {
                    hideUploadSpinner();
                    showStatusMessage('Nastala chyba pri komunikácii so serverom.', 'error');
                };
                
                xhr.send(formData);
            });
        }
    }
    
    /**
     * Zobrazenie stavovej správy
     */
    function showStatusMessage(message, type) {
        const statusMessage = document.getElementById('status-message');
        if (statusMessage) {
            statusMessage.innerHTML = message;
            statusMessage.className = 'alert';
            statusMessage.classList.add('alert-' + type);
            statusMessage.style.display = 'block';
            statusMessage.style.opacity = '1';
            
            // Automatické skrytie správy po 5 sekundách
            if (type !== 'info') {
                setTimeout(function() {
                    statusMessage.style.opacity = '0';
                    statusMessage.style.transition = 'opacity 0.5s';
                    
                    setTimeout(function() {
                        statusMessage.style.display = 'none';
                    }, 500);
                }, 5000);
            }
            
            // Prejsť na začiatok stránky aby bola správa viditeľná
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    }
    
    /**
     * Zobrazenie spinnera pri nahrávaní
     */
    function showUploadSpinner() {
        const spinnerContainer = document.querySelector('.upload-spinner-container');
        if (spinnerContainer) {
            spinnerContainer.style.display = 'flex';
        }
    }
    
    /**
     * Skrytie spinnera po nahrávaní
     */
    function hideUploadSpinner() {
        const spinnerContainer = document.querySelector('.upload-spinner-container');
        if (spinnerContainer) {
            spinnerContainer.style.display = 'none';
        }
    }
    
    /**
     * Formátovanie veľkosti súboru do čitateľnej podoby
     */
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(1024));
        
        return parseFloat((bytes / Math.pow(1024, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    /**
     * Inicializácia vlastnej galérie
     */
    function initializeCustomGallery() {
        // Získať referencie na elementy
        const galleryItems = document.querySelectorAll('.gallery-item');
        const modal = document.getElementById('custom-gallery-modal');
        const closeBtn = document.querySelector('.gallery-close');
        const prevBtn = document.querySelector('.gallery-prev');
        const nextBtn = document.querySelector('.gallery-next');
        const mediaContainer = document.querySelector('.gallery-media-container');
        const counter = document.querySelector('.gallery-counter');
        const dateDisplay = document.querySelector('.gallery-date');
        
        // Stav galérie
        let currentIndex = 0;
        let galleryData = [];
        
        // Naplniť galleryData z HTML elementov
        galleryItems.forEach(item => {
            galleryData.push({
                type: item.getAttribute('data-type'),
                path: item.getAttribute('data-path'),
                date: item.getAttribute('data-date'),
                index: parseInt(item.getAttribute('data-index'))
            });
        });
        
        // Inicializovať náhľady videí
        initializeVideoThumbnails();
        
        // Pridať event listener pre každú položku v galérii
        galleryItems.forEach(item => {
            item.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'));
                openGallery(index);
            });
        });
        
        // Zatvoriť galériu
        closeBtn.addEventListener('click', closeGallery);
        
        // Zatvoriť galériu pri kliknutí mimo obrázka/videa
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeGallery();
            }
        });
        
        // Navigácia - predchádzajúci
        prevBtn.addEventListener('click', function() {
            navigateGallery(-1);
        });
        
        // Navigácia - nasledujúci
        nextBtn.addEventListener('click', function() {
            navigateGallery(1);
        });
        
        // Navigácia pomocou klávesnice
        document.addEventListener('keydown', function(e) {
            if (!modal.classList.contains('open')) return;
            
            switch(e.key) {
                case 'Escape':
                    closeGallery();
                    break;
                case 'ArrowLeft':
                    navigateGallery(-1);
                    break;
                case 'ArrowRight':
                    navigateGallery(1);
                    break;
            }
        });
        
        /**
         * Otvorenie galérie na určitom indexe
         */
        function openGallery(index) {
            if (index >= 0 && index < galleryData.length) {
                currentIndex = index;
                loadMedia(galleryData[currentIndex]);
                updateControls();
                
                modal.classList.add('open');
                document.body.style.overflow = 'hidden'; // Zabrániť skrolovaniu stránky počas zobrazenia galérie
                
                // Automatické prispôsobenie výšky pre mobilné zariadenia
                adjustMobileHeight();
            }
        }
        
        /**
         * Zatvorenie galérie
         */
        function closeGallery() {
            modal.classList.remove('open');
            document.body.style.overflow = ''; // Obnovenie skrolovania
            
            // Zastaviť video, ak sa prehráva
            const video = mediaContainer.querySelector('video');
            if (video) {
                video.pause();
            }
            
            // Vyčistiť kontajner
            mediaContainer.innerHTML = '';
        }
        
        /**
         * Navigácia v galérii
         */
        function navigateGallery(step) {
            const newIndex = currentIndex + step;
            
            if (newIndex >= 0 && newIndex < galleryData.length) {
                // Zastaviť video, ak sa prehráva
                const video = mediaContainer.querySelector('video');
                if (video) {
                    video.pause();
                }
                
                currentIndex = newIndex;
                loadMedia(galleryData[currentIndex]);
                updateControls();
            }
        }
        
        /**
         * Načítanie média (obrázok alebo video)
         */
        function loadMedia(item) {
            mediaContainer.innerHTML = '';
            
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
                    mediaContainer.removeChild(placeholder);
                    img.style.opacity = '1';
                };
                
                mediaContainer.appendChild(img);
            } else if (item.type === 'video') {
                const video = document.createElement('video');
                video.src = item.path;
                video.controls = true;
                video.playsInline = true;
                video.autoplay = false; // Lepšia UX - nespúšťať automaticky
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
                    
                    // Optimalizácia pre mobilné zariadenia - znížiť kvalitu pre mobilné zariadenia
                    if (window.innerWidth < 768) {
                        if (video.videoHeight > 720) {
                            video.setAttribute('height', 'auto');
                            video.setAttribute('width', '100%');
                        }
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
        }
        
        /**
         * Aktualizácia ovládacích prvkov galérie
         */
        function updateControls() {
            counter.textContent = `${currentIndex + 1} / ${galleryData.length}`;
            dateDisplay.textContent = galleryData[currentIndex].date;
            
            // Zobrazenie/skrytie navigačných tlačidiel
            prevBtn.style.visibility = currentIndex > 0 ? 'visible' : 'hidden';
            nextBtn.style.visibility = currentIndex < galleryData.length - 1 ? 'visible' : 'hidden';
        }
        
        /**
         * Prispôsobenie výšky pre mobilné zariadenia
         */
        function adjustMobileHeight() {
            if (window.innerWidth < 768) {
                const viewportHeight = window.innerHeight;
                const modalContent = document.querySelector('.gallery-modal-content');
                modalContent.style.maxHeight = (viewportHeight * 0.9) + 'px';
                
                // Prispôsobiť kontajner pre médiá
                mediaContainer.style.maxHeight = (viewportHeight * 0.7) + 'px';
            }
        }
        
        /**
         * Inicializácia náhľadov videí
         */
        function initializeVideoThumbnails() {
            const videoItems = document.querySelectorAll('.gallery-item[data-type="video"]');
            
            videoItems.forEach(item => {
                const videoElement = item.querySelector('video.video-preview');
                const canvasElement = item.querySelector('canvas.video-thumbnail-canvas');
                const videoPlaceholder = item.querySelector('.video-placeholder');
                
                if (!videoElement || !canvasElement) return;
                
                // Po načítaní metadát vykresliť prvý frame do canvas
                videoElement.addEventListener('loadedmetadata', function() {
                    // Nastaviť čas na 0.5 sekundy pre získanie prvého zaujímavého frame-u
                    videoElement.currentTime = 0.5;
                });
                
                // Po aktualizácii času vykresliť frame
                videoElement.addEventListener('timeupdate', function() {
                    // Vykresliť frame na canvas
                    const ctx = canvasElement.getContext('2d');
                    
                    // Zachovať pomer strán
                    const width = canvasElement.width;
                    const height = canvasElement.height;
                    const videoWidth = videoElement.videoWidth;
                    const videoHeight = videoElement.videoHeight;
                    
                    let drawWidth, drawHeight, x, y;
                    
                    // Výpočet správnych rozmerov pre zachovanie pomeru strán
                    if (videoWidth / videoHeight > width / height) {
                        // Video je širšie ako canvas - prispôsobiť výšku
                        drawWidth = width;
                        drawHeight = (videoHeight / videoWidth) * width;
                        x = 0;
                        y = (height - drawHeight) / 2;
                    } else {
                        // Video je vyššie ako canvas - prispôsobiť šírku
                        drawHeight = height;
                        drawWidth = (videoWidth / videoHeight) * height;
                        x = (width - drawWidth) / 2;
                        y = 0;
                    }
                    
                    // Vyplniť pozadie
                    ctx.fillStyle = '#f0f0f0';
                    ctx.fillRect(0, 0, width, height);
                    
                    // Vykresliť frame
                    try {
                        ctx.drawImage(videoElement, x, y, drawWidth, drawHeight);
                        
                        // Skryť placeholder po úspešnom vykreslení
                        if (videoPlaceholder) {
                            videoPlaceholder.style.display = 'none';
                        }
                    } catch (e) {
                        console.error('Chyba pri vykreslení náhľadu videa:', e);
                    }
                    
                    // Zastaviť sledovanie, jednorazové vykreslenie stačí
                    videoElement.removeEventListener('timeupdate', arguments.callee);
                });
                
                // Začiatok načítavania videa
                videoElement.load();
            });
        }
        
        // Pridať podporu pre dotykové gestá (swipe)
        let touchStartX = 0;
        let touchEndX = 0;
        
        mediaContainer.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        }, false);
        
        mediaContainer.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        }, false);
        
        function handleSwipe() {
            const swipeThreshold = 50;
            if (touchEndX < touchStartX - swipeThreshold) {
                // Swipe doľava - ďalšia položka
                navigateGallery(1);
            }
            if (touchEndX > touchStartX + swipeThreshold) {
                // Swipe doprava - predchádzajúca položka
                navigateGallery(-1);
            }
        }
        
        // Pridať listener pre zmenu orientácie a zmenu veľkosti okna
        window.addEventListener('resize', function() {
            if (modal.classList.contains('open')) {
                adjustMobileHeight();
            }
        });
        
        console.log('Vlastná galéria inicializovaná');
    }
    
    /**
     * Spracovanie hlásení o úspechu/chybe
     */
    function handleAlerts() {
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
    }
    
    /**
     * Inicializácia lazy loading pre obrázky v galérii
     */
    function initializeLazyLoading() {
        // Kontrola, či prehliadač podporuje Intersection Observer API
        if ('IntersectionObserver' in window) {
            const lazyImageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        const lazyImage = entry.target;
                        // Nahradenie zástupného obrazca skutočným obrázkom
                        lazyImage.src = lazyImage.dataset.src;
                        
                        // Po načítaní obrázka odstrániť lazy-image classu a pridať loaded classu
                        lazyImage.onload = function() {
                            lazyImage.classList.remove('lazy-image');
                            lazyImage.classList.add('loaded');
                            lazyImage.parentElement.classList.add('loaded-container');
                        };
                        
                        // Prestať sledovať tento obrázok
                        observer.unobserve(lazyImage);
                    }
                });
            }, {
                rootMargin: '100px 0px', // Načítať obrázky 100px pred tým, ako sa zobrazia
                threshold: 0.01 // Spustiť načítanie, keď je viditeľný 1% obrázka
            });
            
            // Sledovať všetky obrázky s lazy-image triedou
            const lazyImages = document.querySelectorAll('.lazy-image');
            lazyImages.forEach(function(lazyImage) {
                lazyImageObserver.observe(lazyImage);
            });
            
            console.log('Lazy loading inicializovaný pre ' + lazyImages.length + ' obrázkov');
            
            // Inicializácia náhľadov videí
            initializeVideoThumbnails();
        } else {
            // Fallback pre prehliadače, ktoré nepodporujú Intersection Observer
            const lazyImages = document.querySelectorAll('.lazy-image');
            lazyImages.forEach(function(lazyImage) {
                lazyImage.src = lazyImage.dataset.src;
                lazyImage.classList.remove('lazy-image');
                lazyImage.classList.add('loaded');
                lazyImage.parentElement.classList.add('loaded-container');
            });
            
            // Fallback pre náhľady videí
            initializeVideoThumbnails();
            
            console.log('Lazy loading nie je podporovaný, načítavanie ' + lazyImages.length + ' obrázkov štandardným spôsobom');
        }
    }
    </script>
</body>
</html>