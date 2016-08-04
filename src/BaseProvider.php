<?php
/**
 * @author: dep
 * 02.08.16
 */

namespace demmonico\sms;

use yii\base\Configurable;


class BaseProvider implements Configurable
{
    const LOG_LEVEL_ALL     = 'all';
    const LOG_LEVEL_ERROR   = 'error';

    /**
     * Current level which will be logged
     * Possible are "error", "all"
     * Default is "error"
     *
     * @var string
     */
    public $logLevel = self::LOG_LEVEL_ERROR;
    /**
     * Category which will be used in logger
     * Default is "sms"
     *
     * @var string
     */
    public $logCategory = 'sms';

    /**
     * Base URL to make request
     * TODO overwrite this at provider class
     *
     * @var string
     */
    public $baseUrl;
    /**
     * Routes array to use at sender
     * TODO overwrite this at provider class
     *
     * @var array
     */
    public $routes = [];

    protected $_logType;
    protected $_logMessage;



    final public function __construct($config = [])
    {
        // apply configurable
        if (is_array($config)) foreach ($config as $k=>$v){
            if (property_exists(get_called_class(), $k))
                $this->$k = $v;
        }

        // set logCategory
        if (is_null($this->logCategory))
            $this->logCategory = strtolower(get_called_class());

        $this->init();
    }

    public function init(){}



    /**
     * Returns API route string
     * @param $senderRoute
     * @return string
     * @throws \Exception
     */
    public function getRoute($senderRoute)
    {
        if (isset($this->routes[$senderRoute]))
            return $this->routes[$senderRoute];
        else
            throw new \Exception('Sender: route '.$senderRoute.' is invalid');
    }



    /**
     * Append $message to current log text
     * @param string $message
     * @param string|null $type
     */
    public function addLog($message, $type=null)
    {
        if ($this->_logMessage)
            $this->_logMessage .= '; ';
        $this->_logMessage .= $message;
        if (!is_null($type) && !($this->_logType == 'error' && $type == 'info'))
            $this->_logType = $type;
    }

    /**
     * Wrapper for Yii logger
     */
    public function log()
    {
        $type = $this->_logType;
        $message = $this->_logMessage;
        if ($type && $message)
            \Yii::$type($message, $this->logCategory);
    }

}