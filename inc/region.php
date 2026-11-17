<?php
declare(strict_types=1);
require_once __DIR__.'/site.php';
require_once __DIR__.'/external.php';
const CS_REGION_TING='---TING---';
function cs_region_defaults(): array {
  $c=cs_config();$old=$c['region']??[];
  $items=[];for($i=0;$i<6;$i++){
    $v=$old['items'][$i]??[];
    $items[]=['kop'=>(string)($v['title']??''),'text'=>(string)($v['text']??''),'media'=>(string)($v['image']??''),'blur'=>0,'darken'=>35,'slide'=>!empty($v['slide']),'show'=>!empty($v['show'])];
  }
  return ['intro'=>(string)($old['intro']??'Welkom bij de regioagenda.'),'outro'=>(string)($old['outro']??'Tot zover de regioagenda.'),'items'=>$items,'sec'=>12,'darken'=>35,'media'=>'','style_kop'=>['font'=>'system','size'=>21,'color'=>'#62d8cc','shadow'=>'#000000'],'style_body'=>['font'=>'system','size'=>14,'color'=>'#ffffff','shadow'=>'#000000'],'updated'=>0];
}
function cs_region_file(): string{return cs_root().'/data/region.json';}
function cs_region_load(): array {
  $base=cs_region_defaults();$f=is_file(cs_region_file())?cs_region_file():cs_root().'/data/region.default.json';
  $j=is_file($f)?json_decode((string)@file_get_contents($f),true):[];
  if(!is_array($j))return $base;
  $d=cs_merge($base,$j);
  $d['items']=array_values(array_slice($d['items'],0,6));
  for($i=0;$i<6;$i++)$d['items'][$i]=array_merge($base['items'][$i],is_array($d['items'][$i]??null)?$d['items'][$i]:[]);
  return $d;
}
function cs_region_clean($value,int $max=2600): string {
  $s=str_replace(["\r\n","\r"],"\n",cs_limit($value,$max));
  $s=preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/','',$s);
  return trim((string)$s);
}
function cs_region_script(array $d,int $edition): string {
  if(!in_array($edition,[0,1,2],true))return '';
  $parts=[];
  if(trim($d['intro']??'')!=='')$parts[]=trim($d['intro']);
  $start=$edition===2?3:0;$end=$edition===1?3:6;
  for($i=$start;$i<$end;$i++){
    $text=trim($d['items'][$i]['text']??'');if($text!=='')$parts[]=$text;
  }
  // Geen onderwerpen betekent: maak nooit opnieuw een oude opname met alleen intro/outro.
  if(count($parts)<(trim($d['intro']??'')!==''?2:1))return '';
  $glue=$edition===0?"\n\n":"\n\n".CS_REGION_TING."\n\n";
  $body=implode($glue,$parts);
  if(trim($d['outro']??'')!=='')$body.="\n\n".trim($d['outro']);
  return $body."\n";
}
function cs_region_save(array $post,array &$errors): bool {
  $old=cs_region_load();$d=$old;
  foreach(['intro'=>1200,'outro'=>1200] as $k=>$n)$d[$k]=cs_region_clean($post[$k]??'',$n);
  $d['sec']=cs_int($post['sec']??12,3,120,12);
  $d['darken']=cs_int($post['darken']??35,0,90,35);
  $d['media']=cs_image($post['media']??'');
  foreach(['style_kop','style_body'] as $k){
    $r=$post[$k]??[];if(!is_array($r))$r=[];
    $d[$k]=['font'=>cs_font($r['font']??''),'size'=>cs_int($r['size']??19,10,52,19),'color'=>cs_color($r['color']??'#ffffff'),'shadow'=>cs_color($r['shadow']??'#000000')];
  }
  for($i=0;$i<6;$i++){
    $r=$post['items'][$i]??[];if(!is_array($r))$r=[];
    $d['items'][$i]=['kop'=>cs_region_clean($r['kop']??'',120), 'text'=>cs_region_clean($r['text']??'',2600), 'media'=>cs_upload('region_file',cs_image($r['media']??''),$errors,$i), 'blur'=>cs_int($r['blur']??0,0,30,0), 'darken'=>cs_int($r['darken']??35,0,90,35), 'slide'=>isset($r['slide']), 'show'=>isset($r['show'])];
  }
  $d['updated']=time();
  $json=json_encode($d,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
  if($json===false||!cs_write(cs_region_file(),$json."\n")){$errors[]='Kon regioagenda niet opslaan.';return false;}
  return true;
}
function cs_region_slides(array $d): array {
  $out=[];for($i=0;$i<6;$i++){$r=$d['items'][$i]??[];
    if(empty($r['slide'])||trim((string)($r['text']??''))==='')continue;
    $out[]=['title'=>$r['kop']?:'Regioagenda','subtitle'=>$r['text'],'image'=>$r['media']?:$d['media'],'caption_mode'=>'overlay','title_color'=>$d['style_kop']['color'],'subtitle_color'=>$d['style_body']['color'],'caption_bg'=>'#102636','title_size'=>(int)$d['style_kop']['size']];
  }
  return $out;
}
function cs_region_txt_url(int $edition): string {
  $override=cs_external_key('static_regio_txt_url');
  if($override!==''){
    // Expliciete link kan als basis-URL ingesteld worden; route vanuit STATIC blijft onafhankelijk.
    return rtrim($override,'/').'/ff-regioagenda-'.$edition.'.txt';
  }
  $https=!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off';
  if(strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']??'')==='https')$https=true;
  $host=(string)($_SERVER['HTTP_HOST']??'');
  if(!preg_match('/^[a-zA-Z0-9.:-]+$/D',$host))$host='localhost';
  $path=rtrim(str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME']??'/region-admin.php')),'/');
  return ($https?'https':'http').'://'.$host.$path.'/region-text.php?edition='.$edition;
}
function cs_region_github_request(string $method,string $endpoint,?array $payload=null): array {
  $tok=cs_github_token();if($tok==='')return [0,'GitHub-token ontbreekt in je externe configuratie.'];
  if(!function_exists('curl_init'))return [0,'PHP cURL ontbreekt.'];
  $ch=curl_init('https://api.github.com'.$endpoint);
  curl_setopt_array($ch,[CURLOPT_CUSTOMREQUEST=>$method,CURLOPT_HTTPHEADER=>['Authorization: Bearer '.$tok,'Accept: application/vnd.github+json','Content-Type: application/json','User-Agent: CMB-STATIC-Regioagenda'],CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>20,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2]);
  if($payload!==null)curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($payload));
  $body=curl_exec($ch);$code=(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE);
  if($body===false)$body='Verbindingsfout: '.curl_error($ch);
  curl_close($ch);return [$code,(string)$body];
}
function cs_region_repo(): string {
  $repo=cs_external_key('static_regio_repo')?:cs_external_key('regio_repo')?:'johndedooy-hub/focus-nieuws-tts';
  return preg_match('~^[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+$~D',$repo)?$repo:'';
}
function cs_region_workflow(): string {
  $workflow=cs_external_key('regio_workflow');return preg_match('/^[a-zA-Z0-9_.-]+\.ya?ml$/D',$workflow)?$workflow:'regio-editie-tts.yml';
}
function cs_region_dispatch(int $edition): array {
  if(!in_array($edition,[1,2],true))return [false,'Ongeldige editie'];
  if(cs_region_script(cs_region_load(),$edition)==='')return [false,'Vul eerst een onderwerp in.'];
  $repo=cs_region_repo();if($repo==='')return [false,'Stel regio_repo in het externe cmb-config.php-bestand in.'];
  [$code,$body]=cs_region_github_request('POST','/repos/'.$repo.'/actions/workflows/'.rawurlencode(cs_region_workflow()).'/dispatches',['ref'=>'main','inputs'=>['edition'=>(string)$edition,'txt_url'=>cs_region_txt_url($edition)]]);
  if($code===204)return [true,'GitHub-opdracht voor editie '.$edition.' is gestart.'];
  $j=json_decode($body,true);return [false,'GitHub antwoordde '.$code.': '.(is_array($j)?($j['message']??'onbekend'):substr($body,0,180))];
}
function cs_region_status(): array {
  if(cs_region_repo()==='')return ['ok'=>false,'message'=>'Geen regio_repo ingesteld'];
  [$code,$body]=cs_region_github_request('GET','/repos/'.cs_region_repo().'/actions/workflows/'.rawurlencode(cs_region_workflow()).'/runs?per_page=12');
  if($code!==200)return ['ok'=>false,'message'=>'GitHub-status niet beschikbaar ('.$code.')'];
  $d=json_decode($body,true);$eds=[];
  foreach(($d['workflow_runs']??[]) as $run){
    if(!preg_match('/editie\s*(\d)/i',(string)($run['display_title']??$run['name']??''),$m))continue;
    $ed=(int)$m[1];if(isset($eds[$ed]))continue;
    $eds[$ed]=['status'=>$run['status']??'','conclusion'=>$run['conclusion']??'','url'=>$run['html_url']??''];
  }
  return ['ok'=>true,'eds'=>$eds];
}
