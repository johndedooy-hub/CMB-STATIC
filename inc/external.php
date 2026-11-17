<?php
declare(strict_types=1);
/** API-sleutels uitsluitend laden uit een bestand BUITEN public_html.
 *  Dezelfde thuisconfiguratie als CMB SITE2, zonder diens installatie te wijzigen.
 */
function cs_external_path(): string {
  static $path = null;
  if ($path !== null) return $path;
  $path = '';
  $root = realpath(dirname(__DIR__));
  if (!$root) return '';
  $candidates = [];
  // Hosting volgens CMB SITE2: /home/gebruiker/domains/voorbeeld.nl/public_html/cmb-static
  $cursor = $root;
  for ($i=0;$i<12;$i++) {
    if (basename($cursor)==='domains') {
      $candidates[] = dirname($cursor).'/cmb-config.php';
      break;
    }
    $up=dirname($cursor); if ($up===$cursor) break; $cursor=$up;
  }
  // Ook hosting zonder domains-map: één map BOVEN public_html.
  $cursor = $root;
  for ($i=0;$i<12;$i++) {
    if (basename($cursor)==='public_html' || basename($cursor)==='httpdocs') {
      $candidates[] = dirname($cursor).'/cmb-config.php';
      break;
    }
    $up=dirname($cursor); if($up===$cursor) break; $cursor=$up;
  }
  $manual = getenv('CMB_STATIC_CONFIG');
  if (is_string($manual) && str_starts_with($manual, '/')) array_unshift($candidates, $manual);
  foreach ($candidates as $candidate) {
    $real = realpath($candidate);
    if (!$real || !is_file($real)) continue;
    // Geen geheimen uit of onder de publiek toegankelijke map halen.
    if ($real===$root || str_starts_with($real,$root.DIRECTORY_SEPARATOR)) continue;
    if (strpos($real,DIRECTORY_SEPARATOR.'public_html'.DIRECTORY_SEPARATOR)!==false || str_ends_with($real,DIRECTORY_SEPARATOR.'public_html')) continue;
    $path=$real;return $path;
  }
  return '';
}
function cs_external_config(): array {
  static $cfg=null;
  if ($cfg!==null) return $cfg;
  $cfg=[];$p=cs_external_path();
  if($p==='')return $cfg;
  try {$v=include $p; if(is_array($v)) $cfg=$v;}catch(Throwable $e){error_log('CMB STATIC: externe configuratie kon niet worden gelezen.');}
  $folder=basename(dirname(__DIR__));$host=strtolower(preg_replace('/:\d+$/','',$_SERVER['HTTP_HOST']??''));
  if(isset($cfg['per_host'][$host])&&is_array($cfg['per_host'][$host]))$cfg=array_replace($cfg,$cfg['per_host'][$host]);
  if(isset($cfg['per_folder'][$folder])&&is_array($cfg['per_folder'][$folder]))$cfg=array_replace($cfg,$cfg['per_folder'][$folder]);
  return $cfg;
}
function cs_external_key(string $name): string {
  $cfg=cs_external_config();
  $v=$cfg[$name]??'';
  return is_string($v)?trim($v):'';
}
/** Probeert alleen bestanden in DEZELFDE niet-publieke thuismap als cmb-config.php. */
function cs_external_legacy(string $file): array|string {
  if(!in_array($file,['github-token-tts.php','github-token.php','ns-api.php'],true))return '';
  $cfg=cs_external_path();if($cfg==='')return '';
  $candidate=dirname($cfg).DIRECTORY_SEPARATOR.$file;
  if(!is_file($candidate))return '';
  try {$value=include $candidate; return is_array($value)||is_string($value)?$value:'';}catch(Throwable $e){return '';}
}
function cs_github_token(): string {
  foreach(['regio_token','github_token','github_tts_token','github_api_token'] as $k){$v=cs_external_key($k);if($v!=='')return $v;}
  $cfg=cs_external_config();
  $f=$cfg['regio_token_file']??'';
  if(is_string($f)&&$f!==''&&str_starts_with($f,'/')&&is_file($f)){
    $real=realpath($f);$base=cs_external_path();
    if($real&&$base&&dirname($real)===dirname($base)){
      try{$v=include $real;if(is_string($v)&&trim($v)!=='')return trim($v);}catch(Throwable $e){}
    }
  }
  foreach(['github-token-tts.php','github-token.php'] as $name){$v=cs_external_legacy($name);if(is_string($v)&&trim($v)!=='')return trim($v);if(is_array($v))foreach(['token','github_token','regio_token'] as $k)if(!empty($v[$k])&&is_string($v[$k]))return trim($v[$k]);}
  return '';
}
function cs_provider_key(string $provider): string {
  $keys=['ns'=>['ns_api_key','ns_key'],'ov'=>['ov_api_key','9292_api_key'],'verkeer'=>['verkeer_api_key','ndw_api_key'],'ai'=>['openrouter_key','gemini_key','anthropic_key']];
  foreach($keys[$provider]??[] as $k){$v=cs_external_key($k);if($v!=='')return $v;}
  if($provider==='ns'){
    $v=cs_external_legacy('ns-api.php');
    if(is_string($v)&&trim($v)!=='')return trim($v);
    if(is_array($v))foreach(['ns_api_key','api_key','key','token'] as $k)if(is_string($v[$k]??null)&&$v[$k]!=='')return trim($v[$k]);
    foreach(['NS_API_KEY','NS_API_TOKEN'] as $c)if(defined($c)&&is_string(constant($c)))return trim(constant($c));
  }
  return '';
}
function cs_api_available(): array {
  $cfg=cs_external_config();$ns=cs_external_legacy('ns-api.php');
  return ['config'=>cs_external_path()!=='','github'=>cs_github_token()!=='','ai'=>cs_external_key('openrouter_key')!=='' || cs_external_key('gemini_key')!=='' || cs_external_key('anthropic_key')!=='', 'ns'=>cs_provider_key('ns')!=='', 'ov'=>cs_provider_key('ov')!=='','verkeer'=>cs_provider_key('verkeer')!==''];
}
