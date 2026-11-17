<?php
declare(strict_types=1);
require_once __DIR__.'/inc/site.php';
require_once __DIR__.'/inc/region.php';
require_once __DIR__.'/inc/streams.php';
cs_security_headers();

$c = cs_config();
$t = $c['theme'];
$r = $c['radio'];
$w = $c['topbar'];
$lay = $c['layout'];
$pl = $c['pl'];
$labels = $c['labels'];
$streams = cs_streams($r);
$region = cs_region_load();

$slides = array_values(array_filter(array_slice($c['slides'] ?? [], 0, 12), static fn($s) => is_array($s) && (!empty($s['title']) || !empty($s['image']))));
foreach (cs_region_slides($region) as $event) {
    if (count($slides) < 12) $slides[] = $event;
}
$tiles = array_values(array_filter($c['tiles'] ?? [], static fn($tile) => is_array($tile) && !empty($tile['title'])));
$newsTiles = array_slice($tiles, 0, 4);
$actionTiles = array_slice($tiles, 0, 4);
$regionItems = array_values(array_filter($region['items'] ?? [], static fn($item) => is_array($item) && (!empty($item['kop']) || !empty($item['text']) || !empty($item['media']))));
$regionItems = array_slice($regionItems, 0, 6);

$font = cs_font($t['font_family'] ?? '');
$bgMode = in_array($t['bg_mode'] ?? 'image', ['image', 'video', 'solid'], true) ? $t['bg_mode'] : 'image';
$srcBg = cs_image($t['bg_image'] ?? '') ?: 'assets/img/studio-ambient.webp';
$videoBg = cs_image($t['bg_video'] ?? '');
$cover = cs_image($r['cover'] ?? '') ?: cs_image($r['logo'] ?? '');

$settings = [
    'streams' => $streams,
    'stream' => $streams[0]['streamUrl'] ?? '',
    'backup' => $streams[1]['streamUrl'] ?? '',
    'failover' => !empty($r['failover']),
    'volume' => cs_int($r['volume'] ?? 75, 0, 100, 75),
    'recheck' => cs_int($r['stream_recheck_sec'] ?? 60, 20, 600, 60),
    'interval' => cs_int($lay['slide_interval'] ?? 8, 3, 60, 8),
    'seconds' => !empty($w['time_seconds']),
    'motion' => $t['motion'] ?? 'subtle',
    'weatherAnimation' => !empty($w['weather_animation']),
    'tickerSpeed' => cs_int($c['ticker']['speed'] ?? 60, 15, 150, 60),
    'trafficUrl' => 'traffic.php',
    'ovUrl' => 'ov.php',
];

function cs_menu_panel(string $label, string $url): string {
    $probe = strtolower(trim($label.' '.$url));
    return match (true) {
        str_contains($probe, 'nieuw') => 'news',
        str_contains($probe, 'weer') => 'weather',
        str_contains($probe, 'regio') => 'region',
        str_contains($probe, 'verkeer') || str_contains($probe, 'auto') || str_contains($probe, 'trein') || str_contains($probe, 'ov') => 'traffic',
        str_contains($probe, 'extra'), str_contains($probe, 'contact'), str_contains($probe, 'privacy') => 'extra',
        default => 'home',
    };
}
?>
<!doctype html>
<html lang="nl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="<?= cs_e($t['bg_color']) ?>">
<meta name="description" content="<?= cs_e($r['station_name']) ?> — luister live, slides, regioagenda en studio-info.">
<title><?= cs_e($r['station_name']) ?> | CMB STATIC</title>
<link rel="stylesheet" href="assets/css/style.css?v=<?= (int)filemtime(__DIR__.'/assets/css/style.css') ?>">
<style>:root{
--bg:<?= cs_color($t['bg_color'], '#080d12') ?>;--surface:<?= cs_color($t['card_color'], '#16202b') ?>;--accent:<?= cs_color($t['accent_color'], '#d8b46e') ?>;--accent-alt:<?= cs_color($t['accent_alt'], '#cfd5da') ?>;--txt:<?= cs_color($t['text_color'], '#f5f5f1') ?>;--muted:<?= cs_color($t['muted_color'], '#a8b0b7') ?>;--nav:<?= cs_color($t['nav_color'], '#0d1822') ?>;
--radius:<?= cs_int($t['border_radius'] ?? 5, 4, 40, 5) ?>px;--maxw:<?= cs_int($t['max_width'] ?? 1440, 800, 1900, 1440) ?>px;--glass:<?= number_format(cs_int($t['glass'] ?? 78, 35, 100, 78) / 100, 2, '.', '') ?>;
--font:<?= cs_e($font) ?>;--base:<?= cs_int($t['base_font_size'] ?? 16, 12, 22, 16) ?>px;--tile-cols:<?= cs_int($lay['tile_columns'] ?? 4, 2, 5, 4) ?>;--slide-height:<?= cs_int($lay['slide_height'] ?? 336, 220, 680, 336) ?>px;--slide-shade:<?= cs_int($lay['slide_overlay'] ?? 54, 0, 90, 54) / 100 ?>;
}</style>
<script>window.CMB_STATIC=<?= json_encode($settings, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_SLASHES) ?>;</script>
</head>
<body class="bg-<?= cs_e($bgMode) ?> motion-<?= cs_e($t['motion'] ?? 'subtle') ?>">
<div class="studio-backdrop" aria-hidden="true">
  <img class="studio-image" src="<?= cs_e($srcBg) ?>" alt="" aria-hidden="true" style="opacity:<?= number_format(cs_int($t['bg_opacity'] ?? 38, 0, 90, 38) / 100, 2, '.', '') ?>">
  <?php if ($bgMode === 'video' && $videoBg !== ''): ?>
    <video id="studio-video" class="studio-video" muted autoplay loop playsinline preload="none" poster="<?= cs_e($srcBg) ?>"><source src="<?= cs_e($videoBg) ?>" type="video/mp4"></video>
  <?php endif; ?>
  <div class="studio-vignette"></div>
