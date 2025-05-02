<?php
namespace ReCaptcha2;

use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'view_helpers' => [
        'factories' => [
            Form\View\Helper\Captcha\ReCaptcha2::class  => InvokableFactory::class,
            'recaptcha2formviewhelpercaptcharecaptcha2' => InvokableFactory::class,
        ],
        'aliases' => [
            'captcharecaptcha2'     => Form\View\Helper\Captcha\ReCaptcha2::class,
            // weird alias used by Laminas\Captcha
            'captcha/recaptcha2'    => Form\View\Helper\Captcha\ReCaptcha2::class,
            'captcha_recaptcha2'    => Form\View\Helper\Captcha\ReCaptcha2::class,
        ],
    ],
];
