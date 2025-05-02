<?php

namespace ReCaptcha2\Captcha;

use Traversable;
use Laminas\Captcha\Exception;
use Laminas\Http\Client as HttpClient;

class NoCaptchaService extends AbstractService
{
    const API_SERVER = 'https://www.google.com/recaptcha/api';
    const VERIFY_SERVER = 'https://www.google.com/recaptcha/api/siteverify';
    const CACERT_PATH = __DIR__ . '/../../ssl/cacert.pem';

    /**
     * @var string
     */
    protected $ip;

    /**
     * @var array
     */
    protected $params = [
        'onload' => null,
        'render' => null,
        'hl' => null,
    ];

    /**
     *
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @param array|Traversable $options
     */
    public function __construct($options = [])
    {
        $this->setOptions($options);
    }

    /**
     * @return string
     */
    public function getServiceUri()
    {
        return self::API_SERVER;
    }

    /**
     * @return HttpClient
     */
    public function getHttpClient()
    {
        if (null === $this->httpClient) {
            $this->httpClient = new HttpClient();
        }
        return $this->httpClient;
    }

    /**
     * @param array|Traversable|HttpClient $httpClient
     * @return NoCaptchaService
     */
    public function setHttpClient($httpClient)
    {
        $clientOptions = [];
        if (is_array($httpClient)) {
            $clientOptions  = isset($httpClient['options']) ? $httpClient['options'] : [];
            $httpClient     = isset($httpClient['class']) ? $httpClient['class'] : HttpClient::class;
        }

        if (is_string($httpClient)) {
            if (!class_exists($httpClient)) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Unable to locate HttpClient class "%s"',
                    $httpClient
                ));
            }
            $httpClient = new $httpClient(null, $clientOptions);
        }

        if (!$httpClient instanceof HttpClient) {
            throw new Exception\DomainException(sprintf(
                '%s expects a valid implementation of Laminas\Http\Client; received "%s"',
                __METHOD__,
                (is_object($httpClient) ? get_class($httpClient) : gettype($httpClient))
            ));
        }

        $this->httpClient = $httpClient;
        return $this;
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     * @return NoCaptchaService
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * @param array $params
     * @return NoCaptchaService
     */
    public function setParams(array $params)
    {
        foreach ($this->params as $key => $param) {
            if (array_key_exists($key, $params)) {
                $this->params[$key] = $params[$key];
            }
        }
        return $this;
    }

    /**
     * @param  array|Traversable $options
     * @throws Exception\InvalidArgumentException
     * @return NoCaptchaService
     */
    public function setOptions($options = [])
    {
        if (!is_array($options) && !$options instanceof Traversable) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an array or Traversable');
        }

        foreach ($options as $name => $option) {
            $fname = 'set' . ucfirst($name);
            if (($fname != 'setOptions') && method_exists($this, $fname)) {
                $this->{$fname}($option);
            }
        }

        return $this;
    }

    /**
     * @param string $responseField
     * @return \Laminas\Http\Response
     */
    protected function post($responseField)
    {
        if ($this->secretKey === null) {
            throw new Exception\DomainException('Missing secret key');
        }

        /* Fetch an instance of the http client */
        $httpClient = $this->getHttpClient();

        $params = [
            'secret' => $this->secretKey,
            'response' => $responseField,
        ];
        if (null !== $this->ip) {
            $params['remoteip'] = $this->ip;
        }

        $request = new \Laminas\Http\Request();
        $request->setUri(self::VERIFY_SERVER);
        $request->getPost()->fromArray($params);
        $request->setMethod(\Laminas\Http\Request::METHOD_POST);
        $httpClient->setEncType(HttpClient::ENC_URLENCODED);

        return $httpClient->send($request);
    }

    /**
     * @param string $value
     * @return \ReCaptcha2\Captcha\Result
     */
    public function verify($value)
    {
        $response = $this->post($value);
        return new Result($response);
    }
}
