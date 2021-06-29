<?php

declare(strict_types=1);

namespace Micoli\Tests\Smtp\Server;

use Micoli\Tests\AbstractTest;
use Micoli\Smtp\Server\Event\ConnectionHeloReceivedEvent;
use Micoli\Smtp\Server\Event\Events;
use Micoli\Smtp\Server\Event\MessageReceivedEvent;
use Micoli\Smtp\Server\Message\Message;
use Micoli\Tests\Smtp\SessionTestTrait;

class AuthenticationPlainServerTest extends AbstractTest
{
    use SessionTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSession();
    }

    /** @test */
    public function itShouldReceiveAMessage(): void
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
            'AUTH PLAIN',
            base64_encode("user1\000user1\000password1"),
            'MAIL FROM:<john@doe.com>',
            'RCPT TO:<receiver@domain.org>',
            'RCPT TO:<other@domain.org>',
            'RCPT TO:<another@domain.org>',
            'DATA',
            'Message-ID: <285ff55a147a6a43e527b1e51963b129@swift.generated>',
            'Subject: Wonderful Subject',
            'Date: Wed, 23 Jun 2021 00:05:56 +0200',
            'From: John Doe <john@doe.com>',
            'To: receiver@domain.org, A name <other@domain.org>, A name <another@domain.org>',
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=utf-8',
            'Content-Transfer-Encoding: quoted-printable',
            '',
            'Here is the message itself',
            '.',
        ]);
        $this->loop->run();
        $this->assertSame([
            "220 Welcome to ReactPHP SMTP Server\r\n",
            "250-Hello 127.0.0.1\r\n",
            "250 AUTH CRAM-MD5 LOGIN PLAIN\r\n",
            "334 \r\n",
            "235 2.7.0 Authentication successful\r\n",
            "250 MAIL OK\r\n",
            "250 Accepted\r\n",
            "250 Accepted\r\n",
            "250 Accepted\r\n",
            "354 Enter message, end with CRLF . CRLF\r\n",
            "250 OK\r\n",
        ], $this->socketDataReceived);

        $this->assertCount(1, $this->eventCalls[Events::CONNECTION_HELO_RECEIVED]);
        $this->assertCount(1, $this->eventCalls[Events::MESSAGE_RECEIVED]);
        /** @var Message $message */
        $message = $this->eventCalls[Events::MESSAGE_RECEIVED][0]->getMessage();
        $this->assertSame('john@doe.com', $message->getFrom()->getAddress());
        $this->assertSame('receiver@domain.org', $message->getToRecipients()[0]->getAddress());
        $this->assertSame('other@domain.org', $message->getToRecipients()[1]->getAddress());
        $this->assertSame('another@domain.org', $message->getToRecipients()[2]->getAddress());
        $this->assertSame(trim(
            <<<EOS
            Message-ID: <285ff55a147a6a43e527b1e51963b129@swift.generated>
            Subject: Wonderful Subject
            Date: Wed, 23 Jun 2021 00:05:56 +0200
            From: John Doe <john@doe.com>
            To: receiver@domain.org, A name <other@domain.org>, A name <another@domain.org>
            MIME-Version: 1.0
            Content-Type: text/plain; charset=utf-8
            Content-Transfer-Encoding: quoted-printable
            
            Here is the message itself
            EOS
        ), str_replace("\r", '', trim($message->getRawContent())));
    }

    /** @test */
    public function itShouldNotReceiveAMessageWithWrongCredentials(): void
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
            'AUTH PLAIN',
            base64_encode("user1\000user2\000password2"),
        ]);
        $this->loop->run();
        $this->assertSame([
            "220 Welcome to ReactPHP SMTP Server\r\n",
            "250-Hello 127.0.0.1\r\n",
            "250 AUTH CRAM-MD5 LOGIN PLAIN\r\n",
            "334 \r\n",
            "535 Authentication credentials invalid\r\n",
        ], $this->socketDataReceived);

        $this->assertCount(1, $this->eventCalls[Events::CONNECTION_HELO_RECEIVED]);
        $this->assertCount(0, $this->eventCalls[Events::MESSAGE_RECEIVED]);
    }
}
