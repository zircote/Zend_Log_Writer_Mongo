<?php
/**
 *
 * @category   Ifbyphone
 * @package    Zend_Tool
 * @subpackage Framework
 */
/**
 * @see Zend_Tool_Project_Provider_AbstractProvider
 */
require_once 'Zend/Tool/Project/Provider/Abstract.php';
/**
 *
 *
 * @category   Ifbyphone
 * @package    Zend_Tool
 * @subpackage Framework
 */
class Zend_Tool_Project_Provider_MongoLog extends Zend_Tool_Project_Provider_Abstract
{
    /**
     *
     * @var array
     */
    protected $_config;
    /**
     *
     * @var array
     */
    protected $_mongoParams;
    /**
     *
     * @var MongoCollection
     */
    protected $_mongoCollection;
    /**
     * @see Zend_Tool_Project_Provider_AbstractProvider::initialize()
     */
    public function initialize()
    {
        if (!extension_loaded('Mongo')) {
            Zend_Cache::throwException("Cannot use Mongo storage because the ".
            "'Mongo' extension is not loaded in the current PHP environment");
        }
        parent::initialize();
    }

    /**
     * Loads the config into the self::_config property.
     * @return Zend_Tool_Project_Provider_MongoLog
     * @throws Zend_Tool_Project_Exception
     */
    protected function _loadConfig($env)
    {
        $profile = $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);
        $appConfigFileResource = $profile->search('applicationConfigFile');
        $appConfigFilePath = $appConfigFileResource->getPath();
        $this->_setConfig(
            new Zend_Config_Ini($appConfigFilePath, $env)
        );
        $this->_setMongo();
        return $this;
    }
    /**
     *
     * @param array|Zend_Config $config
     * @return Zend_Tool_Project_Provider_MongoLog
     */
    protected function _setConfig($config)
    {
        if($config instanceof Zend_Config){
            $config = $config->toArray();
        }
        $this->_config = $config;
        foreach ($this->_config['resources']['log'] as $log) {
            if(isset($log['writerName']) && strstr($log['writerName'], 'MongoDb')){
                $this->_mongoParams = $log['writerParams'];
                $this->_documentMap = $log['writerParams']['documentMap'];
                return $this;
            }
        }
        throw new Zend_Tool_Project_Provider_Exception(
            'Unable to locate a Zend_Log_Writer_MongoDb configuration'
        );
    }
    /**
     * <b>Example Usage:</b>
     *
     * <code>
     * zf tail log -h 'some-host.cloud.com' -p 7 -f 'somebad thing happened' -e production
     * </code>
     *
     * @param string $filter text to filter the logs by
     * @param string $hostname a comma seperated lists of servers to return logs for
     * @param int    $priority The log priority to filter the tail on
     * @param string $env project environment by which the configs are loaded
     */
    public function tail($filter = null, $hostname = null, $priority = null, $env = 'dev')
    {
        $this->_loadConfig($env);
        $_f = array();
        if($filter){
            $_f['message'] = array('$regex' => sprintf('%s', $filter));
        }
        if((int)$priority > 0 && (int)$priority < 8){
            $_f['priority'] = array( '$lte' => (int) $priority);
        }
        if($hostname){
            $hosts = explode(',', $hostname);
            if(count($hosts) > 1){
                foreach ($hosts as $value) {
                    $_f['$or'][] = array('hostname' => $value);
                }
            } else {
                $_f['hostname'] = $hosts[0];
            }
        }
        $cursor = $this->_mongoCollection->find($_f)->tailable(true);
        while (true) {
            if ($cursor->hasNext()) {
                $doc = $cursor->getNext();
                $this->_registry->getResponse()
                    ->appendContent(
                        sprintf(
                            '[%s] [ %s] [%s]: %s',
                            $doc[$this->_documentMap['hostname']],
                            date(DATE_ISO8601, $doc[$this->_documentMap['timestamp']]->sec),
                            $doc[$this->_documentMap['priorityName']],
                            $doc[$this->_documentMap['message']]
                        )
                    );
            } else {
                usleep(100);
            }
        }
    }
    /**
     * Handles the creation of the MongoCollection
     */
    protected function _setMongo()
    {
        if(!$this->_mongoParams){
            $this->_loadConfig();
        }
        $this->_mongoCollection = $this->_createMongoCollection();
    }

    /**
     *
     *
     * @param array $config
     * @return MongoCollection
     */
    protected function _createMongoCollection()
    {
        if(!isset($this->_mongoParams['server'])){
            $server  = "mongodb://localhost:27017";
        } else {
            $server = $this->_mongoParams['server'];
        }
        if(!isset($this->_mongoParams['options']) || !is_array($this->_mongoParams['options'])){
            $options = array();
        } else {
            $options = $this->_mongoParams['options'];
        }
        $mongo = new Mongo($server, $options);
        return $mongo->selectDB($this->_mongoParams['database'])
            ->selectCollection($this->_mongoParams['collection']);
    }
}

