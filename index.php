<?php
// Nastavenie PHP 8.4 kompatibility
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kontrola AJAX požiadavky pre načítanie ďalších fotografií
if (isset($_GET['action']) && $_GET['action'] === 'loadMoreMedia') {
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    
    // Získanie ďalších médiových súborov
    $additionalFiles = getGalleryFiles($limit, $offset);
    
    // Zistíme koľko je celkovo súborov v galérii
    $allFiles = getGalleryFiles(); 
    $totalFiles = count($allFiles);
    
    // Pripravíme HTML kód pre galériu
    $html = '';
    foreach ($additionalFiles as $index => $file) {
        $fileType = $file['type'];
        $filePath = $file['path'];
        $fileDate = date('d.m.Y H:i', $file['date']);
        $isVideo = $fileType === 'video';
        
        $html .= '<div class="gallery-item" data-type="' . $fileType . '" data-index="' . ($index + $offset) . '" 
                 data-path="' . $filePath . '" data-date="' . $fileDate . '">';
        
        if ($isVideo) {
            $html .= '<div class="video-thumbnail">
                        <video class="video-preview" preload="metadata" src="' . $filePath . '#t=0.5" style="display:none;"></video>
                        <canvas class="video-thumbnail-canvas" width="300" height="300"></canvas>
                        <div class="video-placeholder">Video</div>
                        <div class="play-icon"></div>
                    </div>';
        } else {
            $html .= '<img class="lazy-image" 
                     data-src="' . $filePath . '" 
                     src="data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 3 4\'%3E%3C/svg%3E"
                     alt="Fotka zo svadby">';
        }
        
        $html .= '<div class="item-info">
                    <span class="item-date">' . $fileDate . '</span>
                  </div>';
        
        $html .= '</div>';
    }
    
    // Vypočítame, či existujú ďalšie súbory
    $hasMore = ($offset + count($additionalFiles)) < $totalFiles;
    
    // Vrátime JSON odpoveď
    header('Content-Type: application/json');
    echo json_encode([
        'html' => $html,
        'count' => count($additionalFiles),
        'hasMore' => $hasMore,
        'totalLoaded' => $offset + count($additionalFiles),
        'totalFiles' => $totalFiles,
        'debug' => 'Offset: ' . $offset . ', Limit: ' . $limit . ', Total: ' . $totalFiles
    ]);
    exit;
}

// Kontrola existencie adresára uploads
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
    chmod('uploads', 0777);
}

