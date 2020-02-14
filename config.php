<?php
define('ENVIRONMENT',$_SERVER['REDIRECT_ENVIRONMENT']?? ($_SERVER['ENVIRONMENT'] ?? 'production'));
require_once sprintf('config/%s.php',ENVIRONMENT);
require_once 'vendor/autoload.php';
require_once 'extend/functions.php';
require_once 'core/registry.class.php';
require_once 'core/route.class.php';
require_once 'core/mongo.class.php';

define("VIEWDIR", __DIR__ . "/views/");
define('WEB_ROOT', '/');
//------INSTALL-GENERATED--------//

//------END-INSTALL-GENERATED--------//

