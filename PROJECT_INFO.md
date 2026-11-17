# Projectoverzicht & Handleiding – Radio Portal Basis

**Aangemaakt op:** 12-11-2026 om 01:10:18

## Wat is er al gebouwd (Onthouden Status)
1. **Volledig Responsive Basisontwerp:** Portal-indeling met bovenbalk, zijmenu, grote hero-slider, losse strakke tegels en een welkomstblok.
2. **Bovenbalk (Topbar):**
   - Logo- en zendernaamweergave.
   - Live radiostream-speler met Start/Pauze-knop en volumeregelaar.
   - Snelknoppen voor **Verkeer**, **OV** en **WhatsApp**.
   - Live klok en weerblok.
3. **Bewegende Achtergrond:** Ondersteuning voor het afspelen van een achtergrondvideo (MP4) over het hele scherm.
4. **Centraal CMS Beheerpaneel:** Via `admin.php` zijn alle kleuren, lettertypes, lettergroottes, media-uploads (video's, logo's, slides, tegels), teksten en links direct aan te passen zonder technische kennis.
5. **Bestandsopslag zonder zware database:** Alle instellingen worden opgeslagen in `data/config.json`.

## Wat jij moet doen voor installatie op je hosting
1. **Uitpakken:** Pak dit ZIP-archief uit op je computer.
2. **Uploaden:** Upload alle uitgepakte bestanden en mappen naar de hoofdmap van je website op je hosting (meestal `public_html` of `www`).
3. **Schrijfrechten controleren:** Zorg dat de mappen `data` en `uploads` schrijfrechten hebben (CHMOD 755 of 775), zodat het beheerpaneel nieuwe afbeeldingen en wijzigingen kan opslaan.
4. **Beheren:** Open je website in de browser en klik in het menu op **CMS Beheer** (of ga naar `jouwdomein.nl/admin.php`) om je eigen logo, achtergrondvideo, foto's en radiostream in te stellen.

## Nog openstaande vervolgstappen (voor later)
- Verdere invulling van de pagina-indeling onder de tegels (zoals DJ-overzicht en nieuws/events).
- Wachtwoordbeveiliging toevoegen op het CMS-beheerpaneel.

---

# Versie 2 — zelfstandig CMB STATIC studioportal (16 november 2026)

**Pakketversie:** CMB STATIC 1611 2153.zip · Nederlandse tijd 16-11-2026 21:53


**Let op:** dit deel is de nieuwe actuele projectstatus. Alle beschrijvingen hierboven zijn de historische status van 12 november. De oude opmerkingen over een open beheerpaneel of een bovenbalk met OV/WhatsApp zijn inmiddels achterhaald.

## Doel

Een fraaie, rustige radiowebsite met een volledige eigen beheeromgeving, afgeleid van de aangeleverde STATIC-portal. Geen afhankelijkheid van CMB SITE2, en geen doorontwikkeling van een eerdere zijtak. Alles direct bruikbaar op telefoon, desktop en TV met nadruk op een zuinige studioachtergrond.

## Wat is veranderd

1. **Volledig nieuwe bovenbalk:** links logo en zender, centraal een compacte live player met kleine cover, artiest en titel, play/pause en uitklapbaar volume; rechts NL tijd, datum en live weer.
2. **Levend weerbeeld:** Open-Meteo met automatische plaatszoeker, 10 minuten servercache en onopvallende CSS-effecten voor zon, wolken, regen, sneeuw, mist en onweer. Geen continu achtergrondvideo nodig.
3. **Nieuw professioneel zijmenu:** alle knoppen vanuit beheer; OV en verkeer in menu in plaats van bovenbalk. WhatsApp onderaan bij contact.
4. **Slideshow max. 12:** handmatig toevoegen/verwijderen, afbeeldingsupload en tekstmodus over afbeelding, eronder, beide of uit. Kleur en titelgrootte instelbaar.
5. **Mediategels opnieuw ontworpen:** subtitel, eigen labelkleuren (waaronder groen), eigen achtergrondkleuren, tekstkleuren, afbeeldingen en links.
6. **Nieuwe lichte studioachtergrond:** eigen WebP van circa 60 KB, met microfoon/mengpaneel, plus oude instelbare video als optie.
7. **PL-balk uit het CMB-principe:** permanent privacy en mail; 8 smalle en 2 brede instelbare blokken, opklapbaar. Onafhankelijke doorlopende ticker erboven.
8. **Regioagenda:** twee edities met zes onderwerpen, kaarten op site, optioneel gebruiken in slideshow, download per editie als tekst. TTS/GitHub van de grote CMB is niet overgenomen.
9. **Volledig nieuw veilig beheer:** installatiecode, wachtwoord, CSRF-controle, upload-whitelist, beveiligde gegevensmap, wijzigingen in alle genoemde onderdelen via formulieren en dynamisch toevoegen/verwijderen.
10. **Slimme audiostream:** primaire en optionele reserve-stream, automatische failover uitsluitend bij fouten, metadata via ingestelde API met 12-seconden gedeelde cache.
11. **Responsive:** small-screen menu, speler en weer in aparte overzichtelijke blokken; TV-browsers ontzien met één rustige achtergrondafbeelding en CSS-transities.
12. **Compatibiliteit:** originele 5 bestanden onder uploads ongewijzigd; originele configuratie ook als `data/config-original-1211.json`; historisch `default.php` ongewijzigd.

