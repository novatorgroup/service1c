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
     * @var string
     */
    public $host;

    /**
     * HTTP Service base
     * @var string
     */
    public $base;

    /**
     * 1C user login
     * @var string
     */
    public $login;

    /**
     * 1C user password
     * @var string
     */
    public $password;

    /**
     * Default CURL options
     * @var array
     */
    public $curlOptions = [
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

        if (!empty($this->login)) {
            $authorization = 'Authorization: Basic ' . base64_encode($this->login . ':' . $this->password);
            $this->curlOptions[CURLOPT_HTTPHEADER][] = $authorization;
        }
    }

    /**
     * GET request to 1C HTTP Service
     *
     * @param string $command
     * @param array $params - array params ['param1', 'a' => 'p2', 'b' => 'p3'] -> /param1/?a=p2&b=p3
     * @param array $options - connection options
     * @return Response
     */
    public function get(string $command, array $params = [], array $options = []): Response
    {
        return $this->request('get', $command, $params, $options);
    }

    /**
     * POST request to 1C HTTP Service
     *
     * @param string $command
     * @param array $params - array POST params ['a' => 'p2', 'b' => 'p3']
     * @param array $options - connection options
     * @return Response
     */
    public function post(string $command, array $params = [], array $options = []): Response
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
     * @return Response
     */
    private function request(string $method, string $command, array $params = [], array $options = []): Response
    {
        $ch = curl_init();
        curl_setopt_array($ch, ArrayHelper::merge($this->curlOptions, $options));

        $url = $this->host . $this->base . $command;

        if ($method === 'get') {
            curl_setopt($ch, CURLOPT_URL, $url . '/' . $this->paramsGet($params));
        } else if ($method === 'post') {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }

        $response = new Response();

        $response->result = curl_exec($ch);
        $response->code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $response->error = curl_error($ch);
        } else if (empty($response->result)) {
            $response->error = 'Empty result.';
        }

        curl_close($ch);

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
                $urlParams[] = rawurlencode($param);
            } else {
                $searchParams[] = rawurlencode($key) . '=' . rawurlencode($param);
            }
        }

        $url = implode('/', $urlParams);
        if (count($searchParams)) {
            $url .= '?' . implode('&', $searchParams);
        }

        return $url;
    }
}