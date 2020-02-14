<?php

use MongoDB\Driver\Exception\Exception;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;

set_time_limit(0);

class home extends service
{
    public $report;
    public $errors = array();

    public function __construct()
    {
        $this->allowed_actions = array( 'get',);

        $this->_preprocess();
        $this->_format = 'json';
        //$this->DBQuery('_db')->Delete('change',['denomination'=>['t2','t1']]);

       // exit;



    }

    protected function get(){

        $filter=

        [
            'npi' => ['$lt'=>'1003000196'],'type'=>'person'
        ];
        $options = [
                    'limit' => 5

                ];
        $projection =  [
            'npi' => 1,
            'first_name' => 1,
            'last_name' => 1,
        ];

        pretty_print_r($this->mongoQuery('nppes',$filter,$options,$projection));

exit;

        $client  =  (new MongoDB\Client(MONGODB,  array(
            'ssl' => true,
            'sslAllowInvalidCertificates' => true
        )))->base->nppes;






        try {
       //collection = $manager->base->nppes;
        $cursor = $client->find(
            [
                'npi' => ['$lt'=>'1003000196'],'type'=>'person'
            ],
            [
                'limit' => 5,
                'projection' => [
                    'npi' => 1,
                    'first_name' => 1,
                    'last_name' => 1,
                ],
            ]
        );
        } catch (MongoDB\Driver\Exception\Exception $e) {

            $filename = basename(__FILE__);

            echo "The $filename script has experienced an error.\n";
            echo "It failed with the following exception:\n";

            echo "Exception:", $e->getMessage(), "\n";
            echo "In file:", $e->getFile(), "\n";
            echo "On line:", $e->getLine(), "\n";
        }

        foreach ($cursor as $restaurant) {
            echo pretty_print_r($restaurant,true);
        };
    }



}
