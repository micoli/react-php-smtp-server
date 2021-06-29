<?php

declare(strict_types=1);

namespace Micoli\Tests\Smtp\Server;

use Micoli\Tests\AbstractTest;
use Micoli\Smtp\Server\Event\ConnectionHeloReceivedEvent;
use Micoli\Smtp\Server\Event\Events;
use Micoli\Smtp\Server\Event\MessageReceivedEvent;
use Micoli\Tests\Smtp\SessionTestTrait;

class AuthenticationErrorTest extends AbstractTest
{
    use SessionTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSession();
    }

    /** @test */
    public function itShouldNotReceiveAMessage(): void
    {
        $this->traceEvent(
            Events::CONNECTION_HELO_RECEIVED,
            ConnectionHeloReceivedEvent::class
        );
        $this->traceEvent(
            Events::MESSAGE_RECEIVED,
            MessageReceivedEvent::class
        );

        $this->startServer();

        $this->sendCommands(...[
            'EHLO [127.0.0.1]',
            'AUTH UNKNOWN_AUTHENTICATION_METHOD',
        ]);
        $this->loop->run();
        $this->assertSame([
            "220 Welcome to ReactPHP SMTP Server\r\n",
            "250-Hello 127.0.0.1\r\n",
            "250 AUTH CRAM-MD5 LOGIN PLAIN\r\n",
            "504 Unrecognized authentication type.\r\n",
        ], $this->socketDataReceived);

        $this->assertCount(1, $this->eventCalls[Events::CONNECTION_HELO_RECEIVED]);
        $this->assertCount(0, $this->eventCalls[Events::MESSAGE_RECEIVED]);
    }
}
