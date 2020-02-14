<?php
/**
 * functions.
 * Because sometimes you just want to extend PHP with certain functions you think should have been there to start with
 */


function live_template($view, $pheanstalk = false)
{

    buffer_flush(
        '<html><head><script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script><link href="/css/bootstrap.theme.css" rel="stylesheet">
<script src="/js/bootstrap.min.js"></script>
</head><body>');
    buffer_flush(file_get_contents("../views/{$view}.phtml"));
    buffer_flush_jquery();
    buffer_flush('</body></html');

    live_flush('<!-- COmment-->');
}

function live_tube($view, $pheanstalk = false)
{
sleep(20);
    file_put_contents('/var/log/pheanstalk.log','new request'."\r\n",FILE_APPEND);

    // Cause we are clever and don't want the rest of the script to be bound by a timeout.
    // Set to zero so no time limit is imposed from here on out.
    set_time_limit(0);

    // Client disconnect should NOT abort our script execution
    ignore_user_abort(true);
    file_put_contents('/var/log/pheanstalk.log', __FILE__ . ":" . __LINE__ . "\r\n", FILE_APPEND);
    // Clean (erase) the output buffer and turn off output buffering
    // in case there was anything up in there to begin with.
   // ob_end_clean();

    // Turn on output buffering, because ... we just turned it off ...
    // if it was on.
    ob_start();
    $pheanstalk = $pheanstalk ?? "job_" . microtime_true;



    ?>
    <html>
    <head>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <link href="/css/bootstrap.theme.css" rel="stylesheet">
        <script src="/js/bootstrap.min.js"></script>
        <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
    </head>
    <body id="pheanstalkapplication">
    <input id="tube" value="<?php echo $pheanstalk;?>">
    <?php
    echo file_get_contents("../views/{$view}.phtml");
    ?>
    </body>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="/js/pheanstalk.js" data-data="{'tube':'<?php echo $pheanstalk;?>'}"></script>
    </html>
    <?php

    // Return the length of the output buffer
    $size = ob_get_length();
    file_put_contents('/var/log/pheanstalk.log', __FILE__ . ":" . __LINE__ . "\r\n", FILE_APPEND);
    // send headers to tell the browser to close the connection
    // remember, the headers must be called prior to any actual
    // input being sent via our flush(es) below.
    header("Connection: close\r\n");
    header("Content-Encoding: none\r\n");
    header("Content-Length: $size");
    file_put_contents('/var/log/pheanstalk.log', __FILE__ . ":" . __LINE__ . "\r\n", FILE_APPEND);
    // Set the HTTP response code
    // this is only available in PHP 5.4.0 or greater
    http_response_code(200);
    file_put_contents('/var/log/pheanstalk.log', __FILE__ . ":" . __LINE__ . "\r\n", FILE_APPEND);
    // Flush (send) the output buffer and turn off output buffering
    ob_end_flush();

    // Flush (send) the output buffer
    // This looks like overkill, but trust me. I know, you really don't need this
    // unless you do need it, in which case, you will be glad you had it!
    @ob_flush();
    file_put_contents('/var/log/pheanstalk.log', __FILE__ . ":" . __LINE__ . "\r\n", FILE_APPEND);
    // Flush system output buffer
    // I know, more over kill looking stuff, but this
    // Flushes the system write buffers of PHP and whatever backend PHP is using
    // (CGI, a web server, etc). This attempts to push current output all the way
    // to the browser with a few caveats.
    flush();
file_put_contents('/var/log/pheanstalk.log','flushed new request'."\r\n",FILE_APPEND);


}

function live_append($line = '', $target = 'live_flush')
{
    live_flush($line, $target, true);
}

function live_reload($url = 'self')
{
    if ($url == 'self') {
        buffer_flush("<script>window.location=window.location</script>");
    } else {
        buffer_flush("<script>window.location='$url'</script>");
    }


}

/**
 * @param string $line
 * @param string $target
 */
function live_flush($line = '', $target = 'live_flush', $append = false)
{
    if (!isset($_REQUEST[$target])) {
        $_REQUEST[$target] = 0;
    } else {
        $_REQUEST[$target]++;
    }

    buffer_flush_jquery();
    if (!isset($_REQUEST['jquery_sent'][$target])) {

        buffer_flush("<script>if($('#{$target}').length==0){document.write(\"<div id='$target'></div>\");}</script>");
        $_REQUEST['jquery_sent'][$target] = true;
    }

    $line = str_replace('"', '\"', $line);
    $line = str_replace(['\n', '\r', '\r\n'], '\r\n', $line);
    $lines = explode("\r\n", $line);
    $id = sprintf("%s_%d", $target, $_REQUEST[$target]);
    foreach ($lines as $line) {
        $line = trim(str_replace(['\n', '\r', '\r\n'], '<br/>', $line));
        if ($append == false) {
            buffer_flush("<script class='$id'>$('#{$target}').html(\"$line\");</script>", false);
        } else {

            buffer_flush("<script class='$id'>$('#{$target}').append(\"<br/>$line\");</script>", false);
        }
        $append = true;
    }


    if ($_REQUEST[$target] > 0) {
        buffer_flush("<script class='$id'>$('.{$id}').remove();</script>", false);
    }
}

function isWebRequest()
{
    return isset($_SERVER['HTTP_USER_AGENT']);
}

function number_format_short($n, $precision = 1)
{
    if ($n < 900) {
        // 0 - 900
        $n_format = number_format($n, $precision);
        $suffix = '';
    } else if ($n < 900000) {
        // 0.9k-850k
        $n_format = number_format($n / 1000, $precision);
        $suffix = 'K';
    } else if ($n < 900000000) {
        // 0.9m-850m
        $n_format = number_format($n / 1000000, $precision);
        $suffix = 'M';
    } else if ($n < 900000000000) {
        // 0.9b-850b
        $n_format = number_format($n / 1000000000, $precision);
        $suffix = 'B';
    } else {
        // 0.9t+
        $n_format = number_format($n / 1000000000000, $precision);
        $suffix = 'T';
    }
    // Remove unecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
    // Intentionally does not affect partials, eg "1.50" -> "1.50"
    if ($precision > 0) {
        $dotzero = '.' . str_repeat('0', $precision);
        $n_format = str_replace($dotzero, '', $n_format);
    }
    return $n_format . $suffix;
}

function live_prepend($line = '', $target = 'live_flush')
{
    if (!isset($_REQUEST[$target])) {
        $_REQUEST[$target] = 0;
    } else {
        $_REQUEST[$target]++;
    }

    buffer_flush_jquery();
    if (!isset($_REQUEST['jquery_sent'][$target])) {

        buffer_flush("<script>if($('#{$target}').length==0){document.write(\"<div id='$target'></div>\");}</script>");
        $_REQUEST['jquery_sent'][$target] = true;
    }

    $line = str_replace('"', '\"', $line);
    $line = str_replace("\r\n", '<br/>', $line);
    $id = sprintf("%s_%d", $target, $_REQUEST[$target]);

    buffer_flush("<script class='$id'>$('#{$target}').prepend(\"$line\");</script>", false);


    if ($_REQUEST[$target] > 0) {
        buffer_flush("<script class='$id'>$('.{$id}').remove();</script>", false);
    }
}

function live_pre($line = '', $target = 'live_flush')
{
    buffer_flush_jquery();
    if (!isset($_REQUEST['jquery_sent'][$target])) {

        buffer_flush("<script>if($('#{$target}').length==0){document.write(\"<div id='$target'></div>\");}</script>");
        $_REQUEST['jquery_sent'][$target] = true;
    }
    $line = str_replace('"', '\"', $line);
    $lines = explode("\r\n", $line);
    foreach ($lines as $single_line) {
        buffer_flush("<script>
$('#{$target}').append(\"{$single_line} \");
$('#{$target}').append('\\n');
</script>", false);
    }

}

function buffer_flush_jquery()
{
    if (!isset($_REQUEST['jquery_sent'])) {
        buffer_flush('

<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>');

    }
}

function buffer_progress($value, $target = 'progressbar')
{
    buffer_flush_jquery();
    if (!isset($_REQUEST['progress_sent'][$target])) {
        buffer_flush("<script>if($('#{$target}').length==0){\$(\"#progressbars\").append(\"<div id='$target'></div>\");$('#$target' ).progressbar({
  value: $value
});}</script>");
        $_REQUEST['progress_sent'][$target] = true;
    } else {
        if (!isset($_REQUEST['progressbar_initialized'][$target])) {
            buffer_flush("<script>$('#$target' ).progressbar({value:$value});</script>", false);
            $_REQUEST['progressbar_initialized'][$target] = true;
        }
        buffer_flush("<script class='progressbar'>$('#$target' ).progressbar( 'option', 'value', $value);$('script.progressbar').remove();</script>", false);
    }
}

function memory_flush($target = 'memory')
{

    $text = convert_size(memory_get_usage(true)) . " Used / " . convert_size(memory_get_peak_usage(true)) . ' Peak';
    live_flush($text, $target);

}

function buffer_flush($line = '', $br = true)
{
    if (ob_get_level() == 0) ob_start();
    if ($br === true) {
        echo "<br/>";
    }
    echo $line;
    echo str_pad('', 4096) . "\n";
    ob_flush();
    flush();
    if (trim($line)) {
        #file_put_contents('log.txt',$line);
    }

}


function rglob($pattern, $flags = 0)
{
    $files = glob($pattern, $flags);
    foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
        $files = array_merge($files, rglob($dir . '/' . basename($pattern), $flags));
    }
    return $files;
}

function md($a, $b)
{

    //echo pretty_print_r("$a --- $b",true);
    if (is_array($a) && is_array($b)) {
        return md($a, $b);
    } elseif (is_array($a) || is_array($b)) {
        return false;
    } else {
        return $a == $b;
    }

}

/**
 * @param $bytes
 * @param int $precision
 * @return string
 */

function byte_format($bytes, $precision = 2)
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    // Uncomment one of the following alternatives
    $bytes /= (1 << (10 * $pow));

    return round($bytes, $precision) . ' ' . $units[$pow];
}


