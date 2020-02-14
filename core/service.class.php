<?php

use Aws\S3\S3Client;

if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
    // Windows
    define('SLASH', '\\');
} else {
    // Linux/Unix
    define('SLASH', '/');
}

error_reporting(E_ERROR);

$_SESSION['token'] = isset($_SESSION['token']) ? $_SESSION['token'] : md5(microtime(true));

require_once sprintf("%s/config/%s.php", ROOT, ENVIRONMENT);
require_once sprintf('%s/vendor/autoload.php', ROOT);



set_time_limit(0);
require_once 'view.class.php';

class service
{
    use Model;
    use View;


    public $_action = array('default');
    private $_action_found = false;
    public $css = [];
    public $js = [];
    public $footer_js = [];
    public $footer_js_code = [];
    protected $_view = 'home/index';
    protected $_variables = [];
    protected $tube;

    public $_result = array('status' => 'initial', 'html' => 'Initial');
    public $clock_start = 0;

    protected function check_token()
    {
        if (!$this->registry->get('request', 'token') || $this->registry->get('request', 'token') != $_SESSION['token']) {
            $this->_result = array(
                'status' => 'error',
                'message' => 'Sorry, we encountered an error. Please try again or contact your Account Manager.',
            );
            exit;
        }
    }

    protected function revalidateSession($id = false)
    {
        if ($id != false) {
            $sess = ['id' => 1];
        } else {
            if (isset($_SESSION[APPLICATION])) {
                $sess = @unserialize($_SESSION[APPLICATION]);

            } else {
                if (isset($_COOKIE[APPLICATION])) {
                    $sess = @unserialize($_COOKIE[APPLICATION]);
                } else {
                    $sess = ['id' => 0];
                }

            }
        }
        $record = $this->read($this->_db, "SELECT * from user where id =:id", [':id' => $sess['id']], 'fetch');
        if ($record['status'] == 1) {
            $this->CreateSession($record);
        } else {
            if ($this->registry->get('controller') != 'login') {
                if (!strstr($_SERVER['REQUEST_URI'], 'ajax')) {
                    $_SESSION['redirect_on_login'] = $_SERVER['REQUEST_URI'];
                }

                header('location: /login');
            }
        }

    }

    protected function _preprocess()
    {




        // Load up the registry. this contains the important data we gleaned from the route class
        $this->registry = Registry::getInstance();
        $this->config = Registry::getConfig();
        $this->_dbh();
        //Easycron is considered SYSTEM user


//
//        if (isset($_SESSION[APPLICATION])) {
//
//            $this->registry->set(APPLICATION, @unserialize($_SESSION[APPLICATION]));
//            $this->registry->set('ACCESSLEVEL', $this->registry->get(APPLICATION, 'level'));
//        } else {
//            if (isset($_COOKIE[APPLICATION])) {
//                $this->registry->set(APPLICATION, @unserialize($_COOKIE[APPLICATION]));
//                $this->registry->set('accesslevel', $this->registry->get(APPLICATION, 'level'));
//            } else {
//                if (!in_array($this->registry->get('controller'), ['login', 'workers', 'ajax'])) {
//
//                    if (!isset($_SESSION['redirects'])) {
//                        $_SESSION['redirects'] = 0;
//                    }
//
//                    header(sprintf('location: %slogin ', WEB_ROOT));
//
//                }
//            }
//        }

        // Load up the registry. this contains the important data we gleaned from the route class
        $this->registry->set('title', TITLE);
        $this->registry->set('subtitle', SUBTITLE);
        $this->_format = 'html';
        $body_request = json_decode(file_get_contents('php://input'), true) ?? [];
        $this->_request = array_merge($_GET, $_POST, $body_request);

        $potential_actions = $this->registry->get('action');
        while (count($potential_actions)) {
            $potential_action = array_pop($potential_actions);
            if (method_exists($this, $potential_action)) {
                $this->_action = $potential_action;

                break;
            }
        }
        if (is_array($this->_action)) {
            $this->_action = $this->_action[0];
        }
        $this->registry->set('action', $this->_action);
        $this->registry->set('request', $this->_request);
        if (isset($this->_request['template']) && is_array($this->_request['template']) && count($this->_request['template'])) {
            $this->_returnTemplate = $this->_request['template'];
        }
        if (isset($this->_request['variables']) && is_array($this->_request['variables']) && count($this->_request['variables'])) {
            $this->_variables = $this->_request['variables'];
        }
        //$this->_action = method_exists($this, $this->registry->get('action')) ? $this->registry->get('action') : $this->_action;
        $this->findForRoute('css');
        $this->findForRoute('js');
        // Get our database handle


    }


