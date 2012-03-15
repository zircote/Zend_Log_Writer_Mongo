# Zend_Log_Writer_MongoDb

### Uses

```php
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

<?php
if($bootstrap->hasResource('log')){
    $log = $bootstrap->getResource('log');
    $log->info('log me');
}
```

### Full config via `Zend_Log::factory`

```php
<?php
$logger = Zend_Log::factory(
    array(
        'timestampFormat' => 'Y-m-d',
        array(
            'writerName' => 'MongoDb',
            'writerParams' => array(
                'server' => 'mongodb://somehost.mongolab.com:27017',
                'collection' => 'logging',
                'database' => 'zend_log',
                'options' => array(
                    'username' => 'zircote-dev',
                    'password' => 'somepassword',
                    'connect' => true,
                    'timeout' => 200,
                    'replicaSet' => 'repset1',
                    'db' => 'zend_log'
                )
            )
        )
    )
);
$logger->crit(__METHOD__);
```

### Extended config via `Zend_Log_Writer_MongoDb::factory`

```php
<?php
$config = array(
    'server' => 'mongodb://somehost.mongolab.com:27017',
    'collection' => 'logging',
    'database' => 'zend_log',
    'options' => array(
        'username' => 'zircote-dev',
        'password' => 'somepassword',
        'connect' => true,
        'timeout' => 200,
        'replicaSet' => 'repset1',
        'db' => 'zend_log')
);
$log = new Zend_log();
$log->addWriter(Zend_Log_Writer_MongoDb::factory($config));
$log->info('this is a test ' . __METHOD__);
```

### Using the Writer Factory Method:

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

### Using a MongoCollection Object

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

### An Example Logged Document

```javascript

{
    "_id" : ObjectId("4f5fa1546be132029900009e"),
    "timestamp" : ISODate("2012-03-13T19:34:44Z"),
    "message" : "this is a test 27",
    "priority" : 3,
    "priorityName" : "ERR",
    "hostname" : "zircote-mbp-4.local"
}


```

### A Tailing cursor

```php
<?php
$mongo = new Mongo();
$db = $mongo->selectDB('logging');
$collection = $db->selectCollection('logCollection');
$cursor = $collection->find()->tailable(true);
while (true) {
    if ($cursor->hasNext()) {
        $doc = $cursor->getNext();
        echo date(DATE_ISO8601, $doc['timestamp']->sec), ' ',$doc['priorityName'],' ', $doc['message'], PHP_EOL;
    } else {
        usleep(500);
    }
}

```