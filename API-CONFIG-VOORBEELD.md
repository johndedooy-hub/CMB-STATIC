# CMB STATIC – API-configuratie buiten public_html

CMB STATIC gebruikt dezelfde veilige basislocatie als CMB SITE2: `cmb-config.php` in de thuismap, **boven** de map `domains`. De website zoekt dit bestand automatisch; er zijn geen API-sleutels opgenomen in de ZIP en het bestand mag niet naar een publieke webmap worden geüpload.

## Herkende sleutels

- `regio_repo` (standaard dezelfde repository als CMB SITE2)
- `regio_workflow` (standaard `regio-editie-tts.yml`)
- `regio_token` / `github_token` / `github_tts_token`, of een naast `cmb-config.php` geplaatst `github-token-tts.php` dat het token teruggeeft
- `ns_api_key`, of het reservebestand `ns-api.php` naast `cmb-config.php`
- `ov_api_key` / `9292_api_key`
- `verkeer_api_key` / `ndw_api_key`
- `openrouter_key` / `gemini_key` / `anthropic_key`
- Optioneel `static_regio_repo` om de STATIC-regioagenda een aparte repository te geven
- Optioneel `static_regio_txt_url` alleen als het tekstbestand bewust elders gepubliceerd wordt; anders levert STATIC de eigen regio-tekstlinks

De instellingenpagina toont uitsluitend **aanwezig of ontbreekt**, nooit de inhoud van een sleutel. Het toevoegen of wijzigen van geheimen gaat via het bestaande **externe** serverbestand, niet via dit ZIP-archief.

Deze versie bevat de infrastructuur voor deze sleutels. Het volledige OV-, verkeers- en AI-platform van CMB SITE2 is **niet** naar de rustige STATIC-pagina gekopieerd: alleen de centrale, veilige API-configuratielaag is voorbereid voor latere koppelingen.

## Regioagenda / GitHub

De editor is `region-admin.php` onder dezelfde login als `admin.php`. De workflow ontvangt voor elke editie een openbare tekstlink naar `region-text.php?edition=1` of `edition=2` van deze STATIC-installatie. Voor GitHub Actions moet STATIC dus via HTTPS van buiten bereikbaar zijn. De MP3-generatie werkt alleen wanneer de gebruikte repository, workflow en externe toegangssleutel correct zijn ingericht. CMB STATIC toont geen sleutels aan bezoekers.
