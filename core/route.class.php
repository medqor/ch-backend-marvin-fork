<?php
$GLOBALS['api_version']='';
$registry = Registry::getInstance();
$registry->set('request_method',strtolower($_SERVER['REQUEST_METHOD']??'GET'));

$path = array_slice(explode('/', isset($_GET['request']) ? $_GET['request'] : 'home/'.$registry->get('request_method')), 0);
unset($_GET['request']);
unset($_GET['0']);
$request = array();


if (is_array($path) && count($path) > 0) {



    preg_match_all( '/[v]\d/m', $path[0], $matches);
    if(isset($matches[0][0])){
        $GLOBALS['api_version']=$matches[0][0];
        array_shift($path);

    }



    while (count($path) && !trim($path[0])) {
        array_shift($path);
    }

    $path[0] = (!array_key_exists(0, $path)) || !file_exists(sprintf('./../controllers/%s/%s.controller.php', $path[0],$path[0])) ? 'home' : $path[0];

    $registry->set('controller', $path[0]);
    $registry->set('model', file_exists(sprintf('../models/%s.model.php', $path[0])) ? $path[0] : 'base');

    array_shift($path);

    if (!array_key_exists(0, $path)) {
        $path[0] = 'view';
    } elseif ($path[0] == '') {
        $path[0] = 'view';
    } elseif (strstr($path[0], '?')) {
        $path[0] = substr($path[0], 0, strpos($path[0], '?'));
    }
    $registry->set('action', $path[0]);
    array_shift($path);
if($registry->get('controller') !='install' && file_exists('./../sql/user.sql')){
   $registry->set('controller', 'install');
    $registry->set('action', 'view');
}

    if ($registry->get('controller') === 'home' && $registry->get('action') === 'view' && count($_GET)) {
        $pseudoRequest = array_values($_GET);
        require_once './../routes/routes.php';

        $simpleOption = $pseudoRequest[0];
        $routedOption = $pseudoRequest[0] . '/' . $pseudoRequest[1];
        if (isset($routes[$simpleOption])) {
            $registry->set('controller', $routes[$simpleOption][0]);
            $registry->set('action', $routes[$simpleOption][1]);
        } elseif (isset($routes[$routedOption])) {
            $registry->set('controller', $routes[$routedOption][0]);
            $registry->set('action', $routes[$routedOption][1]);
        }
    }
    $stack=(array)$registry->get('action');
    if( $GLOBALS['api_version']!=''){
        $action=$stack[0];
        $version=preg_replace("/[^0-9]/","",$GLOBALS['api_version']);
        for($x=2; $x<=$version;$x++){
          $stack[]=sprintf("v%d_%s",$x,$action);
        }
       // $stack=array_reverse($stack);
    }
    $registry->set('action', $stack);

    // the rest of the path is the query string
    $num = count($path);
    if ($num > 0) {
        for ($i = 0; $i < $num; $i += 2) {
            if ($pos = strpos($path[$i], '?')) {
                $request[$path[$i]] = substr($path, $pos + 1);
            } else if (array_key_exists($i + 1, $path)) {
                if ($pos = strpos($path[$i + 1], '?')) {

                    $request[$path[$i]] = substr($path[$i + 1], 0, $pos);
                    list($key, $var) = explode('=', substr($path[$i + 1], $pos + 1));
                    $request[$key] = $var;
                } else {
                    $path[$i] = urldecode($path[$i]);

                    if (false !== ($pos = strpos($path[$i], '[]'))) {
                        $key = substr($path[$i], 0, $pos);

                        if (false === isset($request[$key]) || false === is_array($request[$key])) {
                            $request[$key] = array();
                        }

                        $request[$key][] = $path[$i + 1];
                    } else {
                        $request[$path[$i]] = $path[$i + 1];
                    }
                }
            }
        }
    }


    if (count($request)) {
        foreach ($request as $key => $vals) {
            if (!$registry->get($key)) {
                $registry->set($key, $vals);
            }
        }
        $registry->set('request', array_merge($registry->get('request'), $request, $_GET, $_POST));
    } else {
        $registry->set('request', array_merge((array)$registry->get('request'), $_GET, $_POST));
    }

}
