<?php
declare(strict_types=1);
require_once __DIR__.'/site.php';
function cs_stream_defaults(): array {
  return ['name'=>'','streamUrl'=>'','streamUrlAlt'=>'','statsJsonUrl'=>'','apiBackupUrl'=>'','jsSrc'=>'','jsPort'=>'','coverApi'=>'itunes'];
}
function cs_streams(array $radio): array {
  $base=cs_stream_defaults();
  $first=$radio['streams'][0]??[];$second=$radio['streams'][1]??[];
  if(!is_array($first))$first=[];if(!is_array($second))$second=[];
  // Bestaande configuratie blijft bruikbaar, ook bij een oude upgrade.
  $first=array_merge($base,['name'=>$radio['station_name']??'','streamUrl'=>$radio['stream_url']??'','statsJsonUrl'=>$radio['metadata_url']??'','apiBackupUrl'=>$radio['backup_metadata_url']??''],$first);
  $second=array_merge($base,['name'=>'Reserve','streamUrl'=>$radio['backup_stream_url']??''],$second);
  return [$first,$second];
}
function cs_preset_path(): string{return cs_root().'/stream-presets.json';}
function cs_presets(): array {
  $read=static function(string $p): array {$j=is_file($p)?json_decode((string)@file_get_contents($p),true):[];return is_array($j)?$j:[];};
  $j=array_replace($read(cs_preset_path()),$read(cs_root().'/data/stream-presets.user.json'));
  $out=[];foreach($j as $name=>$data){if(!is_string($name)||!is_array($data))continue;$out[$name]=array_merge(cs_stream_defaults(),$data);}
  return $out;
}
function cs_preset_save(string $name,array $stream): bool {
  $p=cs_root().'/data/stream-presets.user.json';
  $users=is_file($p)?json_decode((string)@file_get_contents($p),true):[];
  if(!is_array($users))$users=[];
  $users[$name]=array_intersect_key($stream,cs_stream_defaults());
  ksort($users,SORT_NATURAL|SORT_FLAG_CASE);
  return cs_write($p,json_encode($users,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)."\n");
}
function cs_stream_clean(array $raw): array {
  $d=cs_stream_defaults();
  foreach(['name','jsPort'] as $k)$d[$k]=cs_limit($raw[$k]??'',120);
  foreach(['streamUrl','streamUrlAlt','statsJsonUrl','apiBackupUrl','jsSrc'] as $k)$d[$k]=cs_url($raw[$k]??'',false);
  $provider=$raw['coverApi']??'itunes';$d['coverApi']=in_array($provider,['itunes','deezer','lastfm','none'],true)?$provider:'itunes';
  return $d;
}
