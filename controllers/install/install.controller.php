<?php
set_time_limit(0);

class install extends service
{
    public $report;
    public $errors = array();

    public function __construct()
    {
        $this->allowed_actions = array('view', 'ajax', 'help');

        $this->_preprocess();

        $this->_format = 'json';
    }

    protected function ajax()
    {
        $this->_format = 'plainjson';
        $config=['defines'=>$this->registry->get('request','defines'),'databases'=>$this->registry->get('request','databases')];
        $config=[];
        $config[]='[defines]';
        foreach($this->registry->get('request','defines') as $key=>$value){
            $config[]=sprintf("%s = %s",$key,$value);
        }
        $config[]='';
        $config[]='[databases]';
        foreach($this->registry->get('request','databases') as $db=>$settings){
            foreach($settings as $setting=>$value){
                $config[]=sprintf("%s[%s] = %s",$db,$setting,$value);
            }
        }
        $request=$this->registry->get('request');
        $config=implode("\r\n",$config);
        file_put_contents('./../config/'.ENVIRONMENT.'.ini',$config);
        $this->_result=['status'=>true,'message'=>'New conf written'];

        if($this->registry->get('request','addUsersTable') =='on'){
            $this->config['databases']=$this->registry->get('request','databases');
            $this->_dbh();
            $sql=file_get_contents('./../sql/user.sql');
            $this->write($this->_db,$sql);
            $sql="INSERT INTO user (username, email, firstname, lastname, type, status,password) VALUES(:username,:email,:firstname,:lastname,'A', 1,:password)";
            $params =[
                ':username'=>$request['user']['username'],
                ':firstname'=>$request['user']['firstname'],
                ':lastname'=>$request['user']['lastname'],
                ':email'=>$request['user']['email'],
                ':password'=>crypt($request['user']['email'],$request['defines']['HASH'].$request['user']['email'])
            ];
            pretty_print_r( $this->write($this->_db,$sql,$params));

            $this->_result['useer_added']=true;

        }
        rename('./../sql/user.sql','./../sql/user_table_added.sql');





    }


    protected function view()
    {


        $this->_format='json';
        exit;


        $alpha=(str_split("THEQUICKBROWNFOXJUMPEDOVERTHELAZYDOGthequickbrownfoxjumpedoverthelazydog1234567890!@#$%^&*"));
        shuffle($alpha);
        $this->_result['randomPass']=substr(implode("",$alpha),0,8);

        $this->_result['usersTableExists']=$this->checkUsersTable();
        $this->_result['config'] = $this->config;
        $this->_view = 'install/view';
        $this->_format = 'html';

        exit;
    }


}
