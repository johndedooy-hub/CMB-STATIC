# CMB STATIC — Complete rustige radiowebsite

**Pakket:** `CMB STATIC 1711 0205.zip` · Nederlandse tijd 17-11-2026 02:05 · zelfstandig project, geen extra buitenmap.

## Installeren of updaten (zonder codewerk)

1. Maak eerst een reservekopie van je huidige STATIC-site, met name van `data/` en `uploads/`.
2. Pak de ZIP uit en upload **de inhoud** naar de bestaande STATIC-site. De mappen en bestanden staan meteen in de hoofdmap van de ZIP.
3. Bestaande persoonlijke instellingen worden bewust niet meegestuurd onder de live bestandsnamen `data/config.json`, `data/region.json`, `data/admin-auth.json` of `data/stream-presets.user.json`. Ze worden dus niet overschreven door dit updatepakket.
4. Bij een geheel nieuwe installatie leest de website automatisch `data/config.default.json` en `data/region.default.json`. Bij de eerste wijzigingen worden de persoonlijke instellingen aangemaakt.
5. Open `admin.php` om alles aan te passen. Voor een nieuwe installatie staat de eenmalige code in `data/INSTALLATIECODE.txt`. Gebruik minimaal twaalf tekens voor je eigen beheerwachtwoord. De code wordt bij eerste installatie gedeactiveerd.

**Let op:** zet de site niet live als de `data/`-map rechtstreeks opvraagbaar is. Bij Apache/LiteSpeed beschermt de meegeleverde `.htaccess` deze map. Bij Nginx moet de hoster directe toegang tot `data/` en het uitvoeren van bestanden in `uploads/` blokkeren. PHP 8.1+ met cURL, JSON en Fileinfo wordt aanbevolen.

## Wat kan de website nu?

- Bovenaan: **logo of zendernaam**, compacte speler met kleine cover, actuele artiest/titel, play/pause en uitklapbaar volume.
- Nederlandse datum en tijd **naast** actueel weer, ook op mobiel. Het weer toont subtiele CSS-wolken, regen, sneeuw en onweer, met zelfgekozen plaats.
- Professioneel menu naast maximaal **twaalf slides**, met tekst op de afbeelding of in een aparte strook, inclusief instelbare kleuren en grootte.
- Vier uitgelichte kaarten/tegels, elk met eigen titel, subtitel, label, achtergrondkleur, labelachtergrond, tekstkleur, link en afbeelding.
- Onder de tegels: **één compacte studiobalk** met instelbare teksten en link. De regioagenda en contactinhoud openen via eigen menu-/linkroutes; geen lange extra secties op de homepage.
- Onderin de verborgen/uitklapbare **PL-balk** met privacy, mail, acht smalle en twee brede mediablokjes. De tickerfunctie is behouden en staat standaard **aan**; dit is in beheer aanpasbaar.
- Statische WebP-studioachtergrond als lichte standaard. De oorspronkelijk geüploade afbeeldingen en MP4's zijn meegeleverd en blijven bruikbaar.
- Alle logo-, tekst-, kleur-, formaat-, menu-, stream- en slidevelden zijn via beheer te wijzigen. Ook de kleine tekstlabels van klok, weer en navigatie hebben een eigen beheeronderdeel.

## Streams uit CMB SITE2

`admin.php` bevat twee **complete** streamprofielen: hoofdstream en reserve, inclusief naam, audio-URL, tweede audio-adres van dezelfde zender, stats-URL, backup-metadata-API, JS-bron, poort/mount en hoeszoeker (iTunes/Deezer/Last.fm). Bij storing neemt de volledige reservestream over, dus niet alleen het geluid. De speler haalt de juiste metadata op voor de **actief gekozen** stream en probeert op instelbare intervallen automatisch terug te schakelen wanneer de hoofdzender weer bereikbaar is.

De twaalf bestaande zenderpresets van CMB SITE2 staan in `stream-presets.json`. Kies ze in beheer, vul in bij stream 1 of 2 en klik op **Alles opslaan**. Eigen opgeslagen zenders komen in `data/stream-presets.user.json` zodat een volgende ZIP hun gegevens **niet** overschrijft. De originele presetlijst blijft intact.

De echte beschikbaarheid van externe radiostreams is niet op jouw hosting bevestigd. Sommige providers weigeren een snelle beschikbaarheidscontrole; dat kan terugschakeling vertragen. Alleen praktische tests op jouw host en TV kunnen dit uitwijzen.

## Regioagenda uit CMB SITE2

`region-admin.php` is een apart, beveiligd onderdeel van hetzelfde beheer. Je kunt twee edities van drie onderwerpen invullen, met eigen kopregels, spraaktekst, afbeelding, blur/donkerte, slidevinkje, intro/outro en stijl voor kop en tekst. De openbare route `region-view.php` toont de aangevinkte agendaonderwerpen **alleen wanneer iemand ze kiest via het menu**.