/**
 * @param $data
 * @param bool $return
 * @return string
 */
function pretty_print_r($data, $return = false)
{
    $text = sprintf("<pre>%s</pre>", print_r($data, true));
    if ($return == true) {
        return $text;
    }
    die($text);
}

function interpolate($sql, $params, $return = false)
{
    // generate a readable version of the query (with all parameters replaced)
    $interpolated = $sql;
    foreach ($params as $key => $value) {
        if (is_int($value)) {
            $interpolated = str_replace($key, (int)$value, $interpolated);
        } else {
            $interpolated = str_replace($key, "'$value'", $interpolated);
        }
    }
    return pretty_print_r($interpolated, $return);
}

function get_redirect_url($url)
{
    $redirect_url = null;

    $url_parts = @parse_url($url);
    if (!$url_parts) return false;
    if (!isset($url_parts['host'])) return false; //can't process relative URLs
    if (!isset($url_parts['path'])) $url_parts['path'] = '/';

    $sock = fsockopen($url_parts['host'], (isset($url_parts['port']) ? (int)$url_parts['port'] : 80), $errno, $errstr, 30);
    if (!$sock) return false;

    $request = "HEAD " . $url_parts['path'] . (isset($url_parts['query']) ? '?' . $url_parts['query'] : '') . " HTTP/1.1\r\n";
    $request .= 'Host: ' . $url_parts['host'] . "\r\n";
    $request .= "Connection: Close\r\n\r\n";
    fwrite($sock, $request);
    $response = '';
    while (!feof($sock)) $response .= fread($sock, 8192);
    fclose($sock);

    if (preg_match('/^Location: (.+?)$/m', $response, $matches)) {
        if (substr($matches[1], 0, 1) == "/")
            return $url_parts['scheme'] . "://" . $url_parts['host'] . trim($matches[1]);
        else
            return trim($matches[1]);

    } else {
        return false;
    }

}