</div>
<div class="site-wrap" id="home">
  <header class="masthead">
    <a class="brand glass-card" href="#home" aria-label="Naar boven">
      <span class="brand-graphic"><?php if (!empty($r['logo'])): ?><img src="<?= cs_e(cs_image($r['logo'])) ?>" alt="Logo van <?= cs_e($r['station_name']) ?>"><?php else: ?><span class="brand-frequency">◉</span><?php endif; ?></span>
      <span class="brand-copy"><small><?= cs_e($r['eyebrow']) ?></small><strong><?= cs_e($r['station_name'] ?: 'CMB STATIC') ?></strong></span>
    </a>

    <section class="player-card glass-card" aria-label="Radio luisteren">
      <div class="mini-cover"><?php if ($cover !== ''): ?><img id="trackCover" src="<?= cs_e($cover) ?>" alt="Albumhoes"><?php else: ?><span id="coverFallback" aria-hidden="true">♪</span><?php endif; ?></div>
      <div class="track-lines"><span class="live-line"><i class="live-pip"></i><?= cs_e($r['stream_label'] ?? 'LIVE RADIO') ?></span><strong id="trackTitle"><?= cs_e($r['default_title'] ?: $r['current_show']) ?></strong><span id="trackArtist"><?= cs_e($r['default_artist'] ?? 'ON AIR') ?></span></div>
      <div class="player-controls"><button type="button" id="playPauseBtn" class="control-button play-button" aria-label="Radio starten" title="Start of pauzeer">▶</button><button type="button" id="volumeBtn" class="control-button volume-button" aria-label="Volume aanpassen" aria-expanded="false" title="Volume">◖))</button><div id="volumePanel" class="volume-panel" hidden><label for="volumeSlider">Volume</label><input id="volumeSlider" type="range" min="0" max="100" step="1" value="<?= cs_int($r['volume'] ?? 75, 0, 100, 75) ?>"><span id="volumeValue"><?= cs_int($r['volume'] ?? 75, 0, 100, 75) ?>%</span></div></div>
      <audio id="radioStream" preload="none"></audio>
    </section>

    <section class="meteo-clock glass-card" id="weer" aria-label="Tijd, datum en actueel weer">
      <div class="time-side"><span class="clock-kicker"><?= cs_e($labels['time']) ?></span><strong id="liveClock">--:--</strong><span id="liveDate">—</span></div>
      <div class="weather-side">
        <div class="weather-visual weather-cloudy" id="weatherVisual" aria-hidden="true"><span class="weather-sun"></span><span class="cloud cloud-one"></span><span class="cloud cloud-two"></span><span class="rain-layer"></span><span class="lightning-layer"></span></div>
        <div class="weather-info"><span class="clock-kicker"><?= cs_e($labels['weather']) ?> · <span id="weatherStatus">WEER</span></span><span class="temp" id="weatherTemp">--°</span><span class="weather-location"><span id="weatherCity"><?= cs_e($w['weather_city']) ?></span> <span id="weatherDescription"></span></span></div>
      </div>
    </section>
  </header>

  <main>
    <div class="section-heading"><span class="eyebrow"><?= cs_e($labels['hero']) ?></span><span class="heading-rule"></span><span class="top-mini"><?= cs_e($labels['hero_info']) ?> • <?= cs_e($r['station_name']) ?></span></div>

    <section class="hero-layout" aria-label="Navigatie en presentaties">
      <nav class="side-menu glass-card" aria-label="Hoofdmenu">
        <div class="menu-top"><span><?= cs_e($lay['menu_title'] ?? 'NAVIGATIE') ?></span><button class="menu-toggle" id="menuToggle" aria-controls="navList" aria-expanded="false">Menu ☰</button></div>
        <ul class="nav-list" id="navList">
          <?php foreach (($c['menu'] ?? []) as $n => $m): if (empty($m['label'])) continue; $u = (string)($m['url'] ?? ''); $panel = cs_menu_panel((string)$m['label'], $u); ?>
            <li><a href="<?= cs_e(cs_url($u) ?: '#home') ?>" data-panel="<?= cs_e($panel) ?>" class="<?= !empty($m['active']) ? 'active' : '' ?>"><span class="nav-num"><?= str_pad((string)($n + 1), 2, '0', STR_PAD_LEFT) ?></span><span><?= cs_e($m['label']) ?></span><span class="nav-arrow" aria-hidden="true">↗</span></a></li>
          <?php endforeach; ?>
        </ul>
        <div class="menu-foot"><span class="onair-ring"></span><div><?= cs_e($labels['menu_live']) ?><span><?= cs_e($labels['menu_live_info']) ?></span></div></div>
      </nav>

      <div class="hero-stage">
        <div class="slideshow-shell glass-card" id="slideshowShell" aria-label="Slideshow">
          <?php if ($slides): foreach ($slides as $i => $s): $img = cs_image($s['image'] ?? ''); $mode = in_array($s['caption_mode'] ?? 'overlay', ['overlay','outside','both','hidden'], true) ? $s['caption_mode'] : 'overlay'; ?>
            <article class="slide <?= $i === 0 ? 'active' : '' ?> caption-<?= cs_e($mode) ?>" data-title="<?= cs_e($s['title'] ?? '') ?>" data-subtitle="<?= cs_e($s['subtitle'] ?? '') ?>" data-caption="<?= cs_e($mode) ?>" data-color="<?= cs_color($s['title_color'] ?? '#ffffff') ?>" data-subcolor="<?= cs_color($s['subtitle_color'] ?? '#ecf3f5') ?>" data-fontsize="<?= cs_int($s['title_size'] ?? 33, 18, 56, 33) ?>" aria-hidden="<?= $i === 0 ? 'false' : 'true' ?>">
              <?php if ($img !== ''): ?><img class="slide-img" src="<?= cs_e($img) ?>" alt="" <?= $i > 0 ? 'loading="lazy"' : '' ?>><?php else: ?><div class="slide-img fallback-slide"></div><?php endif; ?>
              <div class="slide-gradient"></div>
              <?php if ($mode === 'overlay' || $mode === 'both'): ?><div class="slide-copy" style="--slide-color:<?= cs_color($s['title_color'] ?? '#ffffff') ?>;--sub-color:<?= cs_color($s['subtitle_color'] ?? '#ecf3f5') ?>;--copy-bg:<?= cs_color($s['caption_bg'] ?? '#102636') ?>;--copy-size:<?= cs_int($s['title_size'] ?? 33, 18, 56, 33) ?>px"><small><?= cs_e($labels['slide_brand']) ?> · <?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></small><h2><?= cs_e($s['title'] ?? '') ?></h2><p><?= cs_e($s['subtitle'] ?? '') ?></p></div><?php endif; ?>
            </article>
          <?php endforeach; else: ?>
            <article class="slide active" aria-hidden="false"><div class="slide-img fallback-slide"></div><div class="slide-gradient"></div><div class="slide-copy"><small><?= cs_e($labels['slide_brand']) ?></small><h2><?= cs_e($r['station_name']) ?></h2><p><?= cs_e($r['current_show']) ?></p></div></article>
          <?php endif; ?>
          <div class="slide-ui"><span id="slideCounter">01 / <?= str_pad((string)max(1, count($slides)), 2, '0', STR_PAD_LEFT) ?></span><div id="slideDots" class="slide-dots" aria-label="Slide kiezen"></div><div class="slide-buttons"><button type="button" id="slidePrev" aria-label="Vorige slide">‹</button><button type="button" id="slideNext" aria-label="Volgende slide">›</button></div></div>

          <div class="overlay-shell" id="overlayShell" hidden>
            <div class="overlay-top"><strong id="overlayTitle">Studio overzicht</strong><button type="button" id="overlayClose" class="overlay-close" aria-label="Paneel sluiten">×</button></div>
            <div class="overlay-panels">
              <section class="overlay-panel active" data-panel="home">
                <div class="overlay-grid overlay-grid-2">
                  <article class="mini-card"><span class="mini-label">Start</span><h3><?= cs_e($r['station_name']) ?></h3><p><?= cs_e($c['welcome']['text'] ?? '') ?></p></article>
                  <article class="mini-card"><span class="mini-label">Studio</span><h3><?= cs_e($labels['hero_info']) ?></h3><p>Open via het menu het nieuws, weer, regio, verkeer en extra informatie zonder dat de muziek stopt.</p></article>
                </div>
              </section>

              <section class="overlay-panel" tabindex="0" data-panel="news">
                <div class="overlay-grid overlay-grid-4">
                  <?php foreach ($newsTiles as $tile): $img = cs_image($tile['image'] ?? ''); ?>
                    <article class="mini-card tile-mini" style="--tile-bg:<?= cs_color($tile['bg_color'] ?? $t['card_color']) ?>"><?php if ($img !== ''): ?><img src="<?= cs_e($img) ?>" alt="" loading="lazy"><?php endif; ?><span class="mini-label"><?= cs_e($tile['tag'] ?? 'NIEUWS') ?></span><h3><?= cs_e($tile['title']) ?></h3><p><?= cs_e($tile['subtitle'] ?? '') ?></p></article>
                  <?php endforeach; ?>
                </div>
              </section>

              <section class="overlay-panel" tabindex="0" data-panel="weather">
                <div class="overlay-grid overlay-grid-4 weather-cards">
                  <article class="mini-card weather-card-main"><span class="mini-label">Actueel</span><h3><span id="panelWeatherTemp">--°</span></h3><p><strong id="panelWeatherTitle"><?= cs_e($w['weather_city']) ?></strong><br><span id="panelWeatherText">Wachten op actuele gegevens…</span></p></article>
                  <article class="mini-card"><span class="mini-label">Weerbeeld</span><h3>Levend</h3><p>De wolken, regen en bliksem bewegen subtiel in de tijd- en weerkaart.</p></article>
                  <article class="mini-card"><span class="mini-label">Plaats</span><h3><?= cs_e($w['weather_city']) ?></h3><p>Deze plaats kun je in beheer vrij aanpassen. De temperatuur volgt dan die gekozen locatie.</p></article>
                  <article class="mini-card"><span class="mini-label">Studio-tip</span><h3>Rustig ontwerp</h3><p>De huidige opzet is bewust licht gehouden voor tv, mobiel en desktop.</p></article>
                </div>
              </section>

              <section class="overlay-panel" tabindex="0" data-panel="region">
                <div class="overlay-grid overlay-grid-3 region-cards">
                  <?php foreach ($regionItems as $ix => $item): $media = cs_image($item['media'] ?? ''); ?>
                    <article class="mini-card region-card"><?php if ($media !== ''): ?><img src="<?= cs_e($media) ?>" alt="" loading="lazy"><?php endif; ?><span class="mini-label">Regio <?= $ix + 1 ?></span><h3><?= cs_e($item['kop'] ?: 'Regio-item') ?></h3><p><?= cs_e(cs_limit($item['text'] ?? '', 220)) ?></p></article>
                  <?php endforeach; ?>
                </div>
              </section>

              <section class="overlay-panel" tabindex="0" data-panel="traffic">
                <div class="traffic-stack">
                  <details class="traffic-group" open><summary>Auto & verkeer</summary><div class="traffic-list" id="trafficRoad"><p class="traffic-empty">Verkeersinformatie laden…</p></div></details>
                  <details class="traffic-group"><summary>Trein</summary><div class="traffic-list" id="trafficTrain"><p class="traffic-empty">Treinmeldingen laden…</p></div></details>
                  <details class="traffic-group"><summary>OV</summary><div class="traffic-list" id="trafficOv"><p class="traffic-empty">OV-meldingen laden…</p></div></details>
                </div>
              </section>

              <section class="overlay-panel" tabindex="0" data-panel="extra">
                <div class="overlay-grid overlay-grid-2 extra-grid">
                  <article class="mini-card"><span class="mini-label">Studio contact</span><h3><?= cs_e($c['contact']['heading'] ?? 'Contact met de studio') ?></h3><p><?= nl2br(cs_e($c['contact']['text'] ?? 'Een verzoekje, tip of bericht? Laat van je horen.')) ?></p><p><a href="mailto:<?= cs_e($pl['email']) ?>"><?= cs_e($pl['email']) ?></a></p></article>
                  <article class="mini-card"><span class="mini-label">Privacy & links</span><h3><?= cs_e($labels['privacy_heading']) ?></h3><p><?= cs_e($pl['privacy'] ?? '') ?></p></article>
                </div>
                <div class="overlay-grid overlay-grid-4 extra-links">
                  <?php for ($i = 0; $i < 10; $i++): $item = $pl['blocks'][$i] ?? []; $img = cs_image($item['img'] ?? ''); $txt = cs_limit($item['text'] ?? '', 30); $url = cs_url($item['url'] ?? ''); if (!$img && !$txt) continue; ?>
                    <article class="mini-card pl-card"><?php if ($url): ?><a href="<?= cs_e($url) ?>" <?= str_starts_with($url, 'http') ? 'target="_blank" rel="noopener noreferrer"' : '' ?>><?php endif; ?><?php if ($img): ?><img src="<?= cs_e($img) ?>" alt="<?= cs_e($txt) ?>" loading="lazy"><?php endif; ?><h3><?= cs_e($txt ?: 'Studio link') ?></h3><?php if ($url): ?></a><?php endif; ?></article>
                  <?php endfor; ?>
                </div>
              </section>
            </div>
          </div>
        </div>
        <div class="slide-outside" id="slideOutside" hidden><span class="out-icon">✦</span><div><strong id="slideOutsideTitle"></strong><span id="slideOutsideSubtitle"></span></div></div>
      </div>
    </section>

    <section class="highlights" id="nieuws">
      <div class="section-heading"><span class="eyebrow"><?= cs_e($labels['highlight']) ?></span><span class="heading-rule"></span><span class="top-mini"><?= cs_e($labels['highlight_info']) ?></span></div>
      <div class="tiles-grid">
        <?php foreach ($actionTiles as $tile): $link = cs_url($tile['url'] ?? ''); $tileImg = cs_image($tile['image'] ?? ''); ?>
          <article class="tile glass-card" style="--tile-bg:<?= cs_color($tile['bg_color'] ?? $t['card_color']) ?>;--tile-fg:<?= cs_color($tile['title_color'] ?? '#ffffff') ?>;--tile-tag-bg:<?= cs_color($tile['tag_bg'] ?? '#187d65') ?>;--tile-tag-fg:<?= cs_color($tile['tag_color'] ?? '#eafff7') ?>;--tile-sub:<?= cs_color($tile['subtitle_color'] ?? '#c2d2d7') ?>"><?php if ($tileImg !== ''): ?><img src="<?= cs_e($tileImg) ?>" alt="" loading="lazy" class="tile-img"><?php endif; ?><div class="tile-tint"></div><div class="tile-inner"><span class="tile-badge"><?= cs_e($tile['tag'] ?? 'UITGELICHT') ?></span><h3><?= cs_e($tile['title']) ?></h3><p><?= cs_e($tile['subtitle'] ?? '') ?></p><?php if ($link !== ''): ?><a href="<?= cs_e($link) ?>" class="tile-link" <?= str_starts_with($link, 'http') ? 'target="_blank" rel="noopener noreferrer"' : 'data-lightbox-link="1"' ?> aria-label="Meer over <?= cs_e($tile['title']) ?>"><span>BEKIJK</span> ↗</a><?php endif; ?></div></article>
        <?php endforeach; ?>
      </div>
    </section>

    <?php if (!empty($lay['welcome_visible'])): ?>
      <section class="welcome-panel studio-strip glass-card" id="over"><div><span class="eyebrow"><?= cs_e($c['welcome']['eyebrow'] ?: $labels['studio_eyebrow']) ?></span><h2><?= cs_e($c['welcome']['heading']) ?></h2><p><?= nl2br(cs_e($c['welcome']['text'])) ?></p></div><?php $wLink = cs_url($c['welcome']['button_url'] ?? ''); if ($wLink === '#contact') $wLink = 'mailto:'.$pl['email']; if ($wLink): ?><a class="outline-action" href="<?= cs_e($wLink) ?>" <?= str_starts_with($wLink, 'http') ? 'target="_blank" rel="noopener noreferrer"' : '' ?>><?= cs_e($c['welcome']['button_text'] ?? 'Mail de studio') ?> ↗</a><?php endif; ?></section>
    <?php endif; ?>
  </main>
