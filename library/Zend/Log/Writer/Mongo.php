<?php

require_once ('Zend/Log/Writer/Abstract.php');
/**
 * 
 * 
 * @author zircote
 *
 */
class Zend_Log_Writer_Mongo extends Zend_Log_Writer_Abstract
{
    /**
     * 
     * 
     * @var MongoCollection
     */
    protected $_collection;
    /**
     * 
     * 
     * @var array
     */
    protected $_documentMap;
    /**
     * 
     * 
     * @param MongoCollection $collection
     * @param array $documentMap
     */
    public function __construct(MongoCollection $collection, $documentMap = null)
    {
        $this->_collection = $collection;
        $this->_documentMap = $documentMap;
    }
    /**
     * (non-PHPdoc)
     * @see Zend_Log_Writer_Abstract::_write()
     */
    protected function _write ($event)
    {
        if ($this->_collection === null) {
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
        $this->_collection->save($dataToInsert);
    }
    /**
     * 
     * 
     * @param array $config
     */
    static public function factory ($config)
    {
        $config = self::_parseConfig($config);
        $config = array_merge(
            array('collection'  => null,'documentMap' => null), 
            $config
        );
        if (isset($config['documentmap'])) {
            $config['documentMap'] = $config['documentmap'];
        }
        if(!$config['collection'] instanceof MongoCollection){
            $config['collection'] = self::_createMongoCollection($config);
        }
        return new self(
            $config['collection'],
            $config['documentMap']
        );
    }
    /**
     * 
     * 
     * @param array $config
     * @return MongoCollection
     */
    static protected function _createMongoCollection($config)
    {
        if(!isset($config['server'])){
            $server  = "mongodb://localhost:27017";
        } else {
            $server = $config['server'];
        }
        if(!isset($config['options']) || !is_array($config['options'])){
            $options = array();
        } else {
            $options = $config['options'];
        }
        $mongo = new Mongo($server, $options);
        return $mongo->selectDB($config['database'])
            ->selectCollection($config['collection']);
    }
}