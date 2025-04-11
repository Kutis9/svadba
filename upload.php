<?php
// Zapnutie zobrazovania chýb pre ladenie
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ladenie - uložíme si všetky informácie o požiadavke
file_put_contents('upload_debug.log', "===== UPLOAD REQUEST =====" . PHP_EOL . 
                  "Time: " . date('Y-m-d H:i:s') . PHP_EOL .
                  "REQUEST: " . print_r($_REQUEST, true) . PHP_EOL . 
                  "FILES: " . print_r($_FILES, true) . PHP_EOL);

// Kontrola metódy požiadavky
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<h1>Metóda nie je povolená</h1>";
    echo "<p>Späť na <a href='index.php'>hlavnú stránku</a></p>";
    exit;
}

// Kontrola adresára pre nahrávanie
$uploadDir = 'uploads/';
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        file_put_contents('upload_debug.log', "Failed to create directory: $uploadDir" . PHP_EOL, FILE_APPEND);
        echo "<h1>Chyba</h1>";
        echo "<p>Nepodarilo sa vytvoriť adresár pre nahrávanie</p>";
        echo "<p>Späť na <a href='index.php'>hlavnú stránku</a></p>";
        exit;
    }
}

// Nastavenie práv
chmod($uploadDir, 0777);

// Získanie metadát
$author = isset($_POST['author']) ? trim($_POST['author']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Inicializácia premenných
$uploadedFiles = [];
$errors = [];

// Spracovanie nahraných súborov
if (isset($_FILES['media']) && !empty($_FILES['media']['name'][0])) {
    $fileCount = count($_FILES['media']['name']);
    
    // Podporované typy súborov
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov', 'heic', 'heif'];
    $maxFileSize = 50 * 1024 * 1024; // 50 MB
    
    for ($i = 0; $i < $fileCount; $i++) {
        $fileName = $_FILES['media']['name'][$i];
        $fileSize = $_FILES['media']['size'][$i];
        $fileTmpName = $_FILES['media']['tmp_name'][$i];
        $fileError = $_FILES['media']['error'][$i];
        
        file_put_contents('upload_debug.log', "Processing file: $fileName, size: $fileSize, tmp: $fileTmpName, error: $fileError" . PHP_EOL, FILE_APPEND);
        
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Kontrola chýb
        if ($fileError !== 0) {
            $errors[] = "Chyba pri nahrávaní súboru $fileName. Kód chyby: $fileError";
            file_put_contents('upload_debug.log', "Upload error: $fileError" . PHP_EOL, FILE_APPEND);
            continue;
        }
        
        // Kontrola veľkosti
        if ($fileSize > $maxFileSize) {
            $errors[] = "Súbor $fileName je príliš veľký. Maximálna veľkosť je 50 MB.";
            file_put_contents('upload_debug.log', "File too large: $fileSize > $maxFileSize" . PHP_EOL, FILE_APPEND);
            continue;
        }
        
        // Kontrola typu súboru
        if (!in_array($fileExt, $allowedExtensions)) {
            $errors[] = "Súbor $fileName má nepovolený formát. Povolené formáty: " . implode(', ', $allowedExtensions);
            file_put_contents('upload_debug.log', "Invalid file type: $fileExt" . PHP_EOL, FILE_APPEND);
            continue;
        }
        
        // Generovanie unikátneho názvu súboru
        $newFileName = uniqid('svadba_', true) . '.' . $fileExt;
        $destination = $uploadDir . $newFileName;
        
        file_put_contents('upload_debug.log', "Trying to move file to: $destination" . PHP_EOL, FILE_APPEND);
        
        // Nahratie súboru
        if (move_uploaded_file($fileTmpName, $destination)) {
            file_put_contents('upload_debug.log', "File moved successfully" . PHP_EOL, FILE_APPEND);
            $uploadedFiles[] = $destination;
            
            // Nastavenie práv pre nahraný súbor
            chmod($destination, 0666);
            
            // Uloženie metadát (pre jednoduchosť ako textový súbor)
            if (!empty($author) || !empty($message)) {
                $metadataFile = $uploadDir . pathinfo($newFileName, PATHINFO_FILENAME) . '.txt';
                $metadata = [
                    'Autor' => $author,
                    'Správa' => $message,
                    'Dátum nahratia' => date('Y-m-d H:i:s'),
                    'Pôvodný názov' => $fileName
                ];
                
                file_put_contents($metadataFile, json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                chmod($metadataFile, 0666); // Nastavenie práv pre metadáta
                
                file_put_contents('upload_debug.log', "Metadata saved to: $metadataFile" . PHP_EOL, FILE_APPEND);
            }
        } else {
            $errors[] = "Chyba pri nahrávaní súboru $fileName.";
            file_put_contents('upload_debug.log', "Failed to move file from $fileTmpName to $destination" . PHP_EOL, FILE_APPEND);
            file_put_contents('upload_debug.log', "is_uploaded_file: " . (is_uploaded_file($fileTmpName) ? "true" : "false") . PHP_EOL, FILE_APPEND);
            file_put_contents('upload_debug.log', "is_writeable dir: " . (is_writable($uploadDir) ? "true" : "false") . PHP_EOL, FILE_APPEND);
        }
    }
} else {
    file_put_contents('upload_debug.log', "No files selected" . PHP_EOL, FILE_APPEND);
    $errors[] = 'Neboli vybrané žiadne súbory.';
}

// Odpoveď
file_put_contents('upload_debug.log', "Errors: " . print_r($errors, true) . PHP_EOL, FILE_APPEND);
file_put_contents('upload_debug.log', "Uploaded files: " . print_r($uploadedFiles, true) . PHP_EOL, FILE_APPEND);

// Zobrazenie výsledku priamo na stránke namiesto presmerovania
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Výsledok nahrávania</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #e83e8c;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .back {
            margin-top: 20px;
        }
        .debug {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 30px;
            white-space: pre-wrap;
            font-family: monospace;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <h1>Výsledok nahrávania</h1>

    <?php if (empty($errors)): ?>
        <div class="success">
            <?php
            $successCount = count($uploadedFiles);
            echo "Úspešne nahraných $successCount " . ($successCount == 1 ? "súbor" : ($successCount < 5 ? "súbory" : "súborov"));
            ?>
        </div>

        <?php if (!empty($uploadedFiles)): ?>
            <h2>Nahrané súbory:</h2>
            <ul>
                <?php foreach ($uploadedFiles as $file): ?>
                    <li><?php echo htmlspecialchars($file); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php else: ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div class="back">
        <a href="index.php">Späť na hlavnú stránku</a>
    </div>
    
    <div class="debug">
        <h3>Debug informácie:</h3>
        <h4>$_POST:</h4>
        <?php print_r($_POST); ?>
        
        <h4>$_FILES:</h4>
        <?php print_r($_FILES); ?>
    </div>
</body>
</html> 