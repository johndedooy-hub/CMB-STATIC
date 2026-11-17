<?php
 declare(strict_types=1);
 require_once __DIR__.'/inc/auth.php'; require_once __DIR__.'/inc/streams.php';cs_security_headers();header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');
 if(!cs_authed()){http_response_code(401);echo json_encode(['ok'=>false,'message'=>'Log opnieuw in.']);exit;}
 if($_SERVER['REQUEST_METHOD']!=='POST'||!cs_verify_csrf()){http_response_code(403);echo json_encode(['ok'=>false,'message'=>'Ongeldige aanvraag of sessie.']);exit;}
 $name=cs_limit($_POST['name']??'',100);$s=$_POST['stream']??null;
 if($name===''||!is_array($s)){http_response_code(400);echo json_encode(['ok'=>false,'message'=>'Voer een zendernaam in.']);exit;}
 $ok=cs_preset_save($name,cs_stream_clean($s));
 if(!$ok)http_response_code(500);
 echo json_encode(['ok'=>$ok,'message'=>$ok?'Zender opgeslagen in stream-presets.json':'Opslaan mislukt (controleer schrijfrechten).']);
