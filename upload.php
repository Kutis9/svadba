<?php
// Nastavenie maximálnej veľkosti súboru (10MB)
$maxFileSize = 10 * 1024 * 1024;

// Povolené typy súborov - rozšírené o formáty mobilných zariadení
$allowedTypes = [
    'image/jpeg', 
    'image/jpg', 
    'image/png', 
    'image/gif', 
    'image/webp',        // WebP formát používaný na Android
    'image/heic',        // HEIC formát používaný na iPhone
    'image/heif',        // HEIF formát používaný na iPhone
    'image/bmp',         // BMP formát
    'image/tiff',        // TIFF formát
    'image/tif',         // TIFF formát
];

// Priečinok na ukladanie súborov
$uploadDir = 'uploads/';

// Kontrola existencie priečinka
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Inicializácia odpovedi
$response = [
    'success' => false,
    'message' => '',
    'files' => []
];

// Kontrola, či boli poslané súbory
if (!isset($_FILES['photos'])) {
    $response['message'] = 'Neboli vybrané žiadne súbory.';
    echo json_encode($response);
    exit;
}

// Získanie informácií o používateľovi
$name = isset($_POST['name']) ? htmlspecialchars($_POST['name']) : 'Anonym';

// Spracovanie nahraných súborov
$files = $_FILES['photos'];
$successCount = 0;

for ($i = 0; $i < count($files['name']); $i++) {
    // Kontrola chyby nahrávania
    if ($files['error'][$i] !== UPLOAD_ERR_OK) {
        continue;
    }

    // Kontrola veľkosti súboru
    if ($files['size'][$i] > $maxFileSize) {
        continue;
    }

    // Kontrola typu súboru
    if (!in_array($files['type'][$i], $allowedTypes)) {
        continue;
    }

    // Generovanie unikátneho názvu súboru
    $fileName = time() . '_' . $i . '_' . basename($files['name'][$i]);
    $targetFile = $uploadDir . $fileName;

    // Nahrávanie súboru
    if (move_uploaded_file($files['tmp_name'][$i], $targetFile)) {
        $successCount++;
        
        // Uloženie metadát
        $metadata = [
            'file' => $fileName,
            'name' => $name,
            'date' => date('Y-m-d H:i:s')
        ];
        
        // Uloženie metadát do JSON súboru
        $metadataFile = $uploadDir . pathinfo($fileName, PATHINFO_FILENAME) . '.json';
        file_put_contents($metadataFile, json_encode($metadata));
        
        $response['files'][] = [
            'file' => $fileName,
            'url' => $targetFile
        ];
    }
}

if ($successCount > 0) {
    $response['success'] = true;
    $response['message'] = "Úspešne nahraných $successCount " . ($successCount == 1 ? "súbor" : ($successCount < 5 ? "súbory" : "súborov"));
} else {
    $response['message'] = 'Nepodarilo sa nahrať žiadne súbory.';
}

// Odpoveď vo formáte JSON
header('Content-Type: application/json');
echo json_encode($response);