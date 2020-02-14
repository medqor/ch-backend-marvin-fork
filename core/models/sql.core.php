<?php

use MongoDB\Driver\Exception\Exception;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;

error_reporting(E_ALL);
ini_set('display_errors', 1);

trait sql
{
    use shared;
    private $tables = [];
    private $useJoin = false;
    private $joinType = false;
    protected $params = [];
    private $groupBy = [];
    private $orderBy = [];
    private $crudType = false;
    private $queryInitialized = false;
    private $columnEscapeCharacter = false;
    private $columnEscapeCharacters = ['mysql' => '`'];
    protected $sql = false;
    private $execute = true;
    private $lastInsertId = false;
    protected $_connections = [];
    protected $_context =false;


    protected function __construct()
    {
        $this->_context = stream_context_create(array(
                "ssl" => array(
                    "cafile" => SSL_DIR . "/" . SSL_FILE,
                ))
        );

    }

    protected function DisableExecute()
    {
        $this->execute = false;
        return $this;
    }

    private function initializeQuery()
    {
        $this->connection = false;
        $this->columns = [];
        $this->columnNames = [];
        $this->execute = true;
        $this->tables = [];
        $this->columns = [];
        $this->params = [];
        $this->data = [];
        $this->insertRows = [];
        $this->useJoin = false;
        $this->joinType = false;
        $this->groupBy = [];
        $this->orderBy = [];
        $this->crudType = false;
        $this->queryInitialized = true;
        $this->columnEscapeCharacter = false;
        $this->aliases = range('a', 'z');
    }

//    private function setCrudType($type = false)
//    {
//        $type = trim(strtoupper($type));
//        switch ($type) {
//            case 'INSERT':
//            case 'SELECT':
//            case 'UPDATE';
//            case 'DELETE';
//                $this->crudType = $type;
//                break;
//            case 'VACUUM':
//            case 'ANALYZE':
//                break;
//            default:
//                throw new Exception(sprintf('SQL function %s is not recognized', $type ?? 'NULL'));
//        }
//
//    }

//    protected function DBQuery($conn)
//    {
//        if (!isset($this->$conn)) {
//            throw new Exception(sprintf('SQL connection %s is not recognized', $conn ?? 'NULL'));
//        }
//        $this->initializeQuery();
//        $this->connection = $this->$conn;
//        $this->columnEscapeCharacter = $this->columnEscapeCharacters[$this->config['databases'][$conn]['type']] ?? '`';
//        return $this;
//    }
//
//    protected function Select($tablesAndColumns = false)
//    {
//
//        if (is_array($tablesAndColumns)) {
//            if (count($tablesAndColumns) > count($this->aliases)) {
//                throw new Exception(sprintf('The number of tables (%s) exceeds our limit for aliases', count($tablesAndColumns)));
//            }
//            foreach ($tablesAndColumns as $tableName => $columns) {
//                $alias = $this->aliases[count($this->tables)];
//                $this->tables[$alias] = $tableName;
//
//                foreach ((array)$columns as $column) {
//                    $this->columns[] = sprintf("%s.%s%s%s", $alias, $this->columnEscapeCharacter, $column, $this->columnEscapeCharacter);
//                }
//
//            }
//        }
//        $this->setCrudType('SELECT');
//        return $this;
//
//    }
//
//    protected function Insert($table, $columns, $data)
//    {
//        $this->columns = array_values((array)$columns);
//        foreach ($this->columns as $idx => $column) {
//            $this->columns[$idx] = sprintf("%s%s%s", $this->columnEscapeCharacter, $column, $this->columnEscapeCharacter);
//            $this->columnNames[$idx] = $column;
//        }
//        $data = array_values($data);
//        if (is_array($data) && is_array($data[0])) {
//            $dataRows = [];
//            foreach ($data as $idx1 => $rows) {
//                if (!is_array($rows)) {
//                    throw new Exception(sprintf('We were expecting an array for inserts and did not get one'));
//                }
//                $insertRow = [];
//                foreach ($rows as $colOffset => $value) {
//                    $param = sprintf(':%s_%s', $this->columnNames[$colOffset], $idx1);
//                    $insertRow[] = $param;
//                    $this->params[$param] = $value === false ? NULL : (is_integer($value) ? (int)$value : $value);
//                }
//                $dataRows[] = sprintf("(%s)", implode(",", $insertRow));
//            }
//            $this->sql = sprintf("INSERT INTO %s%s%s (%s) VALUES %s;", $this->columnEscapeCharacter, $table, $this->columnEscapeCharacter, implode(",", $this->columns), implode(",", $dataRows));
//
//        } elseif (is_array($data) && !is_array($data[0])) {
//            foreach ($data as $colOffset => $value) {
//                $param = sprintf(':%s', $this->columnNames[$colOffset]);
//                $insertRow[] = $param;
//                $this->params[$param] = $value === false ? NULL : (is_integer($value) ? (int)$value : $value);
//
//            }
//            $this->sql = sprintf("INSERT INTO %s%s%s (%s) VALUES (%s);", $this->columnEscapeCharacter, $table, $this->columnEscapeCharacter, implode(",", $this->columns), implode(",", $insertRow));
//
//        } elseif (!is_array($columns) || count($columns) == 1) {
//
//        } else {
//
//        }
//
//        if ($this->execute === true) {
//            try {
//                $stmt = $this->connection->prepare($this->sql);
//                $stmt->execute($this->params);
//            } catch (Exception $e) {
//                throw new Exception($e->getMessage());
//            }
//
//            $this->lastInsertId = $this->connection->lastInsertId();
//        }
//        return $this;
//    }
//
//    protected function Exception($message)
//    {
//        throw new Exception($message);
//        exit;
//    }
//
//    protected function Interpolate($return = false)
//    {
//        $this->execute = false;
//        return $this;
//    }
//
//    protected function Update($tablesAndColumns)
//    {
//
//        $this->tables = $table;
//        $this->setCrudType('UPDATE');
//        return $this;
//
//    }
//
//
//    protected function Delete($table, $where = false, $compare = '=', $andOr = 'AND')
//    {
//
//        $compare = trim(strtoupper($compare));
//        $andOr = trim(strtoupper($andOr));
//        if (!in_array($andOr, ['AND', 'OR'])) {
//            throw new Exception(sprintf("Attempting to delete with an invalid join option%s.", $andOr));
//        }
//        if (!in_array($compare, ['=', '>=', '<=', 'IN'])) {
//            throw new Exception(sprintf("Attempting to delete with an invalid join option%s.", $andOr));
//        }
//
//        $this->tables = $table;
//        $this->setCrudType('DELETE');
//        $deleteWhere = [];
//        $deletRow = [];
//        if (!is_array($where)) {
//            throw new Exception(sprintf("Attempting to delete  data from %s without a valid WHERE array please pass a valid column=>matchingValue array", $table));
//        } else {
//
//
//            foreach ($where as $column => $value) {
//                if (is_array($value)) {
//                    $stack = [];
//                    $localCompare = ' = ';
//                    $andOr = 'OR';
//                    foreach ($value as $idx => $item) {
//                        $param = sprintf(':%s_%s', $column, $idx);
//                        $this->params[$param] = $item === false ? NULL : (is_integer($value) ? (int)$item : $item);
//                        $deletRow[] = sprintf("%s %s %s", $column, $localCompare, $param);
//                    }
//
//
//                } elseif (is_array($value)) {
//
//                    throw new Exception(sprintf("Imploding values not yet supported"));
//                    $param = sprintf(':%s', $column);
//                    $deletRow[] = sprintf("%s %s %s", $column, $localCompare, $param);
//                    $this->params[$param] = $value === false ? NULL : (is_integer($value) ? (int)$value : $value);
//                }
//
//
//            }
//
//
//            $this->sql = sprintf("DELETE FROM %s%s%s WHERE %s;",
//                $this->columnEscapeCharacter, $table, $this->columnEscapeCharacter,
//                implode(sprintf(" %s ", $andOr), $deletRow));
//        }
//        if ($this->execute === true) {
//            try {
//                $stmt = $this->connection->prepare($this->sql);
//                $stmt->execute($this->params);
//            } catch (Exception $e) {
//                throw new Exception($e->getMessage());
//            }
//
//            $this->lastInsertId = $this->connection->lastInsertId();
//        } else {
//            interpolate($this->sql, $this->params);
//        }
//        return $this;
//
//    }

