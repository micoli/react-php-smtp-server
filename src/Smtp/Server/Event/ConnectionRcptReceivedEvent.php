<?php

namespace Micoli\Smtp\Server\Event;

use Micoli\Smtp\Server\SmtpSession\SmtpSessionInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class ConnectionRcptReceivedEvent.
 */
class ConnectionRcptReceivedEvent extends Event
{
    protected SmtpSessionInterface $smtpSession;
    protected string $mail;
    protected string $name;

    public function __construct(SmtpSessionInterface $smtpSession, string $mail, string $name)
    {
        $this->smtpSession = $smtpSession;
        $this->mail = $mail;
        $this->name = $name;
    }

    public function getMail(): string
    {
        return $this->mail;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
