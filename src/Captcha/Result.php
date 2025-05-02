<?php

namespace ReCaptcha2\Captcha;

use Laminas\Http\Response as HttpResponse;

class Result
{
    /**
     * @var boolean
     */
    protected $status = null;

    /**
     * @var array
     */
    protected $errorCodes = [];

    /**
     * @param HttpResponse $httpResponse
     */
    public function __construct(HttpResponse $httpResponse = null)
    {
        if ($httpResponse !== null) {
            $this->setFromHttpResponse($httpResponse);
        }
    }

    /**
     * @param boolean $status
     * @return Response
     */
    public function setStatus($status)
    {
        $this->status = (bool)$status;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return boolean
     */
    public function isValid()
    {
        return $this->getStatus();
    }

    /**
     * @param array $errorCodes
     * @return Response
     */
    public function setErrorCodes($errorCodes)
    {
        if (is_string($errorCodes)) {
            $errorCodes = [$errorCodes];
        }
        $this->errorCodes = $errorCodes;

        return $this;
    }

    /**
     * @return array
     */
    public function getErrorCodes()
    {
        return $this->errorCodes;
    }

    /**
     * @param HttpResponse $response
     * @return Response
     */
    public function setFromHttpResponse(HttpResponse $response)
    {
        $body = $response->getBody();

        $parts = json_decode($body, true);

        $status = false;
        $errorCodes = [];

        if (is_array($parts) && array_key_exists('success', $parts)) {
            $status = $parts['success'];
            if (array_key_exists('error-codes', $parts)) {
                $errorCodes = $parts['error-codes'];
            }
        }

        $this->setStatus($status);
        $this->setErrorCodes($errorCodes);

        return $this;
    }
}