    protected function Dump()
    {
        interpolate($this->sql, $this->params);
        return $this;
    }


    /*
     * OLD code below
     *
     */


    protected function _dbh()
    {


        foreach ($this->config['databases'] as $conn => $settings) {
            switch ($settings['type']) {
                case 'sqlite':
                    try {

                        $this->$conn = new \PDO("sqlite:" . $settings['host']);
                        $this->_connections[$conn] = true;
                    } catch (PDOException $e) {

                        $settings['pass'] = str_pad('', strlen($settings['pass']), '*');
                        $this->_result[$conn] = array('error' => 'Connection failed: ' . $e->getMessage(), 'settings' => $settings);
                        $this->$conn = false;
                        $this->_connections[$conn] = false;
                        print_r($e->getMessage());
                    }

                    break;
                default:
                    try {
                        $settings['connString'] = sprintf('%s:dbname=%s;host=%s;port=%s', $settings['type'], $settings['base'], $settings['host'], $settings['port']);
                        $this->$conn = new PDO($settings['connString'], $settings['user'], $settings['pass'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
                        $this->_connections[$conn] = true;
                    } catch (PDOException $e) {

                        $settings['pass'] = str_pad('', strlen($settings['pass']), '*');
                        $this->_result[$conn] = array('error' => 'Connection failed: ' . $e->getMessage(), 'settings' => $settings);
                        $this->$conn = false;
                        $this->_connections[$conn] = false;
                        print_r($e->getMessage());
                    }
                    break;
            }

        }
    }

    protected function read($conn, $sql, $params = array(), $fetch_style = 'fetchAll', $cursor_orientation = PDO::FETCH_ASSOC)
    {
        try {
            $stmt = $conn->prepare($sql);

            $stmt->execute($params);
            $data = $stmt->$fetch_style($cursor_orientation);

            $this->registry->set('sql_query_' . microtime(true), array('sql' => $sql, 'params' => $params));
            return $data;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    protected function write($conn, $sql, $params = array(), $fetch_style = 'fetch', $cursor_orientation = PDO::FETCH_ASSOC)
    {
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->$fetch_style($cursor_orientation);

            $this->registry->set('sql_query_' . microtime(true), array('sql' => $sql, 'params' => $params));
            return $data;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param $_db
     * @param string $sql
     * @param array $params
     * @param bool $interpolate
     * @param bool $cache
     * @param string $fetch_style
     * @param int $cursor_orientation
     * @return null|mixed|string|string[]
     */
    protected function query($_db, $sql = "SELECT NOW()", $params = [], $interpolate = false, $cache = false, $fetch_style = 'fetchAll', $cursor_orientation = PDO::FETCH_ASSOC)
    {
        $sql = trim($sql);
        if (!isset($this->$_db)) {
            $this->_dbh();
            if (!isset($this->$_db)) {
                header('location: /undefined/error?error=connection_does_not_exist[' . $_db . ']');
            }
        }


        if ($interpolate == true) {
            return interpolate($sql, $params, true);
        }



        $stmt = $this->$_db->prepare($sql);
        $stmt->execute($params);
        if (substr(strtoupper(trim($sql)), 0, 6) == 'INSERT') {
            if ($_db != '_redshift') {
                return @$this->$_db->lastInsertId();
            }
        }
        if (substr(strtoupper($sql), 0, 6) == 'SELECT') {

            $return = $stmt->$fetch_style($cursor_orientation);



            return $return;
        }
        if ($_db != '_redshift') {
            return @$this->$_db->lastInsertId();
        }


    }

//    protected function mongoQueryAggregate($collection,$match ){
//
//
//        db.etl_logs.aggregate([{$match: {etldate: {  }}},{ $unwind: "$etldate" },{$group: {_id: {$toLower: 'etldate'},count: { $sum: 1 }}},{$match: {count: { $gte: 1 }}},{ $sort : { count : -1} },{ $limit : 100 }]);
//
//
//    }
    protected function mongoDelete($collection,$filter){
        $client  =  (new MongoDB\Client(MONGODB,  array(
            'ssl' => true,
            'sslAllowInvalidCertificates' => true
        )))->base->$collection;
        try {
            $cursor = $client->deleteMany(
                $filter
            );
        } catch (MongoDB\Driver\Exception\Exception $e) {
            $filename = basename(__FILE__);
            echo "The $filename script has experienced an error.\n";
            echo "It failed with the following exception:\n";
            echo "Exception:", $e->getMessage(), "\n";
            echo "In file:", $e->getFile(), "\n";
            echo "On line:", $e->getLine(), "\n";
        }
    }
    protected function mongoQuery($collection, $filter, $options =[] ,$projection = false){
        if($projection!=false){
            if(!isset($projection['_id'])){
                $projection['_id']=0;
            }
            $options['projection']=$projection;
        }

        $client  =  (new MongoDB\Client(MONGODB,  array(
            'ssl' => true,
            'sslAllowInvalidCertificates' => true
        )))->base->$collection;
        try {
            $cursor = $client->find(
                $filter, $options
            );
        } catch (MongoDB\Driver\Exception\Exception $e) {
            $filename = basename(__FILE__);
            echo "The $filename script has experienced an error.\n";
            echo "It failed with the following exception:\n";
            echo "Exception:", $e->getMessage(), "\n";
            echo "In file:", $e->getFile(), "\n";
            echo "On line:", $e->getLine(), "\n";
        }
        $result=[];

        foreach ($cursor as $document) {
            $result[]=json_decode(json_encode($document),true);
        };
        if(isset($options['limit'])&& $options['limit']==1 && count($result)){
            return $result[0];
        }
        return $result;
    }

    protected function mongoInsert($collection,$document){
        $client  =  (new MongoDB\Client(MONGODB,  array(
            'ssl' => true,
            'sslAllowInvalidCertificates' => true
        )))->base->$collection;
        try {
            $cursor = $client->insertOne(
                 $document
            );
        } catch (MongoDB\Driver\Exception\Exception $e) {
            $filename = basename(__FILE__);
            echo "The $filename script has experienced an error.\n";
            echo "It failed with the following exception:\n";
            echo "Exception:", $e->getMessage(), "\n";
            echo "In file:", $e->getFile(), "\n";
            echo "On line:", $e->getLine(), "\n";
        }
        file_put_contents('/var/log/pheanstalk.log', pretty_print_r($document,true) . "\r\n");

        return $cursor->getInsertedCount();
    }
    protected function mongoUpdateMany($collection, $filter,$document){
        $client  =  (new MongoDB\Client(MONGODB,  array(
            'ssl' => true,
            'sslAllowInvalidCertificates' => true
        )))->base->$collection;
        try {
            $cursor = $client->updateMany(
                $filter, $document
            );
        } catch (MongoDB\Driver\Exception\Exception $e) {
            $filename = basename(__FILE__);
            echo "The $filename script has experienced an error.\n";
            echo "It failed with the following exception:\n";
            echo "Exception:", $e->getMessage(), "\n";
            echo "In file:", $e->getFile(), "\n";
            echo "On line:", $e->getLine(), "\n";
        }

        return $cursor->getModifiedCount();
    }
    protected function mongoUpdatOne($collection, $filter,$document){
        $client  =  (new MongoDB\Client(MONGODB,  array(
            'ssl' => true,
            'sslAllowInvalidCertificates' => true
        )))->base->$collection;
        try {
            $cursor = $client->updateOne(
                $filter, $document
            );
        } catch (MongoDB\Driver\Exception\Exception $e) {
            $filename = basename(__FILE__);
            echo "The $filename script has experienced an error.\n";
            echo "It failed with the following exception:\n";
            echo "Exception:", $e->getMessage(), "\n";
            echo "In file:", $e->getFile(), "\n";
            echo "On line:", $e->getLine(), "\n";
        }

        return $cursor->getModifiedCount();
    }

    public function cacheWrite($collection, $object)
    {


        $ctx = stream_context_create(array(
                "ssl" => array(
                    "cafile" => SSL_DIR . "/" . SSL_FILE,
                ))
        );
        $collection = sprintf("db.%s", $collection);
        $manager = new MongoDB\Driver\Manager(MONGODB, array("ssl" => true), array("context" => $ctx));


        if (!isset($object['uuid'])) {
            $object['uuid'] = $this->uuidSecure();

        }
        // pretty_print_r($object);
        $this->_result['uuid'][] = ['uuid' => $object['uuid'], 'npi' => $object['npi']];
        $filter = ['uuid' => $object['uuid']];
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->insert($object);
        $manager->executeBulkWrite($collection, $bulk);

//        $query = new MongoDB\Driver\Query($filter);
//        $cursor = $manager->executeQuery($collection, $query);
//        //  pretty_print_r($cursor);
//        foreach ($cursor as $document) {
//            echo pretty_print_r($document, true);
//        }


        //     $bulk = new MongoDB\Driver\BulkWrite;

//Create a MongoDB client and open connection to Amazon DocumentDB
        $manager = new MongoDB\Driver\Manager(MONGODB, array("ssl" => true), array("context" => $ctx));
        //  $client = new MongoDB\Driver\Manager(MONGODB);
//    $bulk = new MongoDB\Driver\BulkWrite;
//        $bulk->insert(['x' => 1]);
//        $bulk->insert(['x' => 2]);
//        $bulk->insert(['x' => 3]);
//        $manager->executeBulkWrite('db.collection', $bulk);
    }
}
