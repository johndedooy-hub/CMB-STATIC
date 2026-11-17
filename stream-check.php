<?php
declare(strict_types=1);
require_once __DIR__.'/inc/streams.php';cs_security_headers();
header('Content-Type: application/json; charset=utf-8');header('Cache-Control: public, max-age=20');
$c=cs_config();$st=cs_streams($c['radio']);$url=$st[0]['streamUrl']?:$st[0]['streamUrlAlt'];
if($url===''||!preg_match('~^https?://~i',$url)||!function_exists('curl_init')){echo json_encode(['ok'=>false]);exit;}
$d=cs_cache('streamcheck:'.hash('sha256',$url),25,function()use($url){
  $ch=curl_init($url);curl_setopt_array($ch,[CURLOPT_NOBODY=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>2,CURLOPT_TIMEOUT=>4,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_FOLLOWLOCATION=>false]);
  $ok=curl_exec($ch);$status=(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE);curl_close($ch);
  $reachable=$ok!==false&&$status>=200&&$status<400;
  if(!$reachable && in_array($status,[400,403,405,501],true)){
    // Sommige Icecast/Shoutcast-servers weigeren HEAD maar laten wel een stream-GET toe.
    $ch=curl_init($url);
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>false,CURLOPT_CONNECTTIMEOUT=>2,CURLOPT_TIMEOUT=>4,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_RANGE=>'0-1',CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_WRITEFUNCTION=>static function($ch,$bytes){return strlen($bytes);}]);
    curl_exec($ch);$status=(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE);curl_close($ch);
    $reachable=$status>=200&&$status<400;
  }
  return ['ok'=>$reachable,'checked'=>time()];
},['ok'=>false]);
echo json_encode(['ok'=>!empty($d['ok'])]);
