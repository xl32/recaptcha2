<?php

namespace ReCaptcha2\Captcha;

interface ServiceInterface
{
    /**
     * @return Result
     */
    public function verify($input);

    /**
     * @return string
     */
    public function getSiteKey();

    /**
     * @param string $siteKey
     * @return ServiceInterface
     */
    public function setSiteKey($siteKey);

    /**
     * @param string $secretKey
     * @return ServiceInterface
     */
    public function setSecretKey($secretKey);

    /**
     * @return array
     */
    public function getParams();

    /**
     * @return string
     */
    public function getServiceUri();
}
