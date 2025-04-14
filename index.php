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
    <title>Naša svadba - Zdieľajte s nami spomienky</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="grafikaSvadba/Designer-removebg-preview.png">
    
    <!-- Základné CSS -->
    <link rel="stylesheet" href="css/style.css?v=<?php echo $cacheBusting; ?>">
    
    <!-- Lightgallery CSS pre galériu -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.1/css/lightgallery.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.1/css/lg-zoom.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.1/css/lg-video.css">
</head>
<body>
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
                <div id="gallery-container">
                    <?php foreach ($galleryFiles as $file): ?>
                        <?php 
                        $isVideo = $file['type'] === 'video';
                        $thumbnailPath = $file['path'];
                        ?>
                        
                        <div class="gallery-item" data-type="<?php echo $file['type']; ?>">
                            <a href="<?php echo $file['path']; ?>" 
                               data-lg-size="1600-1067"
                               <?php if ($isVideo): ?>
                               data-video='{"source": [{"src":"<?php echo $file['path']; ?>", "type":"video/mp4"}], "attributes": {"preload": false, "controls": true}}'
                               <?php endif; ?>>
                                
                                <?php if ($isVideo): ?>
                                    <div class="video-thumbnail">
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
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> Svadba Mišky a Lukáša. Ďakujeme.</p>
        </footer>
    </div>
    
    <!-- Lightgallery JS pre galériu -->
    <script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.1/lightgallery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.1/plugins/zoom/lg-zoom.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.1/plugins/video/lg-video.min.js"></script>
    
    <!-- JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Aplikácia bola načítaná');
        
        // Inicializácia formulára
        initializeUploadForm();
        
        // Inicializácia LightGallery
        initializeLightGallery();
        
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
        
        if (fileInput && selectedFilesContainer) {
            fileInput.addEventListener('change', function() {
                selectedFilesContainer.innerHTML = '';
                
                if (this.files.length > 0) {
                    const fileList = document.createElement('ul');
                    fileList.className = 'file-list';
                    
                    for (let i = 0; i < this.files.length; i++) {
                        const file = this.files[i];
                        const fileItem = document.createElement('li');
                        fileItem.className = 'file-item';
                        
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
                        fileInfo.textContent = file.name + ' (' + formatFileSize(file.size) + ')';
                        fileItem.appendChild(fileInfo);
                        
                        fileList.appendChild(fileItem);
                    }
                    
                    selectedFilesContainer.appendChild(fileList);
                }
            });
        }
        
        if (uploadForm) {
            uploadForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const fileInput = document.getElementById('media');
                if (fileInput && fileInput.files.length === 0) {
                    showStatusMessage('Prosím, vyberte aspoň jeden súbor na nahratie.', 'error');
                    return;
                }
                
                // Zobraziť stav nahrávania
                showStatusMessage('Nahrávanie súborov... Prosím, počkajte.', 'info');
                
                // Použitie FormData pre AJAX nahrávanie
                const formData = new FormData(uploadForm);
                
                // AJAX požiadavka na nahratie súborov
                const xhr = new XMLHttpRequest();
                xhr.open('POST', uploadForm.getAttribute('action'), true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                
                xhr.onload = function() {
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
     * Formátovanie veľkosti súboru do čitateľnej podoby
     */
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(1024));
        
        return parseFloat((bytes / Math.pow(1024, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    /**
     * Inicializácia LightGallery
     */
    function initializeLightGallery() {
        const galleryContainer = document.getElementById('gallery-container');
        if (galleryContainer && typeof lightGallery !== 'undefined') {
            lightGallery(galleryContainer, {
                selector: 'a',
                plugins: [lgZoom, lgVideo],
                download: false
            });
        }
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
                            // Pridať triedu na rodičovský 'a' element aby sme mohli skryť spinner
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
        } else {
            // Fallback pre prehliadače, ktoré nepodporujú Intersection Observer
            const lazyImages = document.querySelectorAll('.lazy-image');
            lazyImages.forEach(function(lazyImage) {
                lazyImage.src = lazyImage.dataset.src;
                lazyImage.classList.remove('lazy-image');
                lazyImage.classList.add('loaded');
                lazyImage.parentElement.classList.add('loaded-container');
            });
            
            console.log('Lazy loading nie je podporovaný, načítavanie ' + lazyImages.length + ' obrázkov štandardným spôsobom');
        }
    }
    </script>
</body>
</html>