/**
 * get_all_redirects()
 * Follows and collects all redirects, in order, for the given URL.
 *
 * @param string $url
 * @return array
 */
function get_all_redirects($url)
{
    $redirects = array();
    while ($newurl = get_redirect_url($url)) {
        if (in_array($newurl, $redirects)) {
            break;
        }
        $redirects[] = $newurl;
        $url = $newurl;
    }
    return $redirects;
}

/**
 * get_final_url()
 * Gets the address that the URL ultimately leads to.
 * Returns $url itself if it isn't a redirect.
 *
 * @param string $url
 * @return string
 */
function get_final_url($url)
{
    $redirects = get_all_redirects($url);
    if (count($redirects) > 0) {
        return array_pop($redirects);
    } else {
        return $url;
    }
}

function pcrypt($unencrypted)
{
    return crypt($unencrypted, HASH . $unencrypted);
}

function check_pcrypt($unencrypted, $encrypted)
{
    return (crypt($unencrypted, HASH . $unencrypted) == $encrypted);
}

function generateValidXmlFromObj(stdClass $obj, $node_block = 'nodes', $node_name = 'node')
{
    $arr = get_object_vars($obj);
    return generateValidXmlFromArray($arr, $node_block, $node_name);
}

function generateValidXmlFromArray($array, $node_block = 'nodes', $node_name = 'node')
{
    $xml = '<?xml version="1.0" encoding="UTF-8" ?>';

    $xml .= "\n" . '<' . $node_block . '>';
    $xml .= "\n" . generateXmlFromArray($array, $node_name);
    $xml .= "\n" . '</' . $node_block . '>';

    return $xml;
}


