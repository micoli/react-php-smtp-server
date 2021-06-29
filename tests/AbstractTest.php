<?php

declare(strict_types=1);

namespace Micoli\Tests;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

abstract class AbstractTest extends TestCase
{
    use MockeryPHPUnitIntegration;


    protected function setUp(): void
    {
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
