<?php


trait View{

    protected $_format = 'html';
    protected $allowed_formats = array('json', 'html', 'xml', 'xls', 'buffer', 'raw', 'live','template','terminate');
    protected $documentationMethod = 'jsPDF';
    protected $view_routes_searched = [];
    protected $_template = 'index';
    protected $_returnTemplate = false;

    /**
     * @param bool $view
     * @param string $format
     */
    protected function setView($view=false, $format='html'){
        $this->_view=$view??(sprintf("%s/%s",$this->registry->get('controller'),$this->registry->get('action')));
        $this->_format=$format;

    }
    protected function template(){
        if($this->_returnTemplate != false && is_array($this->_returnTemplate)){


        }
        $this->json();
    }


    protected function multi_diff($arr1,$arr2){
        $result = array();
        foreach ($arr1 as $k=>$v){
            if(!isset($arr2[$k])){
                $result[$k] = $v;
            } else {
                if(is_array($v) && is_array($arr2[$k])){
                    $diff = $this->multi_diff($v, $arr2[$k]);
                    if(!empty($diff))
                        $result[$k] = $diff;
                }
            }
        }
        return $result;
    }

    protected function checkTemplatedFile($route, $type)
    {

        // If we have a URL path: "// [shcemaless] or http:// or https://, assume not local and return what was passed in.
        if (strstr($route, '//')) {
            return $route;
        }
        $templatedOption = substr($route, 0, strlen($route) - (strlen($type)))  . '.' . $type;
        // account for misconfigured DOCUMENT_ROOT having trailing slash
        $alternateRoute = str_replace("//", "/", $_SERVER['DOCUMENT_ROOT'] . "/" . $templatedOption);
        // If there is a template-specific version of the file, use it instead
        if (file_exists($alternateRoute)) {
            return $templatedOption;
            // otherwise use what was sent
        } else {
            return $route;
        }

    }

    /**
     * @param $filter
     */
    protected function findForRoute($filter)
    {


        $path = sprintf("%s/%s/%s.%s", VIEWDIR, $this->registry->get('controller'), $this->registry->get('action'), $filter);
        if (file_exists($path)) {
            $file = str_replace("//", "/", str_replace(VIEWDIR, "views", $path));
            $this->$filter[] = $file;
        }
        $path = sprintf("%s/%s/%s.%s", VIEWDIR, $this->registry->get('controller'), 'common', $filter);
        if (file_exists($path)) {
            $file = str_replace("//", "/", str_replace(VIEWDIR, "views", $path));
            $this->$filter[] = $file;
        }

    }

    /**
     * @param $file
     */
    protected function addCss($file)
    {
        $this->css[] = $file;
    }

    /**
     * @param $file
     */
    protected function addJs($file)
    {
        $this->js[] = $file;
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
    /**
     * @param $file
     */
    protected function addFooterJs($route, $type = false, $data = false, $dependencies = false)
    {
        $route = $this->checkTemplatedFile($route, 'js');
        $file = $route;
        $this->footer_js[] = [$file, $type, $data, $dependencies];

    }

    /**
     *
     */
    public function json()
    {

        echo json_encode($this->_result);
    }

    /**
     *
     */
    protected function xls()
    {
        $fname = $this->registry->get('request', 'file');
        $path = "/tmp/$fname.xlsx";
        header('Content-type: application/vnd.ms-excel');
        header("Content-Disposition: attachment; filename=My411_Report.xlsx");
        header("Expires: 0");
        echo file_get_contents($path);
        exit;
    }

    /**
     *
     */
    protected function plainjson()
    {
        header('Content-type: text/json');
        echo json_encode($this->_result);
        exit;
    }

    protected function help()
    {
        $this->_result = array('status' => '1', 'message' => file_get_contents(__DIR__ . '/../views/api/help.phtml'));
        exit;
    }


    /**
     * @param $message
     */
    protected function notice($message)
    {
        $notice = $this->registry->get('notice') ? (array)$this->registry->get('notice') : array();
        $notice[] = $message;
        $this->registry->set('notice', $notice);
    }

    /**
     * @param $message
     */
    protected function error($message)
    {
        $notice = $this->registry->get('error') ? (array)$this->registry->get('error') : array();
        $notice[] = $message;
        $this->registry->set('error', $notice);
    }

    public function raw()
    {
        exit;
    }
    /**
     * xml()
     *
     * @brief for JSON output
     */
    public function xml()
    {

        echo $this->generateValidXmlFromArray($this->_result, 'result');
    }
    public function debug()
    {
        /**
         * debug()
         * @brief Allow a dump of any public available object data
         */
        echo json_encode(call_user_func('get_object_vars', $this));
    }
    /**
     * html()
     *
     * @brief for HTML output. this is the default
     */
    public function html()
    {

        $route = str_replace("/", SLASH, VIEWDIR . $this->_view . ".phtml");
        if (!file_exists($route)) {

            $route = str_replace("/", SLASH, VIEWDIR . "error/unknown_route.phtml");
            $this->_result['html'] = "<pre>" . print_r($this->_result, true) . "</pre>";
        }
        $this->_result['_userIsAdmin']= $this->registry->get('admin');
        $this->_result['_active_path']=sprintf("%s/%s",  $this->registry->get('controller'),$this->registry->get('action'));
        $base_path = VIEWDIR . "base/{$this->_template}.phtml";
        foreach ($this->_result as $key => $val) {
            $$key = $val;
        }
        require $base_path;
    }


    public function terminate(){



    }

    /**
     * buffer()
     *
     * @brief for buffered output.
     */
    public function buffer()
    {

        die();
    }
    /**
     * pixel()
     *
     * for a pixel render. We don't really use this anymore
     */
    public function pixel()
    {

        $this->_format = '_exit';
        header('Content-Type: image/png');
        echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII=');
        die();
    }


    protected function useVue($js = false)
    {
        if ($_SERVER['ENVIRONMENT'] == 'production') {
            $src = 'https://cdn.jsdelivr.net/npm/vue/dist/vue.js';
        } else {
            $src = 'https://cdn.jsdelivr.net/npm/vue';
        }
        $this->addFooterJs($src);
        if ($js != false) {
            $this->addFooterJsCode($js);
        }
    }

    /**
     * @param bool $addInit
     */
    protected function useTableSorter($addInit = false)
    {


        $this->addFooterJs('/js/jquery.tablesorter.js');
        $this->addFooterJs('/js/jquery.tablesorter.widgets.js');
        if ($addInit != false) {
            $this->addFooterJsCode($addInit);
        } else {
            $this->addFooterJs('/js/tableSorter.js');
        }

    }

}