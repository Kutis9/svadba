## Jazyk
Komunikuj v Slovenskom jazyku

## Hosting
- chcem aby sme boli schopni hostingovat nasu aplikaciu na [forpsi](http://forpsi.sk/)

## Popis aplikácie
- Detailný popis aplikácie a jej funkcií nájdeš v súbore [OpisAplikacie.md](../OpisAplikacie.md)
- Aplikácia slúži na zdieľanie fotiek a videí zo svadby Mišky a Lukáša
- Umožňuje hosťom nahrávať a prezerať mediálne súbory v peknom rozhraní

## Štruktúra projektu
- `index.php` - hlavná stránka pre zobrazenie galérie
- `upload.php` - spracovanie nahrávania súborov
- `css/style.css` - štýly pre aplikáciu
- `js/main.js` - JavaScript funkcionalita
- `grafikaSvadba/` - adresár s grafikou a logom svadby

## Kódové konvencie
- Používaj slovenské komentáre
- Premenné a funkcie pomenúvaj v camelCase
- Preferuj objektovo-orientovaný prístup
- Všetok PHP kód by mal byť kompatibilný s PHP 7.4 a vyššie

## Zabezpečenie
- Vždy validuj a sanitizuj používateľské vstupy
- Kontroluj typy nahrávaných súborov
- Zabezpeč, aby aplikácia neodhalila citlivé informácie

## Optimalizácia
- Obrázky musia byť optimalizované pre rýchle načítanie
- Implementuj lazy loading pre médiá
- Optimalizuj JavaScript kód pre mobilné zariadenia

## Podporované formáty a limity
- Maximálna veľkosť súborov: 50 MB pre obrázky, 300 MB pre videá
- Podporované formáty: JPG, PNG, GIF, WEBP, MP4, MOV, HEIC, HEIF
- Preferované rozlíšenie videí: 1080p alebo nižšie