</div>

<div class="bottom-dock" id="bottomDock">
  <?php if (!empty($c['ticker']['enabled']) && !empty($c['ticker']['items'])): ?>
    <div class="ticker-bar" id="tickerBar" style="--ticker-bg:<?= cs_color($c['ticker']['bg_color'] ?? '#07121d') ?>;--ticker-fg:<?= cs_color($c['ticker']['color'] ?? '#ffffff') ?>;--ticker-size:<?= cs_int($c['ticker']['font_size'] ?? 13, 10, 22, 13) ?>px"><span class="ticker-label"><?= cs_e($labels['ticker']) ?></span><div class="ticker-window" id="tickerWindow"><div class="ticker-items" id="tickerItems"><?php foreach (($c['ticker']['items'] ?? []) as $item): if (empty($item['text'])) continue; ?><span class="ticker-entry"><?= cs_e($item['text']) ?></span><span class="ticker-sep">✦</span><?php endforeach; ?></div></div></div>
  <?php endif; ?>
  <div class="compact-studio-bar glass-card"><span class="eyebrow">STUDIO</span><strong><?= cs_e($r['station_name']) ?></strong><span><?= cs_e($r['current_show']) ?></span></div>
  <div class="pl-dock hidden-pl" id="plDock"><div class="pl-head"><span class="pl-mark">◉</span><strong><?= cs_e($pl['title'] ?? 'Meer van de studio') ?></strong><div class="pl-quick"><button type="button" id="privacyBtn" title="Privacy">🔒</button><a href="mailto:<?= cs_e($pl['email']) ?>" title="Mail de studio">✉</a></div><button class="pl-expand" type="button" id="plToggle" aria-expanded="false" aria-controls="plPanel" title="Balk uitklappen">⌃</button></div>
    <div class="pl-panel" id="plPanel" hidden><div class="pl-items"><?php for ($i = 0; $i < 10; $i++): $item = $pl['blocks'][$i] ?? []; $plImg = cs_image($item['img'] ?? ''); $plTxt = cs_limit($item['text'] ?? '', 24); $plLink = cs_url($item['url'] ?? ''); if (!$plImg && !$plTxt) continue; ?><div class="pl-item"><?php if ($plLink): ?><a href="<?= cs_e($plLink) ?>" <?= str_starts_with($plLink, 'http') ? 'target="_blank" rel="noopener noreferrer"' : '' ?>><?php endif; ?><?php if ($plImg): ?><img src="<?= cs_e($plImg) ?>" alt="<?= cs_e($plTxt) ?>"><?php endif; ?><?php if ($plTxt): ?><span><?= cs_e($plTxt) ?></span><?php endif; ?><?php if ($plLink): ?></a><?php endif; ?></div><?php endfor; ?></div><span class="pl-footer"><?= cs_e($labels['pl_footer']) ?></span></div></div>
</div>

<dialog id="privacyDialog" class="privacy-dialog"><h2><?= cs_e($labels['privacy_heading']) ?></h2><p><?= nl2br(cs_e($pl['privacy'])) ?></p><p>Contact: <a href="mailto:<?= cs_e($pl['email']) ?>"><?= cs_e($pl['email']) ?></a></p><button id="closePrivacy" type="button">Sluiten</button></dialog>
<script src="assets/js/app.js?v=<?= (int)filemtime(__DIR__.'/assets/js/app.js') ?>" defer></script>
</body>
</html>
