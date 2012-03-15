# Zend_Log_Writer_MongoDb

### Uses
```
resources.log.mongo.writerName = "MongoDb"
resources.log.mongo.writerParams.database = "pincrowd"
resources.log.mongo.writerParams.collection = "logging"
resources.log.mongo.writerParams.documentMap.timestamp = 'timestamp'
resources.log.mongo.writerParams.documentMap.message = 'message'
resources.log.mongo.writerParams.documentMap.priority = 'priority'
resources.log.mongo.writerParams.documentMap.priorityName = 'priorityName'
resources.log.mongo.writerParams.documentMap.hostname = 'hostname'
resources.log.mongo.filterName = "Priority"
resources.log.mongo.filterParams.priority = 5

```

#### `Zend_Log::factory`
```php
<?php
    $logger = Zend_Log::factory(
    array('timestampFormat' => 'Y-m-d', 
    array('writerName' => 'MongoDb', 
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
#### `Zend_Log_Writer_MongoDb::factory` with `MongoDb` options
```php
<?php

    $config = array('server' => 'mongodb://somehost.mongolab.com:27017', 
    'collection' => 'logging', 'database' => 'zend_log', 
    'options' => array('username' => 'zircote-dev', 
    'password' => 'somepassword', 'connect' => true, 'timeout' => 200, 
    'replicaSet' => 'repset1', 'db' => 'zend_log'));
    $log = new Zend_log();
    $log->addWriter(Zend_Log_Writer_MongoDb::factory($config));
    $log->info('this is a test ' . __METHOD__);
```
#### Zend_Log_Writer_MongoDb::factory 
```php
<?php
    $config = array(
    'collection' => 'log',
    'database' => 'pincrowd'
    );
    $writer = Zend_Log_Writer_MongoDb::factory($config);
    $log = new Zend_log();
    $log->addWriter($writer);
    $log->info('this is a test');
```
#### Zend_Log_Writer_MongoDb::__construct
```php
<?php
    $mongo = new MongoDb();
    $collection = $mongo->selectDB('logging')
        ->selectCollection('logCollection');
    $log = new Zend_log();
    $writer = new Zend_Log_Writer_MongoDb($collection);
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
    "message": "this is a test MongoDbLogTest::testWrite2",
    "priority": 6,
    "priorityName": "INFO"
}
```

#### Reading from the log with a tailable cursor:

```php
<?php

// db.createCollection("logCollection", {capped:true, size:100000})

$mongo = new Mongo();
$db = $mongo->selectDB('logging');
$collection = $db->selectCollection('logCollection');
$cursor = $collection->find()->tailable(true);
while (true) {
    if ($cursor->hasNext()) {
        $doc = $cursor->getNext();
        echo date(DATE_ISO8601, $doc['timestamp']->sec), ' ',$doc['priorityName'],' ', $doc['message'], PHP_EOL;
    } else {
        usleep(100);
    }
}
```