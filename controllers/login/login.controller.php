<?php

set_time_limit(0);

class login extends service
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
        $this->_format = 'html';
        $this->UnsetSession();

        $this->_view = 'login/login';
        $this->_result['username'] = $this->_request['username'] ?? '';

        if (isset($this->_request['username'])) {
            if (!isset($this->_request['password']) || !trim($this->_request['password'])) {
                $this->error('Please enter your password');
                exit;
            }
            $sql = "SELECT u.*, :password as sanitized
                    from user u
                    where trim(lower(email))=trim(lower(:user)) OR trim(lower(username))=trim(lower(:user))  ";
            $record = $this->read($this->_read, $sql, [':user' => $this->_request['username'], ':password' => $this->_request['password']], 'fetch');

            if ($record['status'] == 1 && check_pcrypt($this->_request['password'], $record['password']) == true) {

                $this->CreateSession($record);
                if (isset($_SESSION['redirect_on_login'])) {
                    $path = $_SESSION['redirect_on_login'];
                    unset($_SESSION['redirect_on_login']);
                    header('location: ' . $path);
                } else {
                    header('location: /home');
                }


            } else {


                $errorFound = false;
                if ($errorFound == false && !$record) {
                    $this->error('Sorry, we could not find an account for that email.');
                    $errorFound = true;
                }
                if ($record['status'] != '1' && $errorFound == false) {
                    $this->error('Sorry, your account is currently disabled. Please contact your admin.');
                    $errorFound = true;
                }
                if ($errorFound == false && check_pcrypt($this->_request['password'], $record['password']) == false) {
                    $this->error('Sorry, the password entered does not match the one on file. Please contact your admin.');
                    $errorFound = true;
                }
                if ($errorFound == false) {
                    $this->error('Sorry, we encountered an error. Please try again or contact your admin.');
                    $errorFound = true;
                }
            }
        }
        exit;
    }
}
