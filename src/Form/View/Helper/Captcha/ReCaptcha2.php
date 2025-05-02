<?php

namespace ReCaptcha2\Form\View\Helper\Captcha;

use Traversable;
use Laminas\Captcha\AdapterInterface;
use Laminas\Form\Element\Captcha;
use Laminas\Form\ElementInterface;
use Laminas\Form\Exception;
use Laminas\Form\View\Helper\FormInput;

class ReCaptcha2 extends FormInput
{
    /**
     * @param  ElementInterface $element
     * @return string
     */
    public function __invoke(ElementInterface $element = null)
    {
        if (!$element) {
            return $this;
        }

        return $this->render($element);
    }

    /**
     * @param  ElementInterface $element
     * @throws Exception\DomainException
     * @return string
     */
    public function render(ElementInterface $element): string
    {
        if (!$element instanceof Captcha) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects a valid implementation of Laminas\Form\Element\Captcha; received "%s"',
                __METHOD__,
                (is_object($element) ? get_class($element) : gettype($element))
            ));
        }
        $captcha = $element->getCaptcha();

        if ($captcha === null || !$captcha instanceof AdapterInterface) {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has a "captcha" attribute implementing %s; none found',
                __METHOD__,
                AdapterInterface::class
            ));
        }

        /* @var $service \ReCaptcha2\Captcha\ServiceInterface */
        $service = $captcha->getService();

        $siteKey = $service->getSiteKey();
        if ($siteKey === null) {
            throw new Exception\DomainException('Missing site key');
        }

        $params = array_filter($service->getParams(), function ($param) {
            return $param !== null;
        });

        $apiPath = $service->getServiceUri();
        $scriptPath = sprintf('%s.js', $apiPath);
        $queryString = '';
        if (!empty($params)) {
            $queryString = http_build_query($params, '', '&amp;');
            $scriptPath .= '?' . $queryString;
        }

        $attributes = $element->getAttributes();
        if ($attributes instanceof Traversable) {
            $attributes = iterator_to_array($attributes);
        }

        $attributes['class'] = 'g-recaptcha';
        $attributes['data-sitekey'] = $siteKey;
        $attributeString = $this->createAttributesString($attributes);

        $template = <<<HTML
<input type="hidden" name="%s" value="1">
<div %s></div>
<script src="%s" async defer></script>
HTML;
        $markup = sprintf($template, $element->getName(), $attributeString, $scriptPath);

        $queryString .= ($queryString ? '&amp;' : '');
        $markup .= <<<HTML
<noscript>
  <div style="width: 302px; height: 422px;">
    <div style="width: 302px; height: 422px; position: relative;">
      <div style="width: 302px; height: 422px; position: absolute;">
        <iframe src="{$apiPath}/fallback?{$queryString}k={$siteKey}"
                frameborder="0" scrolling="no"
                style="width: 302px; height:422px; border-style: none;">
        </iframe>
      </div>
      <div style="width: 300px; height: 60px; border-style: none;
                  bottom: 12px; left: 25px; margin: 0px; padding: 0px; right: 25px;
                  background: #f9f9f9; border: 1px solid #c1c1c1; border-radius: 3px;">
        <textarea id="g-recaptcha-response" name="g-recaptcha-response"
                  class="g-recaptcha-response"
                  style="width: 250px; height: 40px; border: 1px solid #c1c1c1;
                         margin: 10px 25px; padding: 0px; resize: none;" >
        </textarea>
      </div>
    </div>
  </div>
</noscript>
HTML;

        return $markup;
    }
}
