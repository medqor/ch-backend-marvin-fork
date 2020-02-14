<?php
set_time_limit(10);

class admin extends service
{

    public $errors = array();
    public $is_api = true;



    public function __construct()
    {


        $this->allowed_actions = array('view', 'ajax');

        $this->_preprocess();
        $this->_format = 'json';
    }

    protected function ajax()
    {
        $this->_format = 'plainjson';
        $this->_result = array();


    }


    protected function view()
    {
        $this->setView('admin/home');

    }


}
