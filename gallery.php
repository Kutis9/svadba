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
        echo '<div class="photo-item">';
        echo '<img src="' . htmlspecialchars($image['path']) . '" alt="Svadobná fotka" loading="lazy">';
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
?>