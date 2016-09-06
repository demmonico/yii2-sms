<?php
/**
 * @author: dep
 * 02.08.16
 */

namespace demmonico\sms;

use yii\base\Configurable;


/**
 * Class Sender
 * @author: dep
 * @package demmonico\sms
 *
 * @use
 * Send sms
 *      Yii::$app->sms->sendSms('Hello, world!', 'number') or Yii::$app->sms->sendSms('Hello, world!', 'recipientNumber', 'senderNumber')
 * Get account balance
 *      Yii::$app->sms->getBalance()
 * Get account numbers
 *      Yii::$app->sms->getNumbers()
 *
 * Config file
 * 'componentName' => [
 *      'class' => 'demmonico\sms\Sender',
 *      'provider' => [
 *          'class' => 'demmonico\sms\Nexmo',
 *          'apiKey' => '*******',
 *          'apiSecret' => '*******',
 *      ],
 *      'senderNumber' => 'name' or 'number',
 * ],
 * or implementing config component's bootstrap method ( @see https://github.com/demmonico/yii2-config )
 *      in config file
 *      'provider' => [
 *          'class' => 'demmonico\sms\Nexmo',
 *          'apiKey' => [
 *              'component' => 'config',
 *              'sms.Nexmo.apiKey',
 *          ],
 *          'apiSecret' => [
 *              'component' => 'config',
 *              'sms.Nexmo.apiSecret',
 *          ],
 *      ],
 *      and in local params
 *      [
 *          'sms.Nexmo.apiKey' => '******',
 *          'sms.Nexmo.apiSecret' => '******',
 *      ]
 *
 * Debug options
 *      'debug' => [
 *          'redirectNumber' => 'number',
 *          'dummyNumbers' => [
 *              'number',
 *          ],
 *      ],
 */
class Sender implements Configurable
{
    use ConfigurableTrait;


    /**
     * Sender's number
     * @var string
     */
    public $senderNumber;
    /**
     * Provider's params
     * @var array
     */
    public $provider;

    // log params
    /**
     * @var string
     */
    public $logLevel;
    /**
     * @var string
     */
    public $logCategory;

    // debug params
    /**
     * Use for enable and config debug mode
     * If this NULL debug mode disabled
     * If isset $debug['redirectNumber'] then all messages at sendSms() are redirects to this number (field $to will be ignored)
     * If is array $debug['dummyNumbers'] and $to matches to any of dummyNumbers elements then process send will be skip and all sms fields will be logged
     * @var array|null
     */
    public $debug;



    final public function __construct($config = [])
    {
        $this->applyConfigs($config);   // apply configurable
        static::init();
    }

    public function init(){}

    /**
     * Run request
     * @param $route
     * @param array $params
     * @return mixed
     */
    public function run($route, $params=[])
    {
        $api = $this->getProvider();
        return $api->run($route, $params);
    }

    /**
     * Create provider's object
     * @return object
     * @throws \Exception
     */
    protected function getProvider()
    {
        if (empty($this->provider) || !isset($this->provider['class']))
            throw new \Exception('Invalid API provider params');

        $params = $this->provider;
        $provider = $params['class'];
        unset($params['class']);

        // add log params
        if (isset($this->logLevel) && !isset($params['logLevel']))
            $params['logLevel'] = $this->logLevel;
        if (isset($this->logCategory) && !isset($params['logCategory']))
            $params['logCategory'] = $this->logCategory;

        return \Yii::createObject($provider, [$params]);
    }



    /**
     * Returns count of successfully send sms
     * @param string $text
     * @param string|int $to
     * @param string|int $from
     * @return int
     * @throws \Exception
     */
    public function sendSms($text, $to, $from=null)
    {
        // validate
        if (empty($text) || !$this->validateRecipientNumber($to))
            throw new \Exception('Invalid sms param');

        // set from
        if (is_null($from)){
            if (!is_null($this->senderNumber))
                $from = $this->senderNumber;
            else
                throw new \Exception('FromNumber field is required');
        }

        // apply debug mode
        if (is_array($this->debug)){
            // apply redirect mode
            if (isset($this->debug['redirectNumber']) && $this->validateRecipientNumber($this->debug['redirectNumber']))
                $to = $this->debug['redirectNumber'];
            // apply dummy numbers
            if (isset($this->debug['dummyNumbers']) && in_array($to, $this->debug['dummyNumbers'])){
                $message = get_called_class().' DUMMY LOG (sms): '.json_encode(['from' => $from, 'to' => $to, 'text' => $text]);
                \Yii::info($message, $this->logCategory ?: 'sms');
                return 1;
            }
        }

        $r = $this->run('sms', ['from' => $from, 'to' => $to, 'text' => $text]);
        if (is_int($r))
            return $r;
        else
            throw new \Exception('Bad sender response');
    }

    /**
     * Returns boolean whether recipient number validate
     * @param $recipientNumber
     * @return bool
     */
    protected function validateRecipientNumber($recipientNumber)
    {
        return !empty($recipientNumber) AND is_int($recipientNumber) || ctype_digit($recipientNumber);
    }


    /**
     * Returns account balance
     * @return float|int
     * @throws \Exception
     */
    public function getBalance()
    {
        $r = $this->run('account/balance');
        if (is_numeric($r))
            return $r;
        else
            throw new \Exception('Bad sender response');
    }

    /**
     * Returns array of numbers which are associate with account
     * @param int $page
     * @param int $size
     * @return array
     * @throws \Exception
     */
    public function getNumbers($page=1, $size=100)
    {
        $r = $this->run('account/numbers', ['index' => $page, 'size' => $size]);
        if (is_array($r))
            return $r;
        else
            throw new \Exception('Bad sender response');
    }

}