<?php

namespace Test\Unit;

use GeekLab\Conf\Driver\ArrayConfDriver;
use GeekLab\Conf\GLConf;
use PHPUnit\Framework\TestCase;

abstract class ControllerTestCase extends TestCase
{

    protected GLConf $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = new GLConf(
            new ArrayConfDriver(__DIR__ . '/../../config/config.php', __DIR__ . '/../../config/'),
            [],
            ['keys_lower_case']
        );
        $this->config->init();
    }
}
