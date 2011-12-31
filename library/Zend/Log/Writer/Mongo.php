<?php

require_once ('Zend/Log/Writer/Abstract.php');

class Zend_Log_Writer_Mongo extends Zend_Log_Writer_Abstract
{
    /**
     * 
     * 
     * @var MongoCollection
     */
    protected $_db;
    /**
     * 
     * 
     * @var array
     */
    protected $_documentMap;
    /**
     * 
     * 
     * @param MongoCollection $db
     * @param array $documentMap
     */
    public function __construct(MongoCollection $db, $documentMap = null)
    {
        $this->_db = $db;
        $this->_documentMap = $documentMap;
    }
    /**
     * (non-PHPdoc)
     * @see Zend_Log_Writer_Abstract::_write()
     */
    protected function _write ($event)
    {
        if ($this->_db === null) {
            require_once 'Zend/Log/Exception.php';
            throw new Zend_Log_Exception('MongoDb object is null');
        }
        $event['timestamp'] = new MongoDate(strtotime($event['timestamp']));
        if ($this->_documentMap === null) {
            $dataToInsert = $event;
        } else {
            $dataToInsert = array();
            foreach ($this->_documentMap as $columnName => $fieldKey) {
                $dataToInsert[$columnName] = $event[$fieldKey];
            }
        }
        $this->_db->save($dataToInsert);
    }
    /**
     * 
     * 
     * @param array $config
     */
    static public function factory ($config)
    {
        $config = self::_parseConfig($config);
        $config = array_merge(array(
            'db'          => null,
            'collection'  => null,
            'documentMap' => null,
        ), $config);

        if (isset($config['documentmap'])) {
            $config['documentMap'] = $config['documentmap'];
        }
        if(!$config['db'] instanceof Mongo){
            $config['db'] = self::_createMongo($config['db']);
        }
        return new self(
            $config['db'],
            $config['documentMap']
        );
    }
    /**
     * 
     * 
     * @param array $config
     */
    static protected function _createMongo($config)
    {
        if(!isset($config['server'])){
            $server  = "mongodb://localhost:27017";
        } else {
            $server = $config['server'];
        }
        if(!isset($config['options']) || !is_array($config['db']['options'])){
            $options = array();
        } else {
            $options = $config['options'];
        }
        $mongo = new Mongo($server, $options);
        $database = $mongo->selectDB($config['database']);
        return $database->selectCollection($config['collection']);
    }
}