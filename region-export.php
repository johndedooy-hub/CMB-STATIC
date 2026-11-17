<?php
declare(strict_types=1);
require_once __DIR__.'/inc/auth.php';if(!cs_authed()){header('Location: admin.php');exit;}
$c=cs_config()['region'];$edition=(int)($_GET['editie']??1);$edition=$edition===2?2:1;
$txt=trim((string)$c['intro'])."\n\n";
for($i=($edition-1)*3;$i<$edition*3;$i++){ $r=$c['items'][$i]??[];if(empty($r['title'])&&empty($r['text']))continue;$txt.='---TING---'."\n".trim((string)($r['title']??''))."\n".trim((string)($r['text']??''))."\n\n";}
$txt.=trim((string)$c['outro'])."\n";
header('Content-Type: text/plain; charset=utf-8');header('Content-Disposition: attachment; filename="regioagenda-editie-'.$edition.'.txt"');header('Cache-Control: no-store');echo $txt;