    protected function getParam($param, $default = false)
    {
        return $this->registry->get('request', $param) ?? $default;
    }

    protected function CreateSession($session)
    {
        unset($session['password']);
        setcookie(APPLICATION, serialize($session), time() + (60 * 60 * 24 * 7), '/', $_SERVER['SERVER_NAME']);
        $_SESSION[APPLICATION] = serialize($session);

        return $this->SetSession();
    }

    protected function SetSession()
    {
        if (isset($_SESSION[APPLICATION])) {
            $this->registry->set(APPLICATION, @unserialize($_SESSION[APPLICATION]));
            $this->registry->set('ACCESSLEVEL', $this->registry->get(APPLICATION, 'level'));
            $GLOBALS['ACCESSLEVEL'] = $this->registry->get(APPLICATION, 'level');
            return (object)@unserialize($_SESSION[APPLICATION]);
        } else {
            if (isset($_COOKIE[APPLICATION])) {
                $this->registry->set(APPLICATION, @unserialize($_COOKIE[APPLICATION]));
                $this->registry->set('ACCESSLEVEL', $this->registry->get(APPLICATION, 'level'));
                $GLOBALS['ACCESSLEVEL'] = $this->registry->get(APPLICATION, 'level');
                return (object)@unserialize($_COOKIE[APPLICATION]);
            }
            return false;
        }

    }

    protected function UnsetSession()
    {
        unset($_SESSION[APPLICATION]);
        unset($_COOKIE[APPLICATION]);
        setcookie(APPLICATION, '', time() + 1000, '/', $_SERVER['SERVER_NAME']);
        $this->registry->set(APPLICATION, false);
    }

    public function __destruct()
    {
        /*
         * Universal exit when we hit our external errorhandler
         */
        if (isset($GLOBALS['error'])) {
            exit;
        }

        /*
         * Universal close-out. If you forget to exit, you may not reach here, and you will have a bad day.
         */
        if ($this->_format != 'plainjson') {
            $now = microtime(true);
            $this->_result['elapsed'] = number_format(($now - $this->clock_start), 3);
        }

        /*
         * Allow other exit methods
         */
        if (isset($this->registry->get[APPLICATION])) {
            $_SESSION[APPLICATION] = serialize($this->registry->get['auth']);
        }

        if (isset($this->_result['status']) && ($this->_result['status'] == -1 || $this->_result['status'] == false)) {
            if (!isset($this->_result['error_level'])) {
                $this->_result['error_level'] = 'logentries:email';
            }
            if (isset($this->_result['message']) && !isset($this->_result['error'])) {
                $this->_result['error'] = $this->_result['message'];
            }
            if (class_exists('Logentries') && defined('LOGENTRIES_TOKEN')) {
                $logentries = new Logentries();
                $logentries->info('Error in ' . $this->_action . ': ' . implode(",", (array)$this->_result['error']), 'PD411_Advertiser');
            }


        }
        /*
         * Sometimes we need the error message up to this point fo logging, but have a alternate "friendly" message for the end user. we swap that back in here.
         */
        if (isset($this->_result['user_message'])) {
            $this->_result['message'] = $this->_result['user_message'];
            unset($this->_result['user_message']);
        }

        if (method_exists($this, $this->_format) && in_array($this->_format, $this->allowed_formats)) {
            call_user_func(array($this, $this->_format));
        } else {
            /*
             * But default to JSON
             */
            $this->json();
        }
    }

