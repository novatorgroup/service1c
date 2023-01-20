<?php

namespace novatorgroup\service1c;

use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\helpers\ArrayHelper;

/**
 * Component for executing requests to HTTP Services 1C
 *
 * @author Melnikov R.S. <mrs2000@inbox.ru>
 */
final class HttpService extends Component
{
    /**
     * HTTP Service host
     */
    public string $host = '';

    /**
     * HTTP Service base
     */
    public string $base = '';

    /**
     * 1C user login
     */
    public string $login = '';

    /**
     * 1C user password
     */
    public string $password = '';

    /**
     * CURL options
     */
    public array $curlOptions = [];

    private string $hostUrl;

    public function init(): void
    {
        parent::init();

        if (empty($this->host)) {
            throw new InvalidArgumentException('Param «host» is empty.');
        }

        if (empty($this->base)) {
            throw new InvalidArgumentException('Param «base» is empty.');
        }

        $this->hostUrl = rtrim($this->host, '/') . '/' . trim($this->base, '/') . '/hs/';

        if (empty($this->login) === false) {
            $authorization = 'Authorization: Basic ' . base64_encode($this->login . ':' . $this->password);
            $this->curlOptions[CURLOPT_HTTPHEADER][] = $authorization;
        }
    }

    /**
     * GET request to 1C HTTP Service
     *
     * @param array $params - array params ['param1', 'a' => 'p2', 'b' => 'p3'] -> /param1/?a=p2&b=p3
     * @param array $options - connection options
     */
    public function get(string $command, array $params = [], array $options = []): Response
    {
        return $this->request('get', $command, $params, $options);
    }

    /**
     * POST request to 1C HTTP Service
     *
     * @param array $params - array POST params ['a' => 'p2', 'b' => 'p3']
     * @param array $options - connection options
     */
    public function post(string $command, array $params = [], array $options = []): Response
    {
        return $this->request('post', $command, $params, $options);
    }

    /**
     * Execute request
     */
    private function request(string $method, string $command, array $params = [], array $options = []): Response
    {
        $ch = curl_init();

        $options[CURLOPT_RETURNTRANSFER] = true;
        curl_setopt_array($ch, ArrayHelper::merge($this->curlOptions, $options));

        $responseHeaders = [];
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, static function ($ch, $header) use (&$responseHeaders) {
            $len = mb_strlen($header);
            $header = explode(':', $header, 2);
            if (count($header) < 2) {
                return $len;
            }
            $responseHeaders[mb_strtolower(trim($header[0]))] = trim($header[1]);
            return $len;
        });

        if ($method === 'get') {
            curl_setopt($ch, CURLOPT_URL, $this->hostUrl . $command . '/' . $this->paramsGet($params));
        } else if ($method === 'post') {
            curl_setopt($ch, CURLOPT_URL, $this->hostUrl . $command);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, JSON_UNESCAPED_UNICODE));
        }

        $response = new Response();
        $response->result = curl_exec($ch);
        $response->code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response->headers = $responseHeaders;
        if (curl_errno($ch)) {
            $response->error = curl_error($ch);
        }
        curl_close($ch);

        return $response;
    }

    /**
     * Prepare GET params
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