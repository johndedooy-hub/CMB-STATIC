<?php
declare(strict_types=1);
require_once __DIR__.'/inc/region.php';cs_security_headers();
$edition=(int)($_GET['edition']??0);
if(!in_array($edition,[0,1,2],true)){http_response_code(400);exit('Ongeldige editie');}
header('Content-Type: text/plain; charset=utf-8');header('Cache-Control: public, max-age=60');
echo cs_region_script(cs_region_load(),$edition);
