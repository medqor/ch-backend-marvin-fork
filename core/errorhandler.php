<?php


set_error_handler('myErrorHandler');

register_shutdown_function('fatalErrorShutdownHandler');
function myErrorHandler($code, $message, $file, $line)
{

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
    $snippet = "<pre class='error_code'>" . implode("\r\n", $snippet) . "</pre>";
    $req = array_merge($_GET, $_POST);
    $url = sprintf("http://%s%s?%s", $_SERVER['SERVER_NAME'], $_SERVER['REQUEST_URI'], http_build_query($_GET));
    $form = [];
    foreach ($_POST as $key => $value) {
        $form[] = sprintf("<label style='width:10em;display:inline-block'>%s</label></label><textarea name='%s' type='text'>%s</textarea><br/>", $key, $key, str_replace("'", "\'", $value));
    }
    $form = sprintf("<form method='post' action='%s'>%s<input type='submit' value='resubmit'></form>", $url, implode("\r\n", $form));

    $template = file_get_contents(ROOT . '/views/error/error.phtml');
    $application = $GLOBALS['service'];
    unset($application->config);
    $output = sprintf($template,
        date("Y-m-d H:i:s"),
        $code,
        ENVIRONMENT,
        $file,
        $line,
        $message,
        $snippet,
        byte_format(memory_get_usage(), 2),
        print_r($_GET, true),
        print_r($_POST, true),
        $form,
        $GLOBALS['last_query'] ?? '',
        print_r(['application' => $application, 'environment' => $_ENV ?? 'headless', 'session' => $_SESSION ?? 'sessionless', 'server' => $_SERVER ?? 'headless', 'args' => $argv ?? []], true)

    );

    $GLOBALS['error'] = true;

    echo $output;
    mailer('mfrancois@medqor.com', 'Backend Error', $output);

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