    public function _init()
    {
        /**
         * _init()
         *
         * @brief Fire off the initial action. We do this in the index.php file
         */
        $this->start = strtotime('now');
        if (is_array($this->_action)) {
            $actions = $this->_action;
        } else {
            $actions = (array)$this->_action;
        }

        foreach ($actions as $action) {

            if (!method_exists($this, $action)) {
                $this->_result = array(
                    'status' => 'error',
                    'html' => "A critical error was encountered at " . __FUNCTION__ . ":" . (__LINE__ - 3) . ", attempting to call a non-existent action " . $action,
                );
                exit();
            }

            if (!in_array($action, $this->allowed_actions)) {
                $this->_result = array(
                    'status' => 'error',
                    'html' => "A critical error was encountered at " . __FUNCTION__ . ":" . (__LINE__ - 3) . ", attempting to call a non-allowed action " . $action,
                );
                exit();
            }

            call_user_func(array($this, $action));
        }
    }

    /**
     *
     */
    public function execute()
    {
        if (is_array($this->_action)) {
            $actions = $this->_action;
        } else {
            $actions = (array)$this->_action;
        }
        foreach ($actions as $action) {

            if (preg_match("/[v]\d_/", $action)) {
                $allowed_check = explode("_", $action);
                unset($allowed_check[0]);
                $allowed_check = implode("_", $allowed_check);
            } else {
                $allowed_check = $action;
            }
            if (method_exists($this, $action) && in_array($allowed_check, $this->allowed_actions)) {

                call_user_func(array($this, $action));
            } else {
                $this->_format = 'html';
                $this->_view = 'error/unknown_route';
                if (method_exists($this, $action)) {
                    $this->_result = array(
                        'status' => 'error',
                        'html' => "The action $action exists by does not appear to be callable.",
                    );
                } else {
                    $this->_result = array(
                        'status' => 'error',
                        'html' => sprintf("A critical error was encountered  attempting to call %s.", $this->registry->get('action')),
                    );
                }

                exit();
            }
        }
        exit();
    }

    /**
     *
     */
    public function getController()
    {
        $request = array();
    }


    /*
      * Quick helper function so we can see what queries are doing.
      */

    /**
     * @param $code
     */
    protected function addFooterJsCode($code)
    {
        $this->footer_js_code[] = "
<!-- Footer Inserted Code--> 
<script>
    $code
</script>
<!-- END Footer Inserted Code--> ";

    }


    protected function sqlAssign($arr)
    {
        foreach ($arr as $key => $value) {
            unset($arr[$key]);
            $arr[sprintf(":%s", $key)] = $value;
        }
        return $arr;
    }

    protected function autoAssign($fields, $assignToThis = false, $evenIfEmpty = true)
    {
        $return = [];
        foreach ($fields as $field) {
            if ($evenIfEmpty == true) {
                $return[$field] = trim($this->registry->get('request', $field));
            } else {
                $elem = $this->registry->get('request', $field);
                if ($elem !== false) {
                    $return[$field] = trim($elem);
                }
            }
        }
        if ($assignToThis === true) {
            foreach ($return as $field => $value) {
                $this->$field = $value;
            }
        }
        return $return;

    }

