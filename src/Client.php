<?php

namespace Githen\LaravelEqxiu;

use Githen\LaravelEqxiu\Traits\BaseTrait;
use Githen\LaravelEqxiu\Traits\UtilsTrait;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client as GuzzleHttpClient;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class Client
{
    use BaseTrait;
    use UtilsTrait;

    private $baseURI = 'https://open.eqxiu.cn/api/v1/';
    /**
     * The App Id
     */
    private $appId = "";

    /**
     * The App Key
     */
    private $appKey = "";
    /**
     * The Secret Id
     */
    private $secretId = "";

    /**
     * The Secret Key
     */
    private $secretKey = "";

    /**
     * @return string
     */
    public function getAppId(): string
    {
        return $this->appId;
    }

    /**
     * @param string $appId
     */
    public function setAppId(string $appId): void
    {
        $this->appId = $appId;
    }

    /**
     * @return string
     */
    public function getAppKey(): string
    {
        return $this->appKey;
    }

    /**
     * @param string $appKey
     */
    public function setAppKey(string $appKey): void
    {
        $this->appKey = $appKey;
    }

    public function getSecretId(): string
    {
        return $this->secretId;
    }

    public function setSecretId(string $secretId): void
    {
        $this->secretId = $secretId;
    }

    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    public function setSecretKey(string $secretKey): void
    {
        $this->secretKey = $secretKey;
    }

    public function setSecretIdAndSecretKey(string $secretId, string $secretKey): void
    {
        $this->secretId = $secretId;
        $this->secretKey = $secretKey;
    }

    /**
     * Default constructor
     *
     * @param array $config Iflytek configuration data
     * @return void
     */
    public function __construct($config)
    {
        $this->setAppId($config['app_id']);
        $this->setAppKey($config['app_key']);
        return;
    }


    /**
     * @param $uri
     * @param $options
     * @return array|mixed
     */
    public function httpRequest($method, $uri, $params = [], $loginMode = 'token')
    {
        $handlerStack = HandlerStack::create(new CurlHandler());
        $handlerStack->push(Middleware::retry($this->retryDecider(), $this->retryDelay()));
        $httpClient = new GuzzleHttpClient([
            'base_uri' => $this->baseURI,
            'timeout' => 10,
            'verify' => false,
            'handler' => $handlerStack,

        ]);
        $options = [];
        if ($loginMode == 'signature') {
            $params['appId'] = $this->getAppId();
            $params['timestamp'] = time();
            $params['signature'] = $this->genSignature($params, $this->getAppKey());
        }
        if ($loginMode == 'tokenSignature') {
            $params['secretId'] = $this->getSecretId();
            $params['type'] = 'Server';
            $params['timestamp'] = time();
            $params['signature'] = $this->genSignature($params, $this->getSecretKey());
        }
        if ($loginMode == 'token') {
            $token = $this->oauthToken();
            $separator = strpos($uri, '?') === false ? '?' : '&';
            $uri = $uri . $separator . 'token=' . $token;
        }
        if ($method == 'GET') {
            $separator = strpos($uri, '?') === false ? '?' : '&';
            $uri = $uri . $separator . Arr::query($params);
        }
        if ($method == 'POST') {
            $options['headers']['Content-Type'] = 'application/x-www-form-urlencoded';
            $options['form_params'] = $params;
        }
        try {
            $response = $httpClient->request($method, $uri, $options);
            $content = $response->getBody()->getContents();
            $resp = json_decode($content, true);
            if ($loginMode == 'token' && $resp['code'] == 'A011005') {
                $tokenXin = $this->oauthToken(true);
                $uri = str_replace($token, $tokenXin, $uri);
                $response = $httpClient->request($method, $uri, $options);
                $content = $response->getBody()->getContents();
                return json_decode($content, true);
            }
            return $resp;
        } catch (\Exception $e) {
            return $this->message($e->getCode(), $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
    }

    /**
     * 最大重试次数
     */
    const MAX_RETRIES = 3;

    /**
     * 返回一个匿名函数, 匿名函数若返回false 表示不重试，反之则表示继续重试
     * @return \Closure
     */
    private function retryDecider()
    {
        return function (
            $retries,
            Request $request,
            Response $response = null,
            RequestException $exception = null
        ) {
            // 超过最大重试次数，不再重试
            if ($retries >= self::MAX_RETRIES) {
                return false;
            }

            // 请求失败，继续重试
            if ($exception instanceof ConnectException) {
                return true;
            }

            if ($response) {
                // 如果请求有响应，但是状态码不等于200，继续重试
                if ($response->getStatusCode() != 200) {
                    return true;
                }
            }

            return false;
        };
    }

    /**
     * 返回一个匿名函数，该匿名函数返回下次重试的时间（毫秒）
     * @return \Closure
     */
    private function retryDelay()
    {
        return function ($numberOfRetries) {
            return 1000 * $numberOfRetries;
        };
    }

    /**
     * 封装消息
     * @param string $code
     * @param string $message
     * @param array $data
     * @return array
     */
    private function message($code, $message, $data = [])
    {
        return ['code' => $code, 'message' => $message, 'data' => $data];
    }

    public function showMessage($data, $status = 1)
    {
        $channel = config('eqxiu.log_channel', '');
        if (empty($channel)) {
            return;
        }
        if (is_array($data)) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        if ($status == 1) {
            Log::channel($channel)->info($data);
            return;
        }
        Log::channel($channel)->error($data);
    }

}
