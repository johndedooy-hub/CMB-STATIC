<?php
declare(strict_types=1);
require_once __DIR__.'/inc/site.php';
cs_security_headers();header('Content-Type: application/json; charset=utf-8');header('Cache-Control: public, max-age=120');
$cfg=cs_config()['topbar'];
$city=trim((string)($cfg['weather_city']??'Amsterdam'));$country=(string)($cfg['weather_country']??'NL');
$fallback=['ok'=>false,'city'=>$city,'temperature'=>null,'code'=>-1,'is_day'=>true,'wind'=>null,'description'=>'Weer tijdelijk niet beschikbaar'];
$weather=cs_cache('weather:'.strtolower($country.':'.$city),600,function()use($city,$country){
  $geo=cs_cache('geo:'.strtolower($country.':'.$city),604800,function()use($city,$country){
    $url='https://geocoding-api.open-meteo.com/v1/search?name='.rawurlencode($city).'&count=1&language=nl&format=json'.($country!==''?'&countryCode='.rawurlencode($country):'');
    $j=json_decode(cs_remote($url,4)??'',true);$r=$j['results'][0]??null;
    return is_array($r)&&isset($r['latitude'],$r['longitude'])?['lat'=>$r['latitude'],'lon'=>$r['longitude']]:[];
  });
  if(!isset($geo['lat'],$geo['lon']))return [];
  $url='https://api.open-meteo.com/v1/forecast?latitude='.rawurlencode((string)$geo['lat']).'&longitude='.rawurlencode((string)$geo['lon']).'&current=temperature_2m,relative_humidity_2m,precipitation,rain,snowfall,weather_code,wind_speed_10m,is_day&timezone=Europe%2FAmsterdam';
  $j=json_decode(cs_remote($url,4)??'',true);$cur=$j['current']??null;
  if(!is_array($cur)||!isset($cur['temperature_2m']))return [];
  $code=(int)($cur['weather_code']??-1);
  $description='Bewolkt';
  if($code===0)$description='Helder';elseif(in_array($code,[1,2],true))$description='Licht bewolkt';elseif($code===3)$description='Bewolkt';elseif(in_array($code,[45,48],true))$description='Mist';elseif(in_array($code,[51,53,55,56,57],true))$description='Motregen';elseif(in_array($code,[61,63,65,66,67,80,81,82],true))$description='Regen';elseif(in_array($code,[71,73,75,77,85,86],true))$description='Sneeuw';elseif(in_array($code,[95,96,99],true))$description='Onweer';
  return ['ok'=>true,'city'=>$city,'temperature'=>round((float)$cur['temperature_2m']),'code'=>$code,'is_day'=>!empty($cur['is_day']),'wind'=>round((float)($cur['wind_speed_10m']??0)),'description'=>$description];
},$fallback);
echo json_encode($weather,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
