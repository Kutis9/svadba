<?php
// Priečinok s nahranými súbormi
$uploadDir = 'uploads/';

// Kontrola existencie priečinka
if (!file_exists($uploadDir)) {
    echo '<p class="no-photos">Zatiaľ neboli pridané žiadne fotky.</p>';
    exit;
}

// Podporované typy súborov - rozšírené o formáty mobilných zariadení
$supportedExtensions = [
    'jpg', 'jpeg', 'png', 'gif', 
    'webp',  // WebP formát používaný na Android
    'heic', 'heif',  // HEIC/HEIF formáty používané na iPhone
    'bmp',   // BMP formát
    'tiff', 'tif'  // TIFF formáty
];

// Získanie zoznamu súborov
$files = scandir($uploadDir);
$images = [];

// Filtrovanie len obrazových súborov
foreach ($files as $file) {
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (in_array($extension, $supportedExtensions)) {
        // Získanie metadata súboru
        $baseName = pathinfo($file, PATHINFO_FILENAME);
        $metadataFile = $uploadDir . $baseName . '.json';
        $metadata = [];
        
        if (file_exists($metadataFile)) {
            $metadata = json_decode(file_get_contents($metadataFile), true);
        }
        
        $images[] = [
            'file' => $file,
            'path' => $uploadDir . $file,
            'name' => isset($metadata['name']) ? $metadata['name'] : 'Anonym',
            'date' => isset($metadata['date']) ? $metadata['date'] : null
        ];
    }
}

// Zoradenie od najnovších
usort($images, function($a, $b) {
    if ($a['date'] == $b['date']) return 0;
    return ($a['date'] > $b['date']) ? -1 : 1;
});

// Výpis fotiek
if (count($images) > 0) {
    foreach ($images as $image) {
        // Získanie rozmerov obrázka
        $imgPath = $image['path'];
        $imgSize = @getimagesize($imgPath);
        $width = isset($imgSize[0]) ? $imgSize[0] : 800;
        $height = isset($imgSize[1]) ? $imgSize[1] : 600;
        
        // Vygenerujeme náhľad pre rýchlejšie načítanie
        $thumbnailPath = $image['path']; // Môžeme vytvoriť aj separátny priečinok s náhľadmi
        
        echo '<div class="photo-item">';
        echo '<a href="' . htmlspecialchars($imgPath) . '" 
                 data-pswp-width="' . $width . '" 
                 data-pswp-height="' . $height . '" 
                 target="_blank">';
        // Lazy loading pomocou data-src atribútu
        echo '<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" 
                   data-src="' . htmlspecialchars($thumbnailPath) . '" 
                   alt="Svadobná fotka" 
                   class="lazy-image">';
        echo '</a>';
        echo '<div class="photo-info">';
        echo '<span class="photo-author">' . htmlspecialchars($image['name']) . '</span>';
        if ($image['date']) {
            echo '<span class="photo-date">' . date('d.m.Y H:i', strtotime($image['date'])) . '</span>';
        }
        echo '</div>';
        echo '</div>';
    }
} else {
    echo '<p class="no-photos">Zatiaľ neboli pridané žiadne fotky.</p>';
}

// Pridáme template pre PhotoSwipe 5.x
echo '
<!-- PhotoSwipe template -->
<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="pswp__bg"></div>
    <div class="pswp__scroll-wrap">
        <div class="pswp__container">
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
        </div>
        <div class="pswp__ui pswp__ui--hidden">
            <div class="pswp__top-bar">
                <div class="pswp__counter"></div>
                <button class="pswp__button pswp__button--close" title="Zavrieť (Esc)"></button>
                <button class="pswp__button pswp__button--share" title="Zdieľať"></button>
                <button class="pswp__button pswp__button--fs" title="Na celú obrazovku"></button>
                <button class="pswp__button pswp__button--zoom" title="Priblížiť/Oddialiť"></button>
                <div class="pswp__preloader">
                    <div class="pswp__preloader__icn">
                        <div class="pswp__preloader__cut">
                            <div class="pswp__preloader__donut"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
                <div class="pswp__share-tooltip"></div>
            </div>
            <button class="pswp__button pswp__button--arrow--left" title="Predchádzajúca (šípka vľavo)"></button>
            <button class="pswp__button pswp__button--arrow--right" title="Nasledujúca (šípka vpravo)"></button>
            <div class="pswp__caption">
                <div class="pswp__caption__center"></div>
            </div>
        </div>
    </div>
</div>';
?>