## Getest en gecontroleerd

- Alle PHP-bestanden: `php -l` geslaagd.
- JavaScript bestanden: Node-syntaxcontrole geslaagd.
- PHP localhost: startpagina en admin bereikbaar.
- Beveiligde eerste installatie: code geaccepteerd, wachtwoord opgeslagen, setupcode verwijderd; menu en instellingen bewerkbaar.
- Beheer: 175 formulierenvelden opgeslagen, wijzigingen in `data/config.json` teruggevonden; regionale export bereikbaar na login.
- Browser-simulatie: desktop 1440px, mobiel 390px, TV 860px, zonder horizontale overflow en zonder JavaScript-runtimefouten. Mobiel menu en PL-balk kunnen open.
- Niet live op externe hosting getest. Externe weer-API en echte radioservers moeten op de hosting gecontroleerd worden.

## Voor volgende updates

- Behoud ZIP zonder gedateerde buitenmap.
- Bewaak `data/config.json`, wachtwoordbestand en uploads tegen overschrijven tijdens installatie.
- Alleen echt gewijzigde bestanden nieuwe tijdstempel; originele uploads niet aanpassen.
- Bij alle updates dit logboek aanvullen; `LEESMIJ.md` synchroon houden.

---

# Versie 3 — compactere STATIC met CMB SITE2-streams en regioagenda

**Oplevering:** `CMB STATIC 1611 2251.zip` · Nederlandse tijd 16 november 2026.

## Uitgangspunten en scheiding

- CMB STATIC blijft zelfstandig, los van de uitgebreide CMB SITE2-site. De eerdere ontwikkelgeschiedenis hierboven blijft ter referentie; de omschreven beperkingen uit eerdere versies zijn alleen historisch.
- Compacte hoofdpagina zonder aparte regio-, contact- of footerkaarten onder de vier uitgelichte tegels. De studiobalk en uitklapbare PL-balk zijn behouden. Ticker bestaat nog, maar staat standaard uit.
- Mobiel: tijd en weer in twee kolommen binnen dezelfde rij, met Nederlandse datum en dynamische weersymboliek. Strakker gemaakte navigatie en kortere visuele blokken.
- Vaste koppen, kleine labels en teksten zijn via een apart blok in beheer aanpasbaar. Logo's, kleuren en achtergronden zijn via het bestaande beheer aanpasbaar gebleven.

## Overgenomen en verder ontwikkeld

1. `stream-presets.json` met twaalf CMB SITE2-presets; in beheer een keuzelijst voor stream 1 en stream 2.
2. Twee afzonderlijke, complete streamprofielen met audio, alternatief audio-adres, hoofd/backup metadata, jsSrc, jsPort en cover-API. Geen gemengd geluid of generieke metadata van de verkeerde stream.
3. Automatische failover en voorzichtige automatische controle voor terugschakelen. Trackmetadata-cache per stream, coverlookup per provider.
4. Regioagenda met twee edities van drie onderwerpen. Alle invoer via `region-admin.php`, gedeelde login en CSRF; per onderwerp tekst, kop, beeld, donkerte/blur en slidekeuze.
5. Vaste intro/outro, pingelmarkeringen in spraaktekst, openbare tekstuitvoer per editie, GitHub workflow dispatch en statuscontrole **indien** externe repo en token beschikbaar zijn.
6. Externe configuratie (boven `domains`, buiten `public_html`) voor bestaande en toekomstige NS-, OV-, verkeers-, AI- en GitHub-API's; geen geheimen in de frontend of het ZIP-archief.
7. Nieuwe standaardbestanden `data/config.default.json` en `data/region.default.json` in plaats van het overschrijven van werkelijke gebruikersinstellingen. Eigen zenderpresets gaan naar `data/stream-presets.user.json`.
8. Bestaande uploads, oude media, PHP-bestandstructuur en wachtwoordbeveiliging behouden. API-voorbeeld-documentatie toegevoegd.

## Regressie- en functionaliteitscontroles

- PHP syntax gecontroleerd; JavaScript via Node nagekeken.
- Eerste beheerinstallatie en inloggen lokaal getest.
- Twee streaminstellingen, labelteksten en andere beheeropties via POST opgeslagen en uit het gegevensbestand teruggelezen.
- Regioagenda-opslag, spraaktekst-export (met `---TING---` tussen onderwerpen), publieke regiopagina en slides getest.
- Nieuwe preset op eigen gebruikersbestand opgeslagen; unauthenticated POST naar presets geblokkeerd.
- Browserweergave desktop en mobiel gecontroleerd: geen horizontale overflow of JavaScript-paginafouten. Tijd en weer staan op mobiel naast elkaar.
- Automatische beschikbaarheidscontrole, echte radiostreams, actuele weerprovider, API-secrets op jouw hosting en GitHub/TTS-output vereisen na plaatsing een externe praktijkproef.
- Archief zonder buitenmap, originele bestanden behouden (behalve expliciet omgezette instellingen-sjablonen), alleen gewijzigde of nieuwe bestanden met nieuwe ZIP-tijdstempels.

