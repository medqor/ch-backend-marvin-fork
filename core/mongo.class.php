<?php


use MongoDB;


//
//try {
//
//    print_r($config['mongo']);
////    $m = new MongoDB\Client("mongodb://localhost:27017");
////
////
////$db = $m->api;
////$GLOBALS['mongodb']=$db;
//// Manager Class
//    $manager = new MongoDB\Driver\Manager("mongodb://root:SkJxY9ubJTNF@chroniclehealth-primary.cluster-cdqcr83pkeam.us-west-2.docdb.amazonaws.com:27017/?ssl=true&ssl_ca_certs=rds-combined-ca-bundle.pem&replicaSet=rs0&readPreference=secondaryPreferred&retryWrites=false");
//
//// Query Class
//    $query = new MongoDB\Driver\Query(array('age' => 30));
//
//// Output of the executeQuery will be object of MongoDB\Driver\Cursor class
//    $cursor = $manager->executeQuery('testDb.testColl', $query);
//
//// Convert cursor to Array and print result
//    print_r($cursor->toArray());
//
//
//}catch(Exception $e){
//    echo $e->getMessage();
//}
