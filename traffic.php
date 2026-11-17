<?php
declare(strict_types=1);
require_once __DIR__.'/inc/site.php';
cs_security_headers();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private, max-age=0');

$out = cs_cache('traffic-anwb-rss', 300, static function(): array {
    $feeds = [
        'https://www.anwb.nl/rss/feeds/traffic',
        'https://www.anwb.nl/rss/feeds/traffic-jams',
        'https://www.anwb.nl/rss/verkeersinformatie',
    ];
    $xml = '';
    foreach ($feeds as $url) {
        $xml = cs_remote($url, 5) ?? '';
        if ($xml !== '') break;
    }
    if ($xml === '' || !class_exists('SimpleXMLElement')) {
        return ['ok' => false, 'items' => [], 'message' => 'Geen live verkeersfeed gevonden.'];
    }
    libxml_use_internal_errors(true);
    $rss = simplexml_load_string($xml);
    if (!$rss || !isset($rss->channel->item)) {
        return ['ok' => false, 'items' => [], 'message' => 'Geen live verkeersfeed gevonden.'];
    }
    $items = [];
    foreach ($rss->channel->item as $item) {
        $title = trim((string)($item->title ?? ''));
        $desc = trim(strip_tags((string)($item->description ?? '')));
        if ($title === '' && $desc === '') continue;
        $meta = trim((string)($item->pubDate ?? ''));
        $items[] = ['title' => $title, 'text' => $desc, 'meta' => $meta];
        if (count($items) >= 12) break;
    }
    return ['ok' => (bool)$items, 'items' => $items, 'message' => $items ? '' : 'Geen verkeersmeldingen beschikbaar.'];
}, ['ok' => false, 'items' => [], 'message' => 'Geen verkeersmeldingen beschikbaar.']);

echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