Voor audio maakt de site tekst voor editie 1 en 2, met `---TING---`-markeringen tussen onderdelen zoals in CMB SITE2. De tekst is beschikbaar via `region-text.php?edition=1` en `edition=2`. Indien je externe GitHub-token en repository correct zijn geconfigureerd, kun je vanuit regiobeheer de bestaande GitHub Actions-TTS-workflow starten en de status controleren. De mp3's worden door jouw bestaande workflow aangemaakt, **niet** door PHP in deze website. Werkende TTS-uitvoer is niet lokaal bevestigd.

## API-sleutels: niet in de website

CMB STATIC zoekt hetzelfde `cmb-config.php` als CMB SITE2 **buiten de publieke webmap**, in de thuismap boven `domains`. Een optionele `github-token-tts.php` of `ns-api.php` wordt alleen gelezen vanuit **dezelfde veilige map** als die configuratie. De site herkent de sleutelvelden voor GitHub TTS, NS, OV, verkeer en AI, maar toont hun inhoud nooit in beheer of HTML. Er zitten **geen productie-API-sleutels** in deze ZIP.

Dit is de veilige basis voor eventuele toekomstige OV-, verkeers- en AI-functies; de uitgebreide publieke informatie- en AI-modules van CMB SITE2 zijn bewust nog niet naar de rustigere STATIC-hoofdpagina gekopieerd. Details staan in `API-CONFIG-VOORBEELD.md`.

## Gecontroleerd

- PHP-syntax van alle PHP-bestanden en JavaScript-syntax van alle JS-bestanden.
- HTTP-startpagina, inloggen en eerste installatie, opslaan van nieuwe beheerinstellingen en twee afzonderlijke streamprofielen.
- Opslaan en teruglezen van de regioagenda, opbouwen van spraaktekst met pingelmarkeringen, zichtbaarheid van de agenda en de slides.
- Een nieuwe stream-preset bewaren en een ongeautoriseerde wijzigingsaanvraag weigeren.
- De weergave op desktop- en mobiel formaat zonder horizontale overloop; tijd en weer staan naast elkaar op mobiel.
- ZIP-integriteit, behoud van oorspronkelijke media en ongewijzigde bestandsdatums.

**Nog te testen op jouw hosting:** de echte streamovergangen, GitHub-token/TTS met jouw workflow, het lokale weer via HTTPS en de langdurige Zeus Browser-stresstest. Deze externe verbindingen zijn niet als succesvol geclaimd.

## Documentatie en versiebeheer

`PROJECT_INFO.md` bevat het doorlopende ontwikkelverloop. Bij toekomstige updates gebruiken we `CMB STATIC DDMM HHMM.zip` met het **Nederlandse tijdstip op het moment van oplevering**, zonder `ROOTFIX` of extra buitenmap. Alleen gewijzigde bestanden krijgen nieuwe bestandstijden. Persoonlijke data en uploads mogen niet per ongeluk verdwijnen.


## Laatste update — luxe overlayversie

Deze versie trekt CMB STATIC verder richting jouw nieuwe koers:

- standaard blokhoeken staan op **5 px** voor een strakker, vierkanter beeld;
- de hoofdkleuren staan standaard in **donker, zilver en goud**;
- de bovenbalk is compacter en de **play/pause** en **volume** zitten als twee blokknoppen onder elkaar;
- op mobiel staan **logo/naam** en **tijd/weer** in dezelfde bovenste rij, met vaste cijferbreedte zodat de tijd niet verspringt;
- het menu opent als **matglas-overlay over de slides**, zodat interne inhoud in beeld komt terwijl de muziek blijft spelen;
- voorbeeldpanelen voor **Nieuws (4)**, **Weer (4)**, **Regioagenda (6)**, **Verkeer** en **Extra & contact** zijn toegevoegd;
- de ticker onderin staat weer standaard aan, met alleen nog een compacte studiobalk en een verborgen PL-balk;
- `traffic.php` en `ov.php` zijn toegevoegd als lichte voorbereiding op live verkeers- en OV-inhoud vanuit veilige externe configuratie.


## Versie 5 — responsieve layoutcorrectie (17-11-2026 02:05)

**Oplevering:** `CMB STATIC 1711 0205.zip`

- De bestaande min-width- en CSS-grid-conflicten in de header zijn verwijderd. Klok en weer blijven leesbaar in hun gedeelde kaart bij smalle breedtes, ook met seconden.
- Op mobiel blijven zenderlogo/naam links, tijd/weer rechts en de speler daaronder; de weerkaart gebruikt een compacte tweerijige interne indeling in plaats van twee onmogelijk smalle kolommen.
- De speler heeft iets kleinere, gelijkmatig uitgelijnde bediening; het volumevenster wordt niet meer afgesneden door het spelerframe.
- Het menu wordt ingeklapt op compacte schermen; de voetregel loopt niet meer door elkaar en blijft zichtbaar wanneer je het menu opent.
- De desktop-slideshow vult de hoogte naast het menu, zodat de tegels zonder onnodige lege strook volgen.
- De matglas-overlay vergroot op mobiel tijdelijk de slide-ruimte om de inhoud leesbaar te houden en sluit zonder de radiostream opnieuw te starten.
- De datum schakelt op mobiel naar een korte Nederlandstalige datum; klokcijfers gebruiken tabulaire breedte zodat seconden niet verschuiven.
- Het cacheversienummer voor CSS en JS wordt automatisch afgeleid van de bestandstijd. Hierdoor wordt een nieuwe stylesheet na upload sneller geladen.
- De oude runtime-cachebestanden onder `data/cache` zijn niet langer onderdeel van de installatie-ZIP; de cache wordt bij gebruik automatisch opgebouwd.