## Vanaf nu

Het actieve project is CMB STATIC. De drie live gegevensbestanden `data/config.json`, `data/region.json` en `data/stream-presets.user.json` plus `data/admin-auth.json` worden bewust niet in een release-ZIP opgenomen: de volledige website heeft **standaardsjablonen** en maakt gebruikersgegevens zelf aan bij eerste gebruik of een save. Daarmee kun je de hele release uploaden zonder je opgeslagen persoonlijke gegevens per ongeluk te vervangen. Bewaar altijd eerst een eigen reservekopie.


# Versie 4 — luxe zilver/goud overlay-layout

**Oplevering:** `CMB STATIC 1711 0104.zip` · Nederlandse tijd 17 november 2026.

## Hoofdpunten

1. Nieuwe **luxe donkere zilver/goud-look** met compactere bovenbalk en standaard blokafronding van **5 px**.
2. Mobiele bovenbalk herschikt: **logo/naam** en **tijd/weer** staan naast elkaar op de eerste rij; de speler volgt daaronder.
3. Verticale bedieningskolom in de speler: **play/pause** en **volume** elk in een eigen blokje, met uitklapbare volumeregeling.
4. Nieuwe **luxueuze achtergrond** als standaard WebP, met zwaardere blur/glass-uitstraling maar lichte runtime-belasting.
5. Menu-items openen nu als **matglas lightbox-panelen over de slides** in plaats van lange scrollsecties. De muziek blijft doorlopen.
6. Voorbeeldinhoud voor de gevraagde panelen: **Nieuws (4 tegels)**, **Weer (4 tegels)**, **Regioagenda (6 tegels)**, **Verkeer** met uitklapstukken voor auto/trein/OV, en **Extra & contact**.
7. Alleen een **compacte studiobalk** onder de vier uitgelichte tegels; de PL-balk blijft aanwezig maar staat visueel verborgen en uitklapbaar.
8. `stream-presets.json` opnieuw gevuld vanuit CMB SITE2; nieuwe lichte endpoints `traffic.php` en `ov.php` toegevoegd als toekomstvaste brug naar externe veilige API-configuratie.

## Controle

- `php -l` geslaagd op de gewijzigde PHP-bestanden.
- `node --check` geslaagd op `assets/js/app.js`.
- Lokale PHP-server start correct en de hoofdpagina geeft HTTP 200.
- `traffic.php`, `ov.php` en `weather.php` geven nette JSON-terugval wanneer er nog geen feed of sleutel beschikbaar is.


# Versie 5 — Responsive fix / maten kloppend

**Oplevering:** `CMB STATIC 1711 0205.zip` · Nederlandse tijd 17-11-2026 02:05.

## Klantmelding en oorzaak

De screenshots toonden afgesneden weergegevens, grote blokhoogtes en een onrustig mobiele menuvoet. Oorzaak: vaste minimale kolombreedtes en een vaste weerillustratie van 98 px binnen een te smalle kaart; verborgen overflow maskeerde het probleem. De klok op mobiel en het menu hadden ook ongeschikte typografie voor kleine breedtes.

## Gewijzigde bestanden

- `assets/css/style.css`: alle breekpunten 320–1920, flexibele kolommen, kortere topbalk, weerschaal, volume-overlay zonder clipping, ingeklapt menu en juiste hero/slideshow-hoogte.
- `assets/js/app.js`: korte NL-datum op mobiel, behoud van actieve overlay tijdens responsieve inhoudsweergave en correct herstel bij sluiten.
- `index.php`: automatisch cachebusting van CSS/JS via `filemtime`.
- `LEESMIJ.md` en `PROJECT_INFO.md`: doorlopende registratie en installatienotities.
- Runtime cache en lockbestanden uit de ZIP weggelaten, zonder gebruikersdata te wissen.

## Controle en grenzen

- Responsieve rasterweergave gecontroleerd op negen schermbreedtes.
- Klok met seconden en lange plaatsnaam getest zonder horizontale overflow.
- Mobiel menu en matglas-overlay openen/sluiten; radiostream DOM-element blijft dezelfde en krijgt geen pauze-opdracht tijdens menu-openen.
- Volume-paneel opent zichtbaar buiten de speler zonder afsnijden.
- PHP + JS syntax en lokale HTTP geslaagd. Echte radio, weer, extern verkeer/OV, en langdurige tv-belasting niet als live getest.

De grote CMB SITE2 en andere projecten zijn niet aangepast. De JSON-data, externe config en oorspronkelijke mediabestanden blijven intact.


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
