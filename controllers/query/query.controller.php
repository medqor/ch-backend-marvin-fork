<?php

use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use MongoDB\Driver\Manager;
use TheCodingMachine\GraphQLite\SchemaFactory;
use TheCodingMachine\GraphQLite\Context\Context;


use Atanvarno\Dependency\Container;
use function Atanvarno\Dependency\{entry, factory, object, value};

error_reporting(E_ALL);
ini_set('display_errors', 1);

set_time_limit(0);


/**
 * Create indexes:
 * db.providers.createIndex( { "**" : 1 } )
 *
 *
 * find caseless:
 * db.providers.find({practice_state: { "$regex" : "tx" , "$options" : "i"}})
 *
 * Bulk Import:
 *  mongoimport --ssl --host chroniclehealth-primary.cluster-cdqcr83pkeam.us-west-2.docdb.amazonaws.com:27017 --sslCAFile rds-combined-ca-bundle.pem --username root --password SkJxY9ubJTNF --db=base --type=csv --mode=insert --headerline   --numInsertionWorkers=8 --file='./providers.csv'
 *
 *
 * find duplicates:
 *
 * db.nppes.aggregate(
{"$group" : { "_id": "$npi", "count": { "$sum": 1 } } },
{"$match": {"_id" :{ "$ne" : null } , "count" : {"$gt": 1} } },
{"$sort": {"count" : -1} },
{"$project": {"npi" : "$_id", "_id" : 0} }
)
 *
 */

/**
 * @Type()
 */
class query extends service
{

    public $errors = array();
    public $is_api = true;

    public function __construct()
    {


        $this->allowed_actions = array('duplicates','import', 'view', 'ajax');

        $this->_preprocess();
        $this->_format = 'json';
    }
    protected function duplicates(){

    }

    protected function ajax()
    {
        $this->_format = 'plainjson';
        $this->_result = array();


    }




    protected function view()
    {
//pretty_print_r($this->registry->get('request'));

        $collection= $this->registry->get('request','collection');
        $filter= $this->registry->get('request','filter');
        $options= $this->registry->get('request','options');
        $projection= $this->registry->get('request','projection');
        $this->_result=['data'=>$this->mongoQuery($collection,$filter,$options,$projection),'collection'=>$collection,'filter'=>$filter,'options'=>$options,'projection'=>$projection];

    }


}
