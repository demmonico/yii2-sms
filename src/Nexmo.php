<?php
/**
 * @author: dep
 * 02.08.16
 */

namespace demmonico\sms;


/**
 * Class Nexmo works with Nexmo API ( @see https://docs.nexmo.com/ )
 * @author: dep
 * @package demmonico\sms
 */
class Nexmo extends BaseProvider implements SmsProviderInterface
{
    /**
     * @inheritdoc
     */
    public $baseUrl = 'https://rest.nexmo.com/';
    /**
     * @inheritdoc
     */
    public $routes = [
        'account/balance'   => 'account/get-balance',
        'account/numbers'   => 'account/numbers',
        'sms'               => 'sms/json',
    ];

    public $apiKey;
    public $apiSecret;



    /**
     * @inheritdoc
     */
    public function run($route, $params)
    {
        if (empty($this->apiKey) || empty($this->apiSecret))
            throw new \Exception('Invalid API provider params');
        $params = array_merge(
            ['api_key' => $this->apiKey, 'api_secret' => $this->apiSecret],
            $params
        );

        $url = $this->baseUrl.$this->getRoute($route).'?'.http_build_query($params);
        $response = json_decode($this->getResponse($url), true);
        return $this->parseResponse($response, $route);
    }



    /**
     * @inheritdoc
     */
    public function getResponse($url)
    {
        // check curl
        if (!function_exists('curl_version'))
            throw new \Exception('Curl lib is required');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        return curl_exec($ch);
    }

    /**
     * @inheritdoc
     */
    public function parseResponse($response, $route)
    {
        $r = null;

        switch ($route)
        {
            // if account balance response
            case 'account/balance':
                if (isset($response['value']))
                    $r = (float)$response['value'];
                break;

            // if account numbers response
            case 'account/numbers':
                $r = isset($response['numbers']) ? $response['numbers'] : [];
                break;

            // if sms response
            case 'sms':
                $r = $this->parseSmsResponse($response);
                break;
        }

        return $r;
    }

    protected function parseSmsResponse($response)
    {
        $r = null;

        if (isset($response['message-count'])){
            $message = 'Nexmo send '.$response['message-count'].' messages.';

            $r = (int)$response['message-count'];

            if ($r > 0){
                // collect all log
                if ($this->logLevel == 'all'){
                    $this->addLog($message, 'info');
                }
            } else {
                // collect error log
                $this->addLog($message, 'error');
            }
        }
        if (isset($response['messages'])) foreach ($response['messages'] as $message) {
            if (isset($message['status'])){
                if ($message['status'] == 0) {
                    $this->addLog('Status: success', 'info');
                    if (isset($message['message-id']))
                        $this->addLog('MessageId: '.$message['message-id'], 'info');
                } else {
                    $this->addLog('Status: error', 'error');
                    if (isset($message['error-text']))
                        $this->addLog('Text: '.$message['error-text'], 'error');
                }
            }
        }

        return $r;
    }

}