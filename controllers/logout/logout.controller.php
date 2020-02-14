<?php
set_time_limit(0);

class logout extends service
{
    public $report;
    public $errors = array();
    public $navigation = array(
        'view' => 'Home',
    );

    public function __construct()
    {
        $this->allowed_actions = array('view');
        $this->_preprocess();
        $this->registry = Registry::getInstance();

        $this->registry->addValue('system_error', 'Hi');
        $this->_request = array_merge($_GET, $_POST);

        $this->_format = 'json';
    }

    protected function help()
    {
        $this->_format = 'html';
        $this->_view = 'home/help';
        $this->_result = array('status' => 'success', 'message' => file_get_contents(__DIR__ . '/../views/check/help.phtml'));
        exit;
    }

    protected function view()
    {
        $this->UnsetSession();
        unset($this->navigation['logout']);

        $this->navigation['login'] = 'Log In';

        $this->_view = 'login/login';
        $this->_format = 'html';

        exit;
    }
}
