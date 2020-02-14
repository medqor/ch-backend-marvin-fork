<?php
error_reporting(E_ALL);
ini_set('display_errors', true);

$GLOBALS['service'] = new stdClass();


$whitelist = [
    '2605:6000:1a05:c65e:8428:faa8:6bc4:500b',  // PAM IPV6
    '135.26.225.34',                            //Office
    '136.61.22.74',
    '136.32.138.86',                            //Marvin
    '173.172.105.209'                           //Pam IPV4
];

define('HTTP_HOST', $_SERVER['HTTP_HOST']);


//if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) &&
//    !in_array($_SERVER['HTTP_X_FORWARDED_FOR'], $whitelist) && $_SERVER['HTTP_USER_AGENT'] != 'EasyCron/1.0 (https://www.easycron.com/)') {
//
//    die('unauthorized');
//    exit;
//}

define('PAGE_START', microtime(true));

define('ROOT', realpath(__DIR__ . '/..'));
ini_set('display_errors', 0);
require_once(ROOT . '/config.php');
require_once (ROOT . '/lib/include.php');

date_default_timezone_set('US/Central');


set_error_handler('myErrorHandler');

register_shutdown_function('fatalErrorShutdownHandler');
function myErrorHandler($code, $message, $file, $line)
{
    $snippets=[];
    $traces_to_ignore=0;
    $traces = debug_backtrace();
    $ret = array();
    foreach($traces as $i => $call){
        if ($i < $traces_to_ignore  || $i == count($traces)-1) {
            continue;
        }


        if(isset($call['args']) &&
            isset($call['args'][1]) &&
            !is_array( isset($call['args'][1]) ) &&
            strstr($call['args'][1],'Stack trace')){
            $errorLine=explode("\n",$call['args'][1]);

            $errorLine=$errorLine[2];

            $errorLine=explode(":",$errorLine);
            $function = $errorLine[1];
            $errorLine=explode(" ",$errorLine[0]);
            $errorLine=explode("(",$errorLine[1]);
            $fileName=trim($errorLine[0]);
            if(isset($errorLine[1])){
                $errorLine=trim(str_replace(")","",$errorLine[1]));
            }else{
                $errorLine=trim(str_replace(")","",$errorLine[0]));
            }

            $fileSource = explode("\r\n", file_get_contents($fileName));
            $snippet = [];
            for ($x = $errorLine - 6; $x <= $errorLine + 5; $x++) {
                if (isset($fileSource[$x])) {
                    if ($x == ($errorLine - 1)) {
                        $snippet[] = sprintf("<strong style='color:green'>%s</strong>", htmlspecialchars($fileSource[$x]));
                    } else {
                        $snippet[] = sprintf("%s", htmlspecialchars($fileSource[$x]));
                    }
                }
            }
            if($errorLine !=0){
               $snippets[$file.":".$errorLine] = "<pre class='error_code'>" . implode("\r\n", $snippet) . "</pre>";
            }
            unset($traces[$i]);

       }else{
            echo pretty_print_r($call['args'],true);
        }

        $object = '';
//        if (isset($call['class'])) {
//            $object = $call['class'].$call['type'];
//            if (is_array($call['args'])) {
//                foreach ($call['args'] as &$arg) {
//                    get_arg($arg);
//                }
//            }
//        }

//        $ret[] = '#'.str_pad($i - $traces_to_ignore, 3, ' ')
//            .$object.$call['function'].'('.implode(', ', $call['args'])
//            .') called at ['.$call['file'].':'.$call['line'].']';
    }




    $source = explode("\r\n", file_get_contents($file));
    $snippet = [];
    for ($x = $line - 6; $x <= $line + 5; $x++) {
        if (isset($source[$x])) {
            if ($x == ($line - 1)) {
                $snippet[] = sprintf("<strong style='color:green'>%s</strong>", htmlspecialchars($source[$x]));
            } else {
                $snippet[] = sprintf("%s", htmlspecialchars($source[$x]));
            }
        }
    }
    $snippets[$file.':'.$line] = "<pre class='error_code'>" . implode("\r\n", $snippet) . "</pre>";
    $req = array_merge($_GET, $_POST);
    $url = sprintf("http://%s%s?%s", $_SERVER['SERVER_NAME'], $_SERVER['REQUEST_URI'], http_build_query($_GET));
    $form = [];
    foreach (array_merge($_GET,$_POST) as $key => $value) {
        $form[] = sprintf("<label style='width:10em;display:inline-block'>%s</label></label><textarea name='%s' type='text'>%s</textarea><br/>", $key, $key, str_replace("'", "\'", $value));
    }
    $form = count($form)?sprintf("<form method='post' action='%s'>%s<input type='submit' value='resubmit'></form>", $url, implode("\r\n", $form)):'';

    $template = file_get_contents(ROOT . '/views/error/error.phtml');
    $application = $GLOBALS['service'];
    unset($application->config);
    $snippet="";
    foreach($snippets as $loc=>$sn){
        $snippet.=sprintf('<span style="color:blue">%s</span><br/>%s<br/><hr>',$loc,$sn);
    }
    $output = sprintf($template,
        date("Y-m-d H:i:s"),
        $code,
        ENVIRONMENT,
        $file,
        $line,
        pretty_print_r($message,true),
        implode("<hr/>",$ret),
        $snippet,
        byte_format(memory_get_usage(), 2),
        print_r($_GET, true),
        print_r($_POST, true),
        print_r((array)json_decode(file_get_contents("php://input"),true),true),
        $form,
        $GLOBALS['last_query'] ?? '',
        print_r(['application' => $application, 'environment' => $_ENV ?? 'headless', 'session' => $_SESSION ?? 'sessionless', 'server' => $_SERVER ?? 'headless', 'args' => $argv ?? []], true)

    );

    $GLOBALS['error'] = true;

    echo $output;
   // mailer('mfrancois@medqor.com', 'Backend Error', $output);

    exit;
}

function fatalErrorShutdownHandler()
{
    $last_error = error_get_last();
    if ($last_error['type'] === E_ERROR) {
        // fatal error
        myErrorHandler(E_ERROR, $last_error['message'], $last_error['file'], $last_error['line']);
    }
}

$_controller = $registry->get('controller');
$_model = $registry->get('model');

require_once ROOT . '/core/models/shared.core.php';
require_once ROOT . '/core/models/sql.core.php';
$model = sprintf(ROOT . "/controllers/$_controller/models/%s.model.php", $_controller);
require_once file_exists($model) ? $model : ROOT . '/core/models/base.model.php';

require_once ROOT . '/core/service.class.php';

$controller = sprintf(ROOT . "/controllers/$_controller/%s.controller.php", $_controller);
require_once $controller;

$GLOBALS['service'] = new $_controller();
$GLOBALS['service']->clock_start = microtime(true);

$GLOBALS['service']->execute();






