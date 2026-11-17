<?php
declare(strict_types=1);
require_once __DIR__.'/site.php';
if(session_status()!==PHP_SESSION_ACTIVE){
  $https=(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')||($_SERVER['HTTP_X_FORWARDED_PROTO']??'')==='https';
  session_set_cookie_params(['lifetime'=>0,'path'=>rtrim(dirname($_SERVER['SCRIPT_NAME']??'/'),'/').'/','secure'=>$https,'httponly'=>true,'samesite'=>'Lax']);
  session_start();
}
function cs_authfile(): string {return cs_root().'/data/admin-auth.json';}
function cs_authed(): bool {return !empty($_SESSION['cs_admin']);}
function cs_auth_hash(): string {
  $a=is_file(cs_authfile())?json_decode((string)@file_get_contents(cs_authfile()),true):[];
  return is_array($a)?(string)($a['hash']??''):'';
}
function cs_csrf(): string {if(empty($_SESSION['cs_csrf']))$_SESSION['cs_csrf']=bin2hex(random_bytes(24));return $_SESSION['cs_csrf'];}
function cs_verify_csrf(): bool{return isset($_POST['csrf'])&&hash_equals(cs_csrf(),(string)$_POST['csrf']);}
function cs_set_password(string $password): bool {
  if(strlen($password)<12)return false;
  return cs_write(cs_authfile(),json_encode(['hash'=>password_hash($password,PASSWORD_DEFAULT),'updated'=>date('c')],JSON_UNESCAPED_SLASHES));
}
