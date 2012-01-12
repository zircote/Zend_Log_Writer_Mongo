# Zend_Log_Writer_Mongo

### Uses
#### `Zend_Log::factory`
```php
<?php
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
```
#### `Zend_Log_Writer_Mongo::factory` with `Mongo` options
```php
<?php

    $config = array('server' => 'mongodb://somehost.mongolab.com:27017', 
    'collection' => 'logging', 'database' => 'zend_log', 
    'options' => array('username' => 'zircote-dev', 
    'password' => 'somepassword', 'connect' => true, 'timeout' => 200, 
    'replicaSet' => 'repset1', 'db' => 'zend_log'));
    $log = new Zend_log();
    $log->addWriter(Zend_Log_Writer_Mongo::factory($config));
    $log->info('this is a test ' . __METHOD__);
```
#### Zend_Log_Writer_Mongo::factory 
```php
<?php
    $config = array(
    'collection' => 'log',
    'database' => 'pincrowd'
    );
    $writer = Zend_Log_Writer_Mongo::factory($config);
    $log = new Zend_log();
    $log->addWriter($writer);
    $log->info('this is a test');
```
#### Zend_Log_Writer_Mongo::__construct
```php
<?php
    $mongo = new Mongo();
    $collection = $mongo->selectDB('logging')
        ->selectCollection('logCollection');
    $log = new Zend_log();
    $writer = new Zend_Log_Writer_Mongo($collection);
    $log->addWriter($writer);
    $log->err(__METHOD__);
```
#### Resulting Document
```javascript
{
    "_id": {
        "$oid": "4effb7b46be1326d37000000"
    },
    "timestamp": {
        "$date": "2012-01-01T01:32:36.000Z"
    },
    "message": "this is a test MongoLogTest::testWrite2",
    "priority": 6,
    "priorityName": "INFO"
}
```