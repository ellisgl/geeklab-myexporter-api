<?php

namespace Test\Unit;

use GeekLab\Conf\Driver\ArrayConfDriver;
use GeekLab\Conf\GLConf;
use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/../../constants.php');

abstract class ControllerTestCase extends TestCase
{
    protected GLConf $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = new GLConf(
            new ArrayConfDriver(APP_CFG . '/config.php', APP_CFG . '/'),
            [],
            ['keys_lower_case']
        );
        $this->config->init();
    }
}
