<?php

namespace LaminasTest\ReCaptcha2\View\Helper\Captcha;

use ArrayObject;
use PHPUnit\Framework\TestCase;
use ReCaptcha2\Captcha\NoCaptchaService;
use ReCaptcha2\Captcha\ReCaptcha2;
use ReCaptcha2\Form\View\Helper\Captcha\ReCaptcha2 as ReCaptcha2ViewHelper;
use Laminas\Form\Element\Captcha;
use Laminas\Form\Element\Text;
use Laminas\Form\Exception;

class ReCaptcha2Test extends TestCase
{
    public function setUp(): void
    {
        $this->helper = new ReCaptcha2ViewHelper();
        parent::setUp();
    }

    public function testInvokeReturnsSelfWithoutElementParameter()
    {
        $helper = $this->helper;
        $this->assertSame($this->helper, $helper());
    }

    public function testRenderWithAnotherFormElementWillThrowException()
    {
        $this->expectException(
            Exception\InvalidArgumentException::class,
            'expects a valid implementation of Laminas\Form\Element\Captcha; received'
        );
        $this->helper->render(new Text());
    }

    public function testRenderWithoutCaptchaServiceWillThrowException()
    {
        $captchaMock = $this->createMock(Captcha::class, ['getCaptcha']);
        $captchaMock->expects($this->once())
            ->method('getCaptcha')
            ->willReturn(null);

        $this->expectException(
            Exception\DomainException::class,
            'requires that the element has a "captcha" attribute implementing'
        );
        $this->helper->render($captchaMock);
    }

    public function testRenderWithoutSiteKeyServiceWillThrowException()
    {
        $captchaMock = $this->createMock(Captcha::class, ['getCaptcha']);
        $captchaMock->expects($this->once())
            ->method('getCaptcha')
            ->willReturn(new ReCaptcha2());

        $this->expectException(Exception\DomainException::class, 'Missing site key');
        $this->helper->render($captchaMock);
    }

    public function testRender()
    {
        $config = new ArrayObject([
            'siteKey' => 'test-site-key',
            'params' => [
                'render' => 'test-param-render',
            ],
        ]);
        $noCaptchaService = new NoCaptchaService($config);
        $reCaptcha2Mock = $this->createMock(ReCaptcha2::class, ['getService']);
        $reCaptcha2Mock->expects($this->once())
            ->method('getService')
            ->willReturn($noCaptchaService);

        $captchaMock = $this->createMock(Captcha::class, ['getCaptcha', 'getAttributes', 'getName']);
        $captchaMock->expects($this->once())
            ->method('getCaptcha')
            ->willReturn($reCaptcha2Mock);
        $captchaMock->expects($this->once())
            ->method('getAttributes')
            ->willReturn(['data-theme' => 'dark']);
        $captchaMock->expects($this->once())
            ->method('getName')
            ->willReturn('test-name');

        $helper = $this->helper;
        $result = $helper($captchaMock);
        $this->assertStringContainsString('<input type="hidden" name="test-name"', $result);
        $this->assertStringContainsString('<div data-theme="dark" class="g-recaptcha" data-sitekey="test-site-key"', $result);
        $this->assertStringContainsString(sprintf(
            '<iframe src="%s/fallback?render=test-param-render&amp;k=test-site-key"',
            NoCaptchaService::API_SERVER
        ), $result);
    }
}