// Funkcia na získanie zoznamu súborov z adresára uploads
function getGalleryFiles($limit = null, $offset = 0) {
    $dir = 'uploads/';
    $files = [];
    
    if (is_dir($dir)) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov', 'heic', 'heif'];
        $allFiles = [];
        
        foreach (scandir($dir) as $file) {
            // Preskočiť aktuálny adresár, rodičovský adresár a súbory s metadátami
            if ($file === '.' || $file === '..' || pathinfo($file, PATHINFO_EXTENSION) === 'txt') {
                continue;
            }
            
            $filePath = $dir . $file;
            
            // Kontrola či ide o súbor
            if (is_file($filePath)) {
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                
                // Kontrola či ide o podporovaný typ súboru
                if (in_array($extension, $allowedExtensions)) {
                    $fileType = in_array($extension, ['mp4', 'mov']) ? 'video' : 'image';
                    
                    $allFiles[] = [
                        'name' => $file,
                        'path' => $filePath,
                        'type' => $fileType,
                        'date' => filemtime($filePath)
                    ];
                }
            }
        }
        
        // Zoradenie podľa dátumu - najnovšie najprv
        usort($allFiles, function($a, $b) {
            return $b['date'] - $a['date'];
        });
        
        // Aplikovanie limitu a offsetu ak sú nastavené
        if ($limit !== null) {
            $files = array_slice($allFiles, $offset, $limit);
        } else {
            $files = $allFiles;
        }
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
    
    <!-- JavaScript súbory -->
    <script src="js/main.js?v=<?php echo $cacheBusting; ?>" defer></script>
</head>
<body>
    <div class="upload-spinner-container">
        <div class="upload-spinner"></div>
        <div class="upload-progress-text">Nahrávanie súborov... Prosím, počkajte.</div>
        <div class="upload-progress-container">
            <div class="upload-progress-bar"></div>
        </div>
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
                    <?php 
                    // Limit pre prvé načítanie - zobrazíme len prvých 10 položiek
                    $initialLimit = 10;
                    $initialFiles = getGalleryFiles($initialLimit, 0);
                    $totalFiles = count(getGalleryFiles()); // Celkový počet súborov
                    
                    foreach ($initialFiles as $index => $file): ?>
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
                
                <?php if ($totalFiles > $initialLimit): ?>
                    <div class="load-more-container">
                        <button id="load-more-btn" class="btn-primary">Zobraziť ďalšie</button>
                        <div id="loading-indicator" style="display: none;">Načítavam...</div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </section>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> Svadba Mišky a Lukáša.</p>
        </footer>
    </div>
    
    <!-- JavaScript -->
    <script>
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
                
                // Nastavenie timeoutu pre dlhé nahrávania (10 minút)
                xhr.timeout = 600000; // 10 minút v milisekundách
                
                // Event listener pre sledovanie priebehu nahrávania
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        const percentComplete = Math.round((e.loaded / e.total) * 100);
                        const progressText = document.querySelector('.upload-progress-text');
                        const progressBar = document.querySelector('.upload-progress-bar');
                        
                        // Aktualizácia textu s percentami
                        if (progressText) {
                            progressText.textContent = `Nahrávanie súborov: ${percentComplete}% (${formatFileSize(e.loaded)} / ${formatFileSize(e.total)})`;
                        }
                        
                        // Aktualizácia progress baru
                        if (progressBar) {
                            progressBar.style.width = percentComplete + '%';
                        }
                    }
                });
                
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
                
                xhr.ontimeout = function() {
                    hideUploadSpinner();
                    showStatusMessage('Vypršal čas pre nahrávanie súborov. Skúste menší súbor alebo skontrolujte pripojenie k internetu.', 'error');
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
        const progressBar = document.querySelector('.upload-progress-bar');
        const progressText = document.querySelector('.upload-progress-text');
        
        if (spinnerContainer) {
            spinnerContainer.style.display = 'flex';
        }
        
        // Reset progress bar
        if (progressBar) {
            progressBar.style.width = '0%';
        }
        
        // Reset progress text
        if (progressText) {
            progressText.textContent = 'Nahrávanie súborov... Prosím, počkajte.';
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
        
        // Stav galérie - uložiť do globálnej premennej pre prístup z iných funkcií
        window.currentGalleryIndex = 0;
        window.customGalleryData = [];
        
        // Naplniť galleryData z HTML elementov
        galleryItems.forEach(item => {
            window.customGalleryData.push({
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
            if (index >= 0 && index < window.customGalleryData.length) {
                window.currentGalleryIndex = index;
                loadMedia(window.customGalleryData[window.currentGalleryIndex]);
                updateControls();
                
                modal.classList.add('open');
                document.body.style.overflow = 'hidden'; // Zabrániť skrolovaniu stránky počas zobrazenia galérie
                
                // Automatické prispôsobenie výšky pre mobilné zariadenia
                adjustMobileHeight();
            }
        }
        
        // Sprístupnenie openGallery globálne pre volanie z updateCustomGallery
        window.openCustomGallery = openGallery;
        
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
            const newIndex = window.currentGalleryIndex + step;
            
            if (newIndex >= 0 && newIndex < window.customGalleryData.length) {
                // Zastaviť video, ak sa prehráva
                const video = mediaContainer.querySelector('video');
                if (video) {
                    video.pause();
                }
                
                window.currentGalleryIndex = newIndex;
                loadMedia(window.customGalleryData[window.currentGalleryIndex]);
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
            counter.textContent = `${window.currentGalleryIndex + 1} / ${window.customGalleryData.length}`;
            dateDisplay.textContent = window.customGalleryData[window.currentGalleryIndex].date;
            
            // Zobrazenie/skrytie navigačných tlačidiel
            prevBtn.style.visibility = window.currentGalleryIndex > 0 ? 'visible' : 'hidden';
            nextBtn.style.visibility = window.currentGalleryIndex < window.customGalleryData.length - 1 ? 'visible' : 'hidden';
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
        
        // Pridať podporu pre dotykové gestá (swipe)
        let touchStartX = 0;
        let touchEndX = 0;
        let touchStartY = 0;
        let touchEndY = 0;
        
        // Sledovať dotykové gestá pre celý modal, nielen pre kontajner médií
        modal.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
            touchStartY = e.changedTouches[0].screenY;
        }, { passive: true });
        
        modal.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            touchEndY = e.changedTouches[0].screenY;
            handleSwipe();
        }, { passive: true });
        
        function handleSwipe() {
            const swipeThreshold = 30; // Znížený prah pre citlivejšie gestá
            const verticalThreshold = 75; // Prah pre vertikálny pohyb
            
            // Vypočítať horizontálny a vertikálny rozdiel
            const horizontalDiff = touchEndX - touchStartX;
            const verticalDiff = Math.abs(touchEndY - touchStartY);
            
            // Ak je vertikálny pohyb väčší ako prah, nerobiť nič (pravdepodobne scrollovanie)
            if (verticalDiff > verticalThreshold) {
                return;
            }
            
            if (horizontalDiff < -swipeThreshold) {
                // Swipe doľava - ďalšia položka
                navigateGallery(1);
            } else if (horizontalDiff > swipeThreshold) {
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
     * Aktualizácia dát v galérii po načítaní nových položiek
     */
    function updateCustomGallery() {
        console.log('Aktualizácia galérie pre nové položky');
        
        const galleryItems = document.querySelectorAll('.gallery-item');
        const modal = document.getElementById('custom-gallery-modal');
        
        if (!modal || !window.customGalleryData) return;
        
        // Resetovať galériu
        window.customGalleryData = [];
        
        // Naplniť galleryData z HTML elementov - vrátane nových
        galleryItems.forEach(item => {
            window.customGalleryData.push({
                type: item.getAttribute('data-type'),
                path: item.getAttribute('data-path'),
                date: item.getAttribute('data-date'),
                index: parseInt(item.getAttribute('data-index'))
            });
        });
        
        // Pridať event listener pre každú položku v galérií (vrátane novo načítaných)
        galleryItems.forEach(item => {
            // Odstrániť existujúce listenery, aby nedochádzalo k duplikácii
            const newItem = item.cloneNode(true);
            item.parentNode.replaceChild(newItem, item);
            
            // Manuálne načítať obrázok z data-src pre novo pridané položky
            const lazyImage = newItem.querySelector('img.lazy-image');
            if (lazyImage && lazyImage.dataset.src) {
                // Vytvoriť nový Image element pre načítanie obrázka
                const tempImg = new Image();
                tempImg.onload = function() {
                    lazyImage.src = lazyImage.dataset.src;
                    lazyImage.classList.remove('lazy-image');
                    lazyImage.classList.add('loaded');
                    lazyImage.removeAttribute('data-src');
                    
                    if (lazyImage.parentElement) {
                        lazyImage.parentElement.classList.add('loaded-container');
                    }
                };
                tempImg.src = lazyImage.dataset.src;
            }
            
            newItem.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'));
                if (typeof window.openCustomGallery === 'function') {
                    window.openCustomGallery(index);
                }
            });
        });
    }
    
    /**
     * Inicializácia funkcionality "Zobraziť ďalšie"
     */
    function initializeLoadMore() {
        const loadMoreBtn = document.getElementById('load-more-btn');
        const loadingIndicator = document.getElementById('loading-indicator');
        const galleryContainer = document.getElementById('gallery-container');
        
        // Počiatočný offset pre načítanie ďalších položiek
        let currentOffset = <?php echo isset($initialLimit) ? $initialLimit : 10; ?>;
        const limit = 10; // Počet položiek na načítanie
        
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', function() {
                console.log('Kliknutie na tlačidlo "Zobraziť ďalšie"');
                
                // Skryť tlačidlo a zobraziť indikátor načítavania
                loadMoreBtn.style.display = 'none';
                if (loadingIndicator) {
                    loadingIndicator.style.display = 'block';
                }
                
                // AJAX požiadavka pre načítanie ďalších položiek
                const xhr = new XMLHttpRequest();
                xhr.open('GET', `index.php?action=loadMoreMedia&offset=${currentOffset}&limit=${limit}`, true);
                
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            console.log('Server odpoveď:', response);
                            
                            if (response.html && response.count > 0) {
                                // Pridanie nových položiek do galérie
                                galleryContainer.insertAdjacentHTML('beforeend', response.html);
                                
                                // Aktualizovať offset
                                currentOffset += response.count;
                                
                                // Inicializovať lazy loading pre nové obrázky
                                initializeLazyLoading();
                                
                                // Inicializovať náhľady videí pre nové položky
                                initializeVideoThumbnails();
                                
                                // Aktualizovať galériu - KĽÚČOVÁ ZMENA PRE ZOBRAZENIE NOVÝCH FOTIEK V GALÉRIÍ
                                updateCustomGallery();
                                
                                console.log(`Načítaných ${response.count} nových položiek, celkovo ${response.totalLoaded}/${response.totalFiles}`);
                                
                                // Zobraziť tlačidlo "Zobraziť ďalšie" len ak existujú ďalšie položky
                                if (response.hasMore) {
                                    loadMoreBtn.style.display = 'block';
                                }
                            } else {
                                console.log('Žiadne ďalšie položky na načítanie');
                                // Ak nie sú žiadne ďalšie položky, tlačidlo zostane skryté
                            }
                        } catch (e) {
                            console.error('Chyba pri spracovaní JSON odpovede:', e);
                            alert('Nastala chyba pri načítaní ďalších fotografií.');
                            loadMoreBtn.style.display = 'block';
                        }
                    } else {
                        console.error('AJAX chyba:', xhr.status);
                        alert('Nastala chyba pri načítaní ďalších fotografií.');
                        loadMoreBtn.style.display = 'block';
                    }
                    
                    // Skryť indikátor načítavania v každom prípade
                    if (loadingIndicator) {
                        loadingIndicator.style.display = 'none';
                    }
                };
                
                xhr.onerror = function() {
                    console.error('AJAX požiadavka zlyhala');
                    alert('Nastala chyba pri načítaní ďalších fotografií.');
                    loadMoreBtn.style.display = 'block';
                    
                    if (loadingIndicator) {
                        loadingIndicator.style.display = 'none';
                    }
                };
                
                xhr.send();
            });
        }
    }

    /**
     * Inicializácia lazy loading pre obrázky v galérii
     */
    function initializeLazyLoading() {
        console.log('Spúšťa sa lazy loading pre obrázky');
        
        // Kontrola, či prehliadač podporuje Intersection Observer API
        if ('IntersectionObserver' in window) {
            const lazyImageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        const lazyImage = entry.target;
                        console.log('Načítava sa obrázok:', lazyImage.dataset.src);
                        
                        // Nahradenie zástupného obrazca skutočným obrázkom
                        if (lazyImage.dataset.src) {
                            lazyImage.src = lazyImage.dataset.src;
                            
                            // Po načítaní obrázka odstrániť lazy-image classu a pridať loaded classu
                            lazyImage.onload = function() {
                                console.log('Obrázok načítaný:', lazyImage.src);
                                lazyImage.classList.remove('lazy-image');
                                lazyImage.classList.add('loaded');
                                if (lazyImage.parentElement) {
                                    lazyImage.parentElement.classList.add('loaded-container');
                                }
                            };
                            
                            // Pre prípad, že obrázok bol medzičasom načítaný z cache
                            if (lazyImage.complete) {
                                console.log('Obrázok už načítaný (z cache):', lazyImage.src);
                                lazyImage.classList.remove('lazy-image');
                                lazyImage.classList.add('loaded');
                                if (lazyImage.parentElement) {
                                    lazyImage.parentElement.classList.add('loaded-container');
                                }
                            }
                            
                            // Odstrániť atribút data-src, aby sme zabránili opakovanému načítaniu
                            lazyImage.removeAttribute('data-src');
                        }
                        
                        // Prestať sledovať tento obrázok
                        observer.unobserve(lazyImage);
                    }
                });
            }, {
                rootMargin: '200px 0px', // Zvýšené na 200px pre skoršie načítavanie
                threshold: 0.01 // Spustiť načítanie, keď je viditeľný 1% obrázka
            });
            
            // Sledovať všetky obrázky s lazy-image triedou
            const lazyImages = document.querySelectorAll('img.lazy-image');
            console.log('Nájdených lazy-image obrázkov:', lazyImages.length);
            
            lazyImages.forEach(function(lazyImage) {
                lazyImageObserver.observe(lazyImage);
            });
        } else {
            // Fallback pre prehliadače, ktoré nepodporujú Intersection Observer
            console.log('Intersection Observer nie je podporovaný, používa sa fallback');
            
            const lazyImages = document.querySelectorAll('img.lazy-image');
            console.log('Fallback: Nájdených lazy-image obrázkov:', lazyImages.length);
            
            lazyImages.forEach(function(lazyImage) {
                if (lazyImage.dataset.src) {
                    lazyImage.src = lazyImage.dataset.src;
                    lazyImage.removeAttribute('data-src');
                    lazyImage.classList.remove('lazy-image');
                    lazyImage.classList.add('loaded');
                    
                    if (lazyImage.parentElement) {
                        lazyImage.parentElement.classList.add('loaded-container');
                    }
                }
            });
        }
    }

    /**
     * Inicializácia náhľadov videí - globálna verzia funkcie
     */
    function initializeVideoThumbnails() {
        const videoItems = document.querySelectorAll('.gallery-item[data-type="video"]');
        
        console.log('Inicializujem náhľady videí pre ' + videoItems.length + ' videí');
        
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
                ctx.fillStyle = '#000000';
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

    // Po načítaní dokumentu inicializovať všetky funkcie
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializovať vlastnú galériu
        initializeCustomGallery();
        
        // Inicializovať formulár pre nahrávanie
        initializeUploadForm();
        
        // Inicializovať spracovanie súborov pre nahrávanie
        initializeFileInput();
        
        // Inicializovať lazy loading obrázkov
        initializeLazyLoading();
        
        // Inicializovať náhľady videí
        initializeVideoThumbnails();
        
        // Inicializovať "Zobraziť ďalšie" funkcionalitu
        initializeLoadMore();
        
        console.log('Aplikácia bola načítaná');
    });
    </script>
</body>
</html>