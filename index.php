<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Svadobná galéria fotiek</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- FilePond CSS -->
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet">
    <!-- PhotoSwipe CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/photoswipe@5.3.2/dist/photoswipe.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Naša svadobná galéria</h1>
            <p>Zdieľajte s nami svoje zábery z našej svadby!</p>
        </header>

        <section id="upload-section">
            <button id="show-upload-form">Pridať fotky</button>
            
            <div id="upload-form-container" style="display: none;">
                <form id="upload-form" action="upload.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="photos">Vyberte fotky:</label>
                        <!-- Nahradíme štandardný file input FilePond komponentom -->
                        <input type="file" id="photos" name="photos[]" class="filepond" multiple required>
                        <small class="form-text">Podporované formáty: JPG, PNG, GIF, WEBP, HEIC, HEIF, BMP, TIFF. <br>
                        Upozornenie: HEIC/HEIF formáty z iPhone nemusia byť zobrazené vo všetkých prehliadačoch.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="name">Vaše meno (nepovinné):</label>
                        <input type="text" id="name" name="name">
                    </div>
                    
                    <button type="submit" id="upload-button">Nahrať fotky</button>
                </form>
            </div>
        </section>

        <section id="gallery">
            <h2>Galéria fotiek</h2>
            <div id="photos-container" class="pswp-gallery">
                <!-- Tu sa dynamicky načítajú fotky -->
                <?php include 'gallery.php'; ?>
            </div>
        </section>
    </div>

    <!-- FilePond JS -->
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-encode/dist/filepond-plugin-file-encode.js"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.js"></script>
    
    <!-- PhotoSwipe JS -->
    <script src="https://cdn.jsdelivr.net/npm/photoswipe@5.3.2/dist/photoswipe.umd.min.js"></script>
    
    <script src="js/main-new.js"></script>
</body>
</html>