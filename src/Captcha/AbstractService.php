<?php

namespace ReCaptcha2\Captcha;

abstract class AbstractService implements ServiceInterface
{
    /**
     * @var string
     */
    protected $siteKey;

    /**
     * @var string
     */
    protected $secretKey;

    /**
     * @var array
     */
    protected $params = [];

    public function getSiteKey()
    {
        return $this->siteKey;
    }

    public function setSiteKey($siteKey)
    {
        $this->siteKey = $siteKey;
    }

    public function getSecretKey()
    {
        return $this->secretKey;
    }

    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;
    }

    public function getParams()
    {
        return $this->params;
    }

    abstract public function getServiceUri();
}
