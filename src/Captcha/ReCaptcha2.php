<?php

namespace ReCaptcha2\Captcha;

use Laminas\Captcha\AbstractAdapter;
use Laminas\Captcha\Exception;

class ReCaptcha2 extends AbstractAdapter
{
    /**
     * @var ServiceInterface
     */
    protected $service;

    /**#@+
     * Error codes
     */
    const ERROR_CAPTCHA_GENERAL     = 'error-captcha-general';
    const MISSING_INPUT_RESPONSE    = 'missing-input-response';
    const INVALID_INPUT_RESPONSE    = 'invalid-input-response';
    const MISSING_INPUT_SECRET      = 'missing-input-secret';
    const INVALID_INPUT_SECRET      = 'invalid-input-secret';
    /**#@-*/

    /**
     * Error messages
     * @var array
     */
    protected $messageTemplates = [
        self::ERROR_CAPTCHA_GENERAL     => 'Failed to validate captcha',
        self::MISSING_INPUT_RESPONSE    => 'Missing captcha fields',
        self::INVALID_INPUT_RESPONSE    => 'Captcha value is wrong: %value%',
        self::MISSING_INPUT_SECRET      => 'Missing captcha secret',
        self::INVALID_INPUT_SECRET      => 'Invalid captcha secret',
    ];

    /**
     * @param array|\Traversable $options
     * @throws Exception\DomainException
     * @return ReCaptcha2
     */
    public function setOptions($options = [])
    {
        if (array_key_exists('service', $options)) {
            $this->setService($options['service']);
            unset($options['service']);
        }

        parent::setOptions($options);

        return $this;
    }

    /**
     * @return ServiceInterface
     */
    public function getService()
    {
        if (null === $this->service) {
            $this->service = new NoCaptchaService();
        }
        return $this->service;
    }

    /**
     * @param array|\Traversable|ServiceInterface $service
     * @return ReCaptcha2
     */
    public function setService($service)
    {
        $serviceOptions = [];
        if (is_array($service)) {
            $serviceOptions = isset($service['options']) ? $service['options'] : [];
            $service        = isset($service['class']) ? $service['class'] : NoCaptchaService::class;
        }

        if (is_string($service)) {
            if (!class_exists($service)) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Unable to locate service class "%s"',
                    $service
                ));
            }
            $service = new $service($serviceOptions);
        }

        if (!isset($service) || !$service instanceof ServiceInterface) {
            throw new Exception\DomainException(sprintf(
                '%s expects a valid implementation of %s; received "%s"',
                __METHOD__,
                ServiceInterface::class,
                (is_object($service) ? get_class($service) : gettype($service))
            ));
        }
        $this->service = $service;
        return $this;
    }

    /**
     * @see ServiceInterface::getSiteKey()
     */
    public function getSiteKey()
    {
        return $this->getService()->getSiteKey();
    }

    /**
     * @see ServiceInterface::setSiteKey()
     */
    public function setSiteKey($siteKey)
    {
        $this->getService()->setSiteKey($siteKey);
        return $this;
    }

    /**
     * @see ServiceInterface::getSecretKey()
     */
    public function getSecretKey()
    {
        return $this->getService()->getSecretKey();
    }

    /**
     * @see ServiceInterface::setSecretKey()
     */
    public function setSecretKey($secretKey)
    {
        $this->getService()->setSecretKey($secretKey);
        return $this;
    }

    /**
     * @see AbstractAdapter::generate()
     * @return string
     */
    public function generate()
    {
        return '';
    }

    /**
     * @see \Laminas\Validator\ValidatorInterface::isValid()
     * @param mixed $value
     * @param mixed $context
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        if (!is_array($value) && !is_array($context)) {
            $this->error(self::MISSING_INPUT_RESPONSE);
            return false;
        }

        if (!is_array($value) && is_array($context)) {
            $value = $context;
        }

        if (empty($value['g-recaptcha-response'])) {
            $this->error(self::MISSING_INPUT_RESPONSE);
            return false;
        }

        $service = $this->getService();
        $res = $service->verify($value['g-recaptcha-response']);

        if (!$res) {
            $this->error(self::ERROR_CAPTCHA_GENERAL);
            return false;
        }

        if (!$res->isValid()) {
            $errorCodes = $res->getErrorCodes();
            if (!empty($errorCodes)) {
                foreach ($errorCodes as $errorCode) {
                    $this->error(self::INVALID_INPUT_RESPONSE, $errorCode);
                }
                return false;
            }

            $this->error(self::ERROR_CAPTCHA_GENERAL);
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getHelperName()
    {
        return 'captcha/recaptcha2';
    }
}
