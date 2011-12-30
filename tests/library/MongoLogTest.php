<?php

class MongoLogTest extends PHPUnit_Framework_TestCase
{
    public function testWrite()
    {
        $config = array( 'db' => array(
            'collection' => 'log',
            'database' => 'pincrowd'
        ));
        $writer = Zend_Log_Writer_Mongo::factory($config);
        $log = new Zend_log();
        $log->addWriter($writer);
        $log->info('this is a test');
    }
}