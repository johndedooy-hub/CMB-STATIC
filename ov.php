<?php
declare(strict_types=1);
require_once __DIR__.'/inc/site.php';
require_once __DIR__.'/inc/external.php';
cs_security_headers();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private, max-age=0');

$out = cs_cache('ov-ns-live', 300, static function(): array {
    $train = [];
    $ov = [];
    $trainMessage = 'Geen treinmeldingen beschikbaar.';
    $ovMessage = 'Geen OV-meldingen beschikbaar. Voeg eventueel later een OV-bron toe in de externe configuratie.';

    $nsKey = cs_provider_key('ns');
    if ($nsKey !== '') {
        $headers = [
            'http' => [
                'timeout' => 6,
                'header' => "User-Agent: CMB-STATIC/2.0\r\nOcp-Apim-Subscription-Key: {$nsKey}\r\n",
                'ignore_errors' => true,
            ],
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
        ];
        $ctx = stream_context_create($headers);
        $body = @file_get_contents('https://gateway.apiportal.ns.nl/reisinformatie-api/api/v3/disruptions', false, $ctx);
        if (is_string($body) && $body !== '') {
            $json = json_decode($body, true);
            if (is_array($json)) {
                foreach ($json as $row) {
                    if (!is_array($row)) continue;
                    $title = trim((string)($row['title'] ?? $row['id'] ?? 'Treinmelding'));
                    $description = trim((string)($row['description'] ?? $row['title'] ?? ''));
                    $times = trim((string)($row['period']['start'] ?? ''));
                    $train[] = ['title' => $title, 'text' => $description, 'meta' => $times];
                    if (count($train) >= 10) break;
                }
                if ($train) $trainMessage = '';
            }
        }
    } else {
        $trainMessage = 'NS API-sleutel niet gevonden in de externe config buiten public_html.';
    }

    $ovKey = cs_provider_key('ov');
    if ($ovKey !== '') {
        $ov[] = ['title' => 'OV-configuratie aanwezig', 'text' => 'De OV-sleutel is gevonden. Deze plek is voorbereid voor jouw OV-koppeling.', 'meta' => 'Klaar voor verdere uitbreiding'];
        $ovMessage = '';
    }

    return [
        'ok' => true,
        'train' => $train,
        'ov' => $ov,
        'train_message' => $trainMessage,
        'ov_message' => $ovMessage,
    ];
}, ['ok' => true, 'train' => [], 'ov' => [], 'train_message' => 'Geen treinmeldingen beschikbaar.', 'ov_message' => 'Geen OV-meldingen beschikbaar.']);

echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
