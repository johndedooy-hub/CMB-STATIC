<?php
declare(strict_types=1);
require_once __DIR__.'/inc/streams.php';
require_once __DIR__.'/inc/external.php';cs_security_headers();
header('Content-Type: application/json; charset=utf-8');header('Cache-Control: public, max-age=8');
$c=cs_config();$radio=$c['radio'];$streams=cs_streams($radio);
$number=((int)($_GET['stream']??1)===2)?2:1;$s=$streams[$number-1];
$fallback=['artist'=>cs_limit($radio['default_artist']??'ON AIR',120),'title'=>cs_limit($radio['current_show']??'Live radio',180),'cover'=>cs_image($radio['cover']??''),'source'=>'fallback','stream'=>$number];
$stats=array_filter([$s['statsJsonUrl']??'',$s['apiBackupUrl']??''],static fn($v)=>is_string($v)&&$v!=='');
// jsSrc is een mogelijke stats-URL, nooit een echt JS-script ophalen en uitvoeren.
$js=trim((string)($s['jsSrc']??''));
if(preg_match('~^https?://.*(?:/stats|currentsong|7\.html|status-json|get_info\.php)~i',$js))$stats[]=$js;
$stats=array_values(array_unique($stats));
if(!$stats){echo json_encode($fallback,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;}
function cs_decode_track(string $raw): array {
  $raw=trim(preg_replace('/^\xEF\xBB\xBF/','',$raw));
  $artist='';$title='';$cover='';
  $d=json_decode($raw,true);
  if(is_array($d)){
    if(isset($d[0])&&is_array($d[0]))$d=$d[0];
    $np=$d['now_playing']['song']??$d['song']??$d['currenttrack']??null;
    if(is_array($np)){
      $artist=(string)($np['artist']??'');$title=(string)($np['title']??$np['name']??'');$cover=(string)($np['art']??$np['cover']??'');
      if($title===''&&is_string($np['text']??null))$title=$np['text'];
    }
    if($title==='' && isset($d['icestats']['source'])){
      $src=$d['icestats']['source'];if(isset($src[0]))$src=$src[0];
      if(is_array($src)){$artist=(string)($src['artist']??'');$title=(string)($src['title']??'');}
    }
    if($title===''){
      $v=$d['songtitle']??$d['title']??$d['current_song']??$d['currentsong']??$d['song']??'';
      if(is_string($v))$title=$v;
    }
    if($cover==='')$cover=(string)($d['art']??$d['cover']??$d['artwork_url']??'');
  }elseif(preg_match('~<SONGTITLE[^>]*>(.*?)</SONGTITLE>~is',$raw,$m))$title=html_entity_decode(strip_tags($m[1]),ENT_QUOTES|ENT_HTML5,'UTF-8');
  elseif(preg_match('~<body[^>]*>(.*?)</body>~is',$raw,$m)){$parts=explode(',',strip_tags($m[1]),7);if(count($parts)===7)$title=$parts[6];}
  elseif(strlen($raw)<300&&strpos($raw,'<')===false&&strpos($raw,'{')===false)$title=$raw;
  $title=trim(html_entity_decode(strip_tags($title),ENT_QUOTES|ENT_HTML5,'UTF-8'));
  $artist=trim(html_entity_decode(strip_tags($artist),ENT_QUOTES|ENT_HTML5,'UTF-8'));
  if($artist===''&&str_contains($title,' - ')){[$artist,$title]=explode(' - ',$title,2);}
  return ['artist'=>cs_limit($artist,120),'title'=>cs_limit($title,180),'cover'=>cs_image($cover)];
}
function cs_cover_lookup(string $artist,string $title,string $provider): string {
  if($artist===''||$title===''||$provider==='none')return '';
  $term=trim($artist.' '.$title);
  $key='cover:'.strtolower($provider.':'.$term);
  $d=cs_cache($key,86400,function()use($provider,$artist,$title,$term){
    if($provider==='deezer'){
      $raw=cs_remote('https://api.deezer.com/search?q='.rawurlencode($term),3);$j=$raw?json_decode($raw,true):[];
      if(!empty($j['data'][0]['album']['cover_medium']))return ['image'=>(string)$j['data'][0]['album']['cover_medium']];
    }elseif($provider==='lastfm' && cs_external_key('lastfm_key')!==''){
      $url='https://ws.audioscrobbler.com/2.0/?method=track.getInfo&api_key='.rawurlencode(cs_external_key('lastfm_key')).'&artist='.rawurlencode($artist).'&track='.rawurlencode($title).'&format=json';
      $raw=cs_remote($url,3);$j=$raw?json_decode($raw,true):[];
      foreach(array_reverse($j['track']['album']['image']??[]) as $pic)if(!empty($pic['#text']))return ['image'=>$pic['#text']];
    }else{
      $raw=cs_remote('https://itunes.apple.com/search?media=music&entity=song&limit=1&term='.rawurlencode($term),3);
      $j=$raw?json_decode($raw,true):[];
      if(!empty($j['results'][0]['artworkUrl100']))return ['image'=>str_replace('100x100','300x300',(string)$j['results'][0]['artworkUrl100'])];
    }
    return [];
  },[]);
  return cs_image($d['image']??'');
}
$result=cs_cache('track:'.$number.':'.hash('sha256',json_encode($s)),12,function()use($stats,$s,$fallback,$number){
  foreach($stats as $url){$raw=cs_remote($url,4);if(!$raw)continue;$t=cs_decode_track($raw);if($t['title']==='')continue;
    $cover=$t['cover'];if($cover==='')$cover=cs_cover_lookup($t['artist'],$t['title'],(string)($s['coverApi']??'itunes'));
    return ['artist'=>$t['artist']?:$fallback['artist'],'title'=>$t['title'],'cover'=>$cover?:$fallback['cover'],'source'=>'remote','stream'=>$number];
  }
  return [];
},$fallback);
echo json_encode($result,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_INVALID_UTF8_SUBSTITUTE);
