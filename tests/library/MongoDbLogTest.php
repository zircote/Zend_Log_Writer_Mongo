<?php
class MongoDbLogTest extends PHPUnit_Framework_TestCase
{
    public function testWrite()
    {
        $config = array(
        'collection' => 'log',
        'database' => 'zend_log'
        );
        $writer = Zend_Log_Writer_MongoDb::factory($config);
        $log = new Zend_log();
        $log->addWriter($writer);
        $log->info('this is a test');
    }
    public function testWrite2 ()
    {
        $config = array('server' => 'mongodb://somehost.mongolab.com:27017', 
        'collection' => 'logging', 'database' => 'zend_log', 
        'options' => array('username' => 'zircote-dev', 
        'password' => 'somepassword', 'connect' => true, 'timeout' => 200, 
        'replicaSet' => 'repset1', 'db' => 'zend_log'));
        $log = new Zend_log();
        $log->addWriter(Zend_Log_Writer_MongoDb::factory($config));
        $log->info('this is a test ' . __METHOD__);
    }
    public function testZendLog ()
    {
        $logger = Zend_Log::factory(
        array('timestampFormat' => 'Y-m-d', 
        array('writerName' => 'Mongo', 
        'writerParams' => array(
            'server' => 'mongodb://somehost.mongolab.com:27017', 
            'collection' => 'logging', 'database' => 'zend_log', 
            'options' => array('username' => 'zircote-dev', 
            'password' => 'somepassword', 'connect' => true, 'timeout' => 200, 
            'replicaSet' => 'repset1', 'db' => 'zend_log')
        ), 
        'formatterName' => 'Simple', 
        'formatterParams' => array(
            'format' => '%timestamp%: %message% -- %info%'), 
            'filterName' => 'Priority', 
            'filterParams' => array('priority' => Zend_Log::WARN)), 
        array('writerName' => 'Firebug', 'filterName' => 'Priority', 
            'filterParams' => array('priority' => Zend_Log::INFO)))
        );
        $logger->crit(__METHOD__);
    }
    public function testMongoLog3()
    {
        $mongo = new Mongo();
        $collection = $mongo->selectDB('logging')
            ->selectCollection('logCollection');
        $log = new Zend_log();
        $writer = new Zend_Log_Writer_MongoDb($collection);
        $log->addWriter($writer);
        $log->err(__METHOD__);
    }
}