    protected function generatePassword($length = 15)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&";
        return substr(str_shuffle($chars), 0, $length);
    }

    protected function getSessionItem($key)
    {
        if (!isset($_SESSION[APPLICATION])) {
            return false;
        }
        $user = json_decode(json_encode(unserialize($_SESSION[APPLICATION])), true);
        if (!is_array($user) || !count($user)) {
            return false;
        }


        $user['token'] = $_SESSION['token'];
        $user['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $user['title'] = $user['title'];
        $user['level'] = $user['level'];
        $this->registry->set('admin', $user['level'] == 1 ? true : false);
        $user['admin'] = $user['level'] == 1 ? true : false;

        if (isset($user[$key])) {
            return $user[$key];
        }
        return false;


    }

    public function uuidSecure()
    {

        $pr_bits = null;
        $fp = @fopen('/dev/urandom', 'rb');
        if ($fp !== false) {
            $pr_bits .= @fread($fp, 16);
            @fclose($fp);
        } else {
            $this->cakeError('randomNumber');
        }

        $time_low = bin2hex(substr($pr_bits, 0, 4));
        $time_mid = bin2hex(substr($pr_bits, 4, 2));
        $time_hi_and_version = bin2hex(substr($pr_bits, 6, 2));
        $clock_seq_hi_and_reserved = bin2hex(substr($pr_bits, 8, 2));
        $node = bin2hex(substr($pr_bits, 10, 6));

        /**
         * Set the four most significant bits (bits 12 through 15) of the
         * time_hi_and_version field to the 4-bit version number from
         * Section 4.1.3.
         * @see http://tools.ietf.org/html/rfc4122#section-4.1.3
         */
        $time_hi_and_version = hexdec($time_hi_and_version);
        $time_hi_and_version = $time_hi_and_version >> 4;
        $time_hi_and_version = $time_hi_and_version | 0x4000;

        /**
         * Set the two most significant bits (bits 6 and 7) of the
         * clock_seq_hi_and_reserved to zero and one, respectively.
         */
        $clock_seq_hi_and_reserved = hexdec($clock_seq_hi_and_reserved);
        $clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved >> 2;
        $clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved | 0x8000;

        return sprintf('%08s-%04s-%04x-%04x-%012s',
            $time_low, $time_mid, $time_hi_and_version, $clock_seq_hi_and_reserved, $node);
    }


    /**
     * @param $source
     * @param $filename
     * @return string
     * Download large files
     */
    protected function downloadLarge($source, $filename, $ignoreExisting = false)
    {


        $target = $this->getImportFilePath($filename);
        if ($ignoreExisting == false && file_exists($target) && filemtime($target) > strtotime('-5 hours')) {
            live_flush("Already found existing zip. $target", 'file');
            return $target;
        }
        live_flush("downloading $source to $target");

        set_time_limit(0);
        $fp = fopen($target, 'w+');

        $ch = curl_init(str_replace(" ", "%20", $source));
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        return $target;
    }

    protected function human_filesize($bytes, $decimals = 2)
    {
        $size = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . @$size[$factor];
    }

    protected function filesizeLimit($fsize, $tlimit)
    {
        $size = explode(" ", $fsize);
        if (count($size) !== 2) {
            throw new Exception("Invalid size sent to " . __FUNCTION__);
        }
        $limit = explode(" ", $tlimit);
        if (count($limit) !== 2) {
            throw new Exception("Invalid limit sent to " . __FUNCTION__);
        }
        $sizeRank = array_flip(array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'));
        if ($sizeRank[$size[1]] <= $sizeRank[$limit[1]] && (float)$size[0] < (float)$limit[0]) {
            return true;
        }
        return false;
//

    }

    protected function uploadFiles($file,$key)
    {

        $s3Client = S3Client::factory(array(
            'credentials' => array(
                'key' => AWS_ACCESS_KEY_ID,
                'secret' => AWS_SECRET_ACCESS_KEY,

            ),
            'region' => 'us-west-2',
            'version' => 'latest'
        ));
        $s3Client->putObject(array(
            'Bucket' => AWS_BUCKET,
            'Key' => $key,
            'SourceFile' => $file,
            'StorageClass' => 'REDUCED_REDUNDANCY'
        ));

        $s3Client = false;


    }

    protected function wc($path){
        $wc= explode(" ",shell_exec(sprintf("wc -l %s",$path)));
        return ($wc[0]);
    }
protected function livestalk($payload, $tube = false){

        $this->tube=$tube??$this->tube;

    $payload['tube_used']=$this->tube;
    file_put_contents('/var/log/pheanstalk.log',json_encode($payload),FILE_APPEND);
        if(!isset($this->pheanstalk)){
            $this->pheanstalk = new Pheanstalk('127.0.0.1:11300');
        }
        if(!isset($this->tube)){
            $this->tube=$this->uuidSecure();
        }
try {
    $id = $this->pheanstalk
        ->useTube($this->tube)
        ->put(json_encode($payload));
}catch(Exception $e){
            echo $this->tube;
            echo json_encode($payload);
            echo $e->getMessage();
}
}
    protected function mongoimport($db, $collection, $file, $type, $mode, $jsonArray = false, $upsertFields = false)
    {
        if ($type == 'csv' && !strstr($type, 'headerline')) {
            $type .= ' --headerline --parseGrace skipField';
        }

        if ($type == 'tsv' && !strstr($type, 'headerline')) {
            $type .= ' --headerline --parseGrace skipField';
        }

        $cmd = sprintf("mongoimport --ssl --host %s --sslCAFile %s --username %s --password %s --db=%s --type=%s --mode=%s  --collection=%s --numInsertionWorkers=8 --file='%s' %s  %s >/dev/null 2>&1 &",
            MONGO_HOST,
            MONGO_CAFILE,
            MONGO_USER,
            MONGO_PASS,
            $db,
            $type,
            $mode,
            $collection,
            realpath($file),
            $upsertFields != false ? '--upsertFields ' . $upsertFields : '',
            $jsonArray == true ? '--jsonArray' : ''

        );

      //  live_append($cmd, 'command');
        file_put_contents('/var/log/pheanstalk.log', $cmd . "\r\n");

        exec($cmd, $outputArray, $result);
        return $result == 0 ? true : false;

    }

    protected function removePath($path)
    {

        array_map('unlink', glob($path . "/*"));
    }

    protected function unzip($file, $removeExisting = false, $path = 'nppes_file/')
    {
        $zip = new ZipArchive;
        $path = $this->getImportFilePath($path);
        if (file_exists($path) && filemtime($path) > strtotime('-5 hours')) {
            if ($removeExisting === true) {
                #    live_flush("Removed existing csv.", 'file');
                $this->removePath($path);

            } else {
                #   live_flush("Already found existing csv. [$path]", 'file');
                return $path;
            }

        }
        #  live_flush("Preparing to unzip $file to $path",'file');

        $res = $zip->open($file);
        if ($res === TRUE) {


            $zip->extractTo($path);
            $zip->close();

            live_flush("Unzipped $file to $path", 'file');
        } else {

            live_flush("Unzip failed for  $file to $path", 'file');
        }
        return $path;
    }

    protected function process_csv($table, $file_base, $flushSize = 100000, $delimiter = ',')
    {
        $extension = '.csv';
        $source = $this->getImportFileHandle($file_base . $extension);
        $cmd = sprintf("wc -l < %s", $this->getImportFilePath($file_base . $extension));
        $this->totalFileLines = number_format(trim(shell_exec($cmd)), 0, '.', '');
        live_flush(number_format($this->totalFileLines, 0), 'total');
        $cnt = 0;
        $idx = 0;
        $columns = [];
        $counts = [];
        $head = fgetcsv($source, 0, $delimiter);


        $head[] = 'etldate';

        foreach ($head as $idx => $col) {
            $head[$idx] = strtolower(trim($col));
            $columns[$head[$idx]] = 'INTEGER';
            $counts[$head[$idx]] = 1;
        }
        $fileName = $file_base . $cnt . '.csv';


        $dest = $this->getExportFileHandle($fileName);
        fputcsv($dest, $head, $delimiter);
        while (!feof($source)) {
            $this->records++;
            $idx++;
            if ($idx % 1000 == 0) {
                $recsPerSecond = $idx / (microtime(true) - $this->start);
                live_flush(number_format($idx, 0), 'idx');
                live_flush(number_format($recsPerSecond, 1) . "/s", 'ps');
                buffer_progress(($idx / $this->totalFileLines) * 100);

            }
            if ($flushSize!=-1 && $idx % $flushSize == 0) {
                $cnt++;
                fclose($dest);
                live_flush($fileName, 'file');
                $this->uploadFiles($fileName);

                $fileName = $file_base . $cnt . '.csv';
                $dest = $this->getExportFileHandle($fileName);
                fputcsv($dest, $head, $delimiter);
            }
            $line = fgetcsv($source, 0, $delimiter);

#            $line[15] = date('Y-m-d',strtotime($line[1]));
            if (is_array($line) && count($line)) {

                $line = $this->cleanPipes($line);

                $line[] = date('Y-m-d H:i:s', strtotime($this->etldate ?? 'now'));

                if (count($line) != count($head)) {

                    live_flush("Skipped $idx:" . print_r($head, true) . print_r($line, true), 'sql');

                } else {
                    $line = array_combine($head, $line);
                    foreach ($line as $ct => $val) {
                        $val=str_replace("\t","",trim($val));
                        if (strlen($val) > $counts[$ct]) {
                            $counts[$ct] = strlen($val) + 5;
                        }
                        if (!is_integer($val)) {
                            $columns[$ct] = 'VARCHAR';
                            if (strlen($val) > $counts[$ct]) {
                                $counts[$ct] = strlen($val) + 10;
                            }
                        }
                    }
                    fputs($dest, implode($delimiter, $line) . "\r\n");
                }
            }

        }
        fclose($dest);
        live_flush(number_format($idx, 0), 'idx');


        live_flush($fileName, 'file');
        $this->uploadFiles($fileName);

        if($flushSize==-1){
            return $fileName;
        }
    }


    protected function getFileHandle($file)
    {

        $fp = fopen($file, 'r+');
        return $fp;
    }

    protected function getImportFileHandle($file)
    {

        $fp = fopen('/import/' . $file, 'r+');
        return $fp;
    }

    protected function getImportFilePath($file)
    {

        return '/import/' . $file;

    }

    protected function getExportFilePath($file)
    {

        return '/export/' . $file;

    }


    protected function getExportFileHandle($file, $mode = 'w+')
    {

        $fp = fopen('/export/' . $file, $mode);
        return $fp;
    }


    /**
     * remove pipes from all fields
     * @param $row
     * @return array
     */
    protected function cleanPipes($row)
    {
        if (!is_array($row)) {
            return $row;
        }
        foreach ($row as $idx => $val) {
            $row[$idx] = utf8_encode(trim(str_replace("|", "/", $val)));
        }

        return $row;
    }

    public function unload($select, $path)
    {
        $path .= "_";

        $template = "UNLOAD ('%s')  
            to 's3://" . AWS_BUCKET . "/{$path}'  
            access_key_id '" . AWS_ACCESS_KEY . "' 
            secret_access_key '" . AWS_SECRET_ACCESS_KEY . "' 
            HEADER
            PARALLEL OFF
            ALLOWOVERWRITE
            CSV
            ";

        $subselect = explode("\n", $select);
        array_splice($subselect, 3, count($subselect) - 6, ['...']);

        $subselect = implode("\r\n", $subselect);

        $sql = sprintf($template, $select);
        $display_sql = sprintf($template, $subselect);

        pretty_print_r($sql);

        $this->_format = 'live';

        live_flush($display_sql, 'display');
        live_append($this->write($this->_redshift, $sql));


    }

}
