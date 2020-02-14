<?php

use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use MongoDB\Driver\Manager;
use TheCodingMachine\GraphQLite\SchemaFactory;
use TheCodingMachine\GraphQLite\Context\Context;

use Atanvarno\Dependency\Container;
use function Atanvarno\Dependency\{entry, factory, object, value};


set_time_limit(10);

/**
 * @Type()
 */
class test extends service
{

    public $errors = array();
    public $is_api = true;

    public function __construct()
    {


        $this->allowed_actions = array('test','view', 'ajax');

        $this->_preprocess();
        $this->_format = 'json';
    }

    protected function test(){
        $this->etldate = $etlCutoff = date('Y-m-d H:i:s');
        $this->tube ="job_".microtime(true);
//        echo phpinfo();
//        exit;
        live_tube('hubspot_etl/hubspot',$this->tube );



    }
    protected function ajax()
    {
        $this->_format = 'plainjson';
        $this->_result = array();


    }

    /**
     * @param null|string $message
     * @return string
     * @Query
     */
    protected function echo(?string $message): string
    {
        return $message;
    }

    protected function view()
    {
        /**
         * db.nppes.aggregate(
         * {"$group" : { "_id": "$npi", "count": { "$sum": 1 } } },
         * {"$match": {"_id" :{ "$ne" : null } , "count" : {"$gt": 1} } },
         * {"$sort": {"count" : -1} },
         * {"$project": {"npi" : "$_id", "_id" : 0} }
         * )
         *
         *
         *
         *
         * ['$group' : [ '_id': '$npi', 'count': [ '$sum': 1 ] ] ],
         * {"$match": {"_id" :{ "$ne" : null } , "count" : {"$gt": 1} } },
         * {"$sort": {"count" : -1} },
         * {"$project": {"npi" : "$_id", "_id" : 0} }
         * )
         */




    }


}
