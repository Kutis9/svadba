<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Svadobná galéria fotiek</title>
    <link rel="stylesheet" href="css/style.css">
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
                        <input type="file" id="photos" name="photos[]" accept="image/*" multiple required>
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
            <div id="photos-container">
                <!-- Tu sa dynamicky načítajú fotky -->
                <?php include 'gallery.php'; ?>
            </div>
        </section>
    </div>

    <script src="js/main.js"></script>
</body>
</html>