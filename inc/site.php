<?php
declare(strict_types=1);
/** CMB STATIC – zelfstandige, lichtgewicht configuratie- en beveiligingslaag. */
date_default_timezone_set('Europe/Amsterdam');
function cs_root(): string { return dirname(__DIR__); }
function cs_e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function cs_defaults(): array {
    return [
      'theme'=>['bg_color'=>'#070b10','card_color'=>'#141c25','accent_color'=>'#d7b16e','accent_alt'=>'#c9d0d6','text_color'=>'#f3f3ef','muted_color'=>'#b0b7be','nav_color'=>'#0c151e','tile_text_color'=>'#ffffff','font_family'=>'system-ui, -apple-system, Segoe UI, sans-serif','base_font_size'=>'16px','bg_video'=>'','bg_image'=>'assets/img/studio-ambient.webp','bg_mode'=>'image','bg_opacity'=>42,'border_radius'=>5,'max_width'=>1440,'glass'=>78,'motion'=>'subtle'],
      'radio'=>['station_name'=>'Focus Eighties','eyebrow'=>'LUXE RADIO • ONLINE','logo'=>'','stream_url'=>'https://icecast.omroep.nl/radio5-bb-mp3','backup_stream_url'=>'','metadata_url'=>'','backup_metadata_url'=>'','cover'=>'','current_show'=>'80s Music Keeps You Young','default_artist'=>'ON AIR','default_title'=>'Luister live','stream_label'=>'LIVE RADIO','volume'=>75,'failover'=>true,'stream_recheck_sec'=>60,'streams'=>[]],
      'topbar'=>['weather_city'=>'Amsterdam','weather_country'=>'NL','weather_temp'=>'','weather_mode'=>'auto','weather_animation'=>true,'weather_accent'=>'#8cdff4','weather_opacity'=>65,'time_seconds'=>false,'whatsapp'=>'https://wa.me/31612345678','verkeer_url'=>'https://www.anwb.nl/verkeer','ov_url'=>'https://9292.nl'],
      'layout'=>['menu_title'=>'NAVIGATIE','slide_interval'=>8,'slide_overlay'=>48,'slide_height'=>336,'tile_columns'=>4,'welcome_visible'=>true,'region_visible'=>true,'region_heading'=>'Uit de regio','ticker_visible'=>false,'top_rounded'=>true],
      'menu'=>[['label'=>'Home','url'=>'#home','active'=>true],['label'=>'Nieuws','url'=>'#nieuws','active'=>false],['label'=>'Weer','url'=>'#weer','active'=>false],['label'=>'Regioagenda','url'=>'#regio','active'=>false],['label'=>'Verkeer','url'=>'#verkeer','active'=>false],['label'=>'Extra & contact','url'=>'#contact','active'=>false]],
      'slides'=>[], 'tiles'=>[],
      'welcome'=>['heading'=>'Welkom bij Focus Eighties','text'=>'Wij brengen muziek, regio-info en studiobelevenis in één compacte pagina. Open nieuws, weer, regio, verkeer en extra via het menu zonder dat de muziek stopt.','eyebrow'=>'JOUW RADIO, JOUW MOMENT','button_text'=>'Contact met de studio','button_url'=>'#contact'],
      'ticker'=>['enabled'=>true,'mode'=>'marquee','speed'=>60,'color'=>'#f3eee4','bg_color'=>'#07121d','font_size'=>13,'items'=>[['text'=>'Welkom bij Focus Eighties • luxe webplayer • nieuws • weer • regio • verkeer','url'=>''],['text'=>'Menu opent als matglas-paneel over de slides • de muziek blijft gewoon doorspelen','url'=>'']]],
      'pl'=>['email'=>'studio@focusfantastique.nl','title'=>'Meer van de studio','privacy'=>'Wij gaan zorgvuldig om met je gegevens. Voor vragen over privacy kun je mailen naar het studio-adres.','blocks'=>array_fill(0,10,['img'=>'','url'=>'','text'=>''])],
      'region'=>['heading'=>'Regioagenda','intro'=>'Nieuws en activiteiten uit de regio','outro'=>'Heb je een tip? Mail de studio.','show_slides'=>false,'items'=>array_fill(0,6,['title'=>'','text'=>'','image'=>'','show'=>false,'slide'=>false])],
      'contact'=>['heading'=>'Contact met de studio','text'=>'Een verzoekje, tip of bericht? Laat van je horen.'],
      'labels'=>['time'=>'NEDERLANDSE TIJD','weather'=>'HET WEER','hero'=>'IN DE STUDIO','hero_info'=>'LIVE UIT DE STUDIO','highlight'=>'UITGELICHT','highlight_info'=>'PROGRAMMA’S & MEER','menu_live'=>'ALTIJD AAN','menu_live_info'=>'Jouw muziek. Jouw moment.','slide_brand'=>'ON AIR','ticker'=>'STUDIONIEUWS','pl_footer'=>'Direct naar jouw favoriete links','studio_eyebrow'=>'DE STUDIO','privacy_heading'=>'Privacy & contact','footer'=>'Altijd dichtbij, altijd muziek.'],
    ];
}
function cs_merge(array $base, array $saved): array {
  foreach ($saved as $key=>$value) {
    if (isset($base[$key]) && is_array($base[$key]) && is_array($value) && !array_is_list($base[$key])) $base[$key]=cs_merge($base[$key],$value);
    else $base[$key]=$value;
  }
  return $base;
}
function cs_config(): array {
  static $conf=null;
  if ($conf !== null) return $conf;
  $path=cs_root().'/data/config.json';
  if(!is_file($path))$path=cs_root().'/data/config.default.json';
  $saved=is_file($path)?json_decode((string)@file_get_contents($path),true):[];
  if(!is_array($saved)) $saved=[];
  return $conf=cs_merge(cs_defaults(),$saved);
}
function cs_write(string $path,string $data): bool {
  $dir=dirname($path); if(!is_dir($dir)) @mkdir($dir,0755,true);
  $tmp=$path.'.'.bin2hex(random_bytes(5)).'.tmp';
  if(file_put_contents($tmp,$data,LOCK_EX)===false) return false;
  @chmod($tmp,0640); if(@rename($tmp,$path)) return true;
  @unlink($tmp);return false;
}
function cs_save(array $data): bool {
  $json=json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_INVALID_UTF8_SUBSTITUTE);
  return $json!==false && cs_write(cs_root().'/data/config.json',$json."\n");
}
function cs_color($value,string $fallback='#ffffff'): string {
  return is_string($value)&&preg_match('/^#[0-9a-fA-F]{6}$/D',$value)?$value:$fallback;
}
function cs_limit($value,int $max=300): string {
  $v=trim((string)$value);
  if(function_exists('mb_substr'))return mb_substr($v,0,$max,'UTF-8');
  if(function_exists('iconv_substr')){$r=iconv_substr($v,0,$max,'UTF-8');if($r!==false)return $r;}
  return substr($v,0,$max);
}
function cs_int($value,int $min,int $max,int $fallback): int {return is_numeric($value)?max($min,min($max,(int)$value)):$fallback;}
function cs_url($value,bool $allowAnchor=true): string {
  $v=trim((string)$value);
  if($v==='' || strlen($v)>1600 || preg_match('/[\x00-\x1F\\\\]/',$v))return '';
  if($allowAnchor&&preg_match('/^#[a-zA-Z0-9_-]+$/',$v))return $v;
  if($allowAnchor&&str_starts_with($v,'mailto:')&&filter_var(substr($v,7),FILTER_VALIDATE_EMAIL))return $v;
  if(preg_match('~^https?://[^\s]+$~iD',$v))return $v;
  if(in_array($v,['region-view.php','index.php','admin.php'],true))return $v;
  if(preg_match('~^(?:uploads/|assets/)[a-zA-Z0-9_./-]+$~D',$v) && strpos($v,'..')===false)return $v;
  return '';
}
function cs_image($v): string {return cs_url($v,false);}
function cs_font($value): string {
  $allowed=['system-ui, -apple-system, Segoe UI, sans-serif','Arial, Helvetica, sans-serif','Georgia, Times New Roman, serif','Trebuchet MS, Arial, sans-serif','Verdana, Geneva, sans-serif'];
  return in_array($value,$allowed,true)?$value:$allowed[0];
}
function cs_upload(string $key,string $current,array &$errors,int $index=-1): string {
  $file=$_FILES[$key]??null;
  if(!$file)return $current;
  $entry=$index<0?$file:array_map(static fn($arr)=>is_array($arr)?($arr[$index]??null):null,$file);
  $err=(int)($entry['error']??UPLOAD_ERR_NO_FILE);
  if($err===UPLOAD_ERR_NO_FILE)return $current;
  if($err!==UPLOAD_ERR_OK){$errors[]="Upload $key mislukt (code $err).";return $current;}
  $tmp=(string)($entry['tmp_name']??'');$size=(int)($entry['size']??0);
  if(!is_uploaded_file($tmp)){$errors[]='Upload niet herkend.';return $current;}
  if($size>25*1048576){$errors[]='Bestand is groter dan 25 MB.';return $current;}
  $mime=(new finfo(FILEINFO_MIME_TYPE))->file($tmp);
  $extensions=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif','video/mp4'=>'mp4','video/webm'=>'webm'];
  if(!isset($extensions[$mime])){$errors[]='Alleen JPG, PNG, WEBP, GIF, MP4 en WEBM zijn toegestaan.';return $current;}
  if($key!=='bg_video_upload'&&str_starts_with($mime,'video/')){$errors[]='Voor dit onderdeel is alleen een afbeelding toegestaan.';return $current;}
  if($key==='bg_video_upload'&&!str_starts_with($mime,'video/')){$errors[]='De videoachtergrond verwacht MP4 of WEBM.';return $current;}
  if(str_starts_with($mime,'image/')&&$size>8*1048576){$errors[]='Afbeelding is groter dan 8 MB.';return $current;}
  $folder=cs_root().'/uploads';if(!is_dir($folder))@mkdir($folder,0755,true);
  $relative='uploads/'.bin2hex(random_bytes(12)).'.'.$extensions[$mime];
  if(!move_uploaded_file($tmp,cs_root().'/'.$relative)){$errors[]='Upload kon niet worden opgeslagen.';return $current;}
  @chmod(cs_root().'/'.$relative,0644);
  return $relative;
}
function cs_remote(string $url,int $timeout=4): ?string {
  if(!preg_match('~^https?://~i',$url))return null;
  if(function_exists('curl_init')) {
    $ch=curl_init($url);
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>2,CURLOPT_TIMEOUT=>$timeout,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>2,CURLOPT_USERAGENT=>'CMB-STATIC/2.0',CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_ENCODING=>'',CURLOPT_MAXFILESIZE=>524288]);
    $body=curl_exec($ch);$code=curl_getinfo($ch,CURLINFO_RESPONSE_CODE);curl_close($ch);
    return $body!==false&&$code>=200&&$code<300&&strlen((string)$body)<=524288?(string)$body:null;
  }
  $ctx=stream_context_create(['http'=>['timeout'=>$timeout,'header'=>"User-Agent: CMB-STATIC/2.0\r\n",'follow_location'=>0],'ssl'=>['verify_peer'=>true,'verify_peer_name'=>true]]);
  $body=@file_get_contents($url,false,$ctx,0,524288);
  return $body===false?null:$body;
}
function cs_cache(string $key,int $ttl,callable $build,array $fallback=[]): array {
  $dir=cs_root().'/data/cache';if(!is_dir($dir))@mkdir($dir,0755,true);
  $file=$dir.'/'.hash('sha256',$key).'.json';
  $read=static function() use($file): ?array {$d=is_file($file)?json_decode((string)@file_get_contents($file),true):null;return is_array($d)?$d:null;};
  $cached=$read();if($cached && time()-(int)($cached['at']??0)<$ttl)return $cached['value'];
  $lock=@fopen($file.'.lock','c');if(!$lock) return $cached['value']??$fallback;
  if(!flock($lock,LOCK_EX|LOCK_NB)){fclose($lock);return $cached['value']??$fallback;}
  $cached=$read();
  if(!$cached || time()-(int)($cached['at']??0)>=$ttl){
    try{$val=$build();if(is_array($val)&&$val){cs_write($file,json_encode(['at'=>time(),'value'=>$val],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));$cached=['value'=>$val];}}catch(Throwable $e){error_log('CMB STATIC cache: '.$e->getMessage());}
  }
  flock($lock,LOCK_UN);fclose($lock);
  return $cached['value']??$fallback;
}
function cs_security_headers(): void {
  header('X-Content-Type-Options: nosniff');header('Referrer-Policy: strict-origin-when-cross-origin');
  header('X-Frame-Options: SAMEORIGIN');
}