**Getest:** CSS-layout met Chromium bij 320, 360, 390, 430, 768, 1024, 1280, 1440 en 1920 CSS-pixels, met lange plaatsnaam en tijd met seconden. Menu-overlay, sluiten, volume-paneel en aanwezigheid van één ongewijzigd audio-element gecontroleerd. JS- en PHP-syntax en een lokale HTTP-startpagina gecontroleerd.

**Nog te controleren op de hosting:** langdurig TV-/Zeus-gebruik en externe weer-, OV-, verkeer- en radiostreamgegevens. Dit is een responsive-update en verandert geen API-sleutels of streaminstellingen.


## Versie 6 — TV-layout en ticker hersteld (17-11-2026 02:47 Nederlandse tijd)

**Oplevering:** `CMB STATIC 1711 0247.zip`. Gebaseerd op `CMB STATIC 1711 0205.zip`.

- Op liggende TV-achtige viewports vanaf 800 CSS-pixels breed en maximaal 820 pixels hoog staat de header weer op één compacte rij: logo, speler, tijd en weer. Dit corrigeert met name 960×540, waarbij de vorige CSS onterecht naar een tabletindeling omschakelde.
- Het linkermenu heeft op lagere TV-schermen compactere knoppen. Alle zes standaardnavigatie-items en de slideshow passen bij 960×540 boven de ticker en de studiobalk. De ingestelde slidehoogte wordt op zulke schermen beperkt door de beschikbare schermhoogte; de ingestelde waarde wordt niet gewijzigd.
- De ticker rekt het CSS-grid niet langer buiten de viewport uit. De scroltekst blijft binnen de beschikbare schermbreedte, ook bij veel of lange ticker-items.
- Onderste balken krijgen automatisch ruimte gereserveerd via één lichte ResizeObserver; bij zeer lage liggende schermen worden ze onderdeel van de normale documentstroom in plaats van inhoud te bedekken. De tekst in de mobiele studiobalk blijft op één regel.
- Matglas-overlay: op compacte TV-schermen passen de vier standaard weer/nieuws-kaarten in het paneel. Bij langere inhoud kan het paneel scrollen en krijgt het toetsenbord-/afstandsbedieningsfocus. De titel van een geopend paneel bevat niet meer de menunummering en de pijl.
- Bestanden gewijzigd: `assets/css/style.css`, `assets/js/app.js`, `index.php`, `LEESMIJ.md` en `PROJECT_INFO.md`. Alle andere PHP-bestanden, presets, standaardsjablonen en de vijf oorspronkelijke uploads zijn byte-voor-byte gelijk aan de bronversie. Er is niets verwijderd en geen live wachtwoord, API-sleutel of andere geheime configuratie toegevoegd.

### Regressievoorwaarden voor iedere volgende versie

1. Test altijd het complete **zichtbare beeld** (breedte én hoogte), minimaal 960×540, 1280×720, 1920×1080, 390×844, 320×568 en 844×390.
2. Controleer bij TV-formaten dat `.hero-layout` (menu en slideshow) niet onder `.bottom-dock` verdwijnt. Controleer ook dat de ticker maximaal viewportbreedte heeft en dat er geen horizontale overflow is.
3. Open en sluit Nieuwsoverlay, Weeroverlay, mobiel menu en volumepaneel; controleer dat het bestaande audio-element aanwezig blijft en de webconsole geen runtimefouten geeft.
4. Laat instellingen, media en reserve-stream volledig ongemoeid bij een layoutupdate; controleer na het ZIP-maken de bestanden en bestandsdatums tegen de vorige release.
5. Test op de echte Android-TV met Zeus Browser na uploaden. Een Chromium-simulatie voorspelt niet alle browser-/firmwareproblemen en is geen langdurige belastingtest.

**Lokale controle uitgevoerd:** visuele Chromium-controle en automatische DOM-asserties op negen resoluties (960×540, 1280×720, 1920×1080, 390×844, 320×568, 844×390, 1024×768, 1440×900, 768×432), plus interacties voor overlay, menu en volume. Deze controle gebruikt inlined lokale assets en dummy/standaardgegevens; live PHP-hosting, werkelijke radio, externe weer- en verkeers-API en de TV-firmware zijn niet als geslaagd geclaimd.
