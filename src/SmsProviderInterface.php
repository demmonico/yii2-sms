<?php
/**
 * @author: dep
 * 03.08.16
 */

namespace demmonico\sms;


interface SmsProviderInterface
{
    /**
     * Main method
     * @param $route
     * @param $params
     * @return float|int|mixed|null
     */
    public function run($route, $params);

    /**
     * Makes API request and returns API response
     * @param $url
     * @return mixed
     * @throws \Exception
     */
    public function getResponse($url);

    /**
     * Returns parsed API response or NULL if failed parse
     * @param array $response
     * @param string $route
     * @return float|int|mixed|null
     */
    public function parseResponse($response, $route);

    /**
     * Append $message to current log text
     * @param string $message
     * @param string|null $type
     */
    public function addLog($message, $type=null);

    /**
     * Wrapper for Yii logger
     */
    public function log();
}