function generateXmlFromArray($array, $node_name)
{
    $xml = '';
    $idx = 0;
    if (is_array($array) || is_object($array)) {
        foreach ($array as $key => $value) {
            if (is_numeric($key)) {
                $key = $node_name;
            }
            $idx++;
            if ($idx > 1) {
                $xml .= "\n";
            }
            $xml .= '<' . $key . '>' . generateXmlFromArray($value, $node_name) . '</' . $key . '>';
        }
    } else {
        $xml = htmlspecialchars($array, ENT_QUOTES);
    }

    return $xml;
}


function num2alpha($n)
{
    for ($r = ""; $n >= 0; $n = intval($n / 26) - 1) {
        $r = chr($n % 26 + 0x41) . $r;
    }

    return $r;
}

function pretty_time($difference)
{
    $days = floor($difference / 86400);
    $hours = floor(($difference - $days * 86400) / 3600);
    $minutes = floor(($difference - $days * 86400 - $hours * 3600) / 60);
    $seconds = floor($difference - $days * 86400 - $hours * 3600 - $minutes * 60);

    return ($days > 0 ? " $days Days " : "") . ($hours > 0 ? " $hours Hours " : "") . ($minutes > 0 ? " $minutes Minutes " : "") . ($seconds > 0 ? " $seconds Seconds " : "");
}


/**
 * remove pipes from all fields
 * @param $row
 * @return array
 */
function cleanPipes($row)
{
    if (!is_array($row)) {
        return $row;
    }
    foreach ($row as $idx => $val) {
        $row[$idx] = utf8_encode(trim(str_replace("|", "/", $val)));
    }

    return $row;
}


function getFileHandle($file, $mode = 'r+')
{

    $fp = fopen($file, 'r+');
    return $fp;
}

function is_valid_password($password)
{
    return preg_match('/^(?=.*\d)(?=.*[A-Za-z])[0-9A-Za-z!@^#$%]{8,20}$/', $password);
}

