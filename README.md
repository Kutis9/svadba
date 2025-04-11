# Svadobná Galéria

Jednoduchá webová aplikácia na zdieľanie fotiek a videí zo svadobnej udalosti. Aplikácia umožňuje hosťom nahrávať fotky a videá priamo z mobilných zariadení a zdieľať ich s ostatnými hosťami.

## Funkcie

- Nahrávanie fotiek a videí zo všetkých typov mobilných zariadení
- Podpora pre rôzne formáty súborov (JPG, PNG, GIF, WEBP, MP4, MOV, HEIC, HEIF)
- Pridávanie komentárov a mena autora k súborom
- Ukladanie metadát bez potreby databázy
- Responzívny dizajn pre všetky zariadenia
- Galéria s podporou pre fotky aj videá
- Lightbox pre prezeranie fotiek a prehrávanie videí

## Technické informácie

### Požiadavky

- PHP 8.4 alebo novší
- Webový server (Apache, Nginx)
- Povolené PHP funkcie: `file_put_contents`, `move_uploaded_file`
- Dostatočné práva na zápis do adresára `uploads`

### Použité knižnice

- **FilePond** - Moderná knižnica pre nahrávanie súborov s drag & drop podporou
- **LightGallery** - Lightbox galéria s podporou pre fotky a videá

## Inštalácia

1. Skopírujte všetky súbory na váš webový server
2. Uistite sa, že PHP má práva na zápis do adresára `uploads`
3. Prístup k aplikácii cez webový prehliadač

## Štruktúra projektu

```
svadbova-galeria/
├── css/              # CSS štýly
│   └── style.css     # Hlavný štýlový súbor
├── js/               # JavaScript súbory
│   └── main.js       # Hlavný JavaScript súbor
├── uploads/          # Adresár pre nahrané súbory
├── index.php         # Hlavná stránka aplikácie
├── upload.php        # Script pre spracovanie nahrávania súborov
└── README.md         # Dokumentácia projektu
```

## Ako to funguje

1. Hostia navštívia webovú stránku pomocou odkazu
2. Kliknutím na tlačidlo "Pridaj" sa zobrazí formulár pre nahrávanie
3. Hostia môžu vybrať fotky alebo videá zo svojho zariadenia
4. Voliteľne môžu pridať komentár a svoje meno
5. Po nahratí sa súbory zobrazia v galérii

## Technické detaily

- Nahrané súbory sa ukladajú do adresára `uploads/`
- Metadáta (autor, komentár) sa ukladajú do samostatných textových súborov v JSON formáte
- Pre identifikáciu súborov sa používajú unikátne identifikátory
- Galéria automaticky rozpoznáva typ súboru (fotka/video) a zobrazuje ho vhodným spôsobom

## Rozšírené funkcie (budúce verzie)

- Generovanie náhľadov pre videá
- Automatická optimalizácia veľkosti fotiek
- Podpora pre ďalšie formáty súborov
- Organizácia súborov do albumov

## Autor

© 2023 Svadobná Galéria 