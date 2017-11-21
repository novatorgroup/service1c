<?php

namespace novatorgroup\service1c;

use yii\base\Component;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;

/**
 * Component for executing requests to HTTP Services 1C
 */
class HttpService extends Component
{
    /**
     * HTTP Service host
     * http://192.168.1.18/
     * @var string
     */
    public $host;

    /**
     * HTTP Service base
     * @var string
     */
    public $base;

    /**
     * Default CURL options
     * @var array
     */
    public $defaultOptions = [
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_HTTPHEADER => [
            'Content-Type: text/xml'
        ]
    ];

    public function init()
    {
        parent::init();

        if (empty($this->host)) {
            throw new InvalidParamException('Param «service» is empty.');
        }

        if (empty($this->base)) {
            throw new InvalidParamException('Param «base» is empty.');
        }

        $this->host = rtrim($this->host, '/') . '/';
        $this->base = trim($this->base, '/') . '/hs/';
    }

    /**
     * GET request to 1C HTTP Service
     *
     * @param string $command
     * @param array $params - array params ['param1', 'a' => 'p2', 'b' => 'p3'] -> /param1/?a=p2&b=p3
     * @param array $options - connection options
     * @return string
     */
    public function get(string $command, array $params = [], array $options = []): string
    {
        return $this->request('get', $command, $params, $options);
    }

    /**
     * POST request to 1C HTTP Service
     *
     * @param string $command
     * @param array $params - array POST params ['a' => 'p2', 'b' => 'p3']
     * @param array $options - connection options
     * @return string
     */
    public function post(string $command, array $params = [], array $options = []): string
    {
        return $this->request('post', $command, $params, $options);
    }

    /**
     * Execute request
     *
     * @param string $method
     * @param string $command
     * @param array $params
     * @param array $options
     * @return string
     */
    private function request(string $method, string $command, array $params = [], array $options = []): string
    {
        $ch = curl_init();
        curl_setopt_array($ch, ArrayHelper::merge($this->defaultOptions, $options));

        $url = $this->host . $this->base . $command;

        if ($method === 'get') {
            curl_setopt($ch, CURLOPT_URL, $url . '/' . $this->paramsGet($params));
        } else if ($method === 'post') {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }

        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_errno($ch);
        curl_close($ch);

        if ($error || $code !== 200 || empty($response)) {
            return '';
        }

        return $response;
    }

    /**
     * Prepare GET params
     *
     * @param array $params
     * @return string
     */
    private function paramsGet(array $params): string
    {
        $urlParams = [];
        $searchParams = [];
        foreach ($params as $key => $param) {
            if (is_numeric($key)) {
                $urlParams[] = urlencode($param);
            } else {
                $searchParams[] = urlencode($key) . '=' . urlencode($param);
            }
        }

        $url = implode('/', $urlParams);
        if (count($searchParams)) {
            $url .= '?' . implode('&', $searchParams);
        }

        return $url;
    }
}