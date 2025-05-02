<?php

namespace LaminasTest\ReCaptcha2;

use PHPUnit\Framework\TestCase;

class ModuleTest extends TestCase
{
    public function testGetConfigArray()
    {
        $module = new \ReCaptcha2\Module();

        $this->assertIsArray($module->getConfig());
    }
}
