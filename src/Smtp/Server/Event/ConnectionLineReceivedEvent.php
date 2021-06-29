<?php

namespace Micoli\Smtp\Server\Event;

use Micoli\Smtp\Server\SmtpSession\SmtpSessionInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ConnectionLineReceivedEvent extends Event
{
    protected SmtpSessionInterface $smtpSession;
    protected string $line;

    public function __construct(SmtpSessionInterface $smtpSession, string $line)
    {
        $this->smtpSession = $smtpSession;
        $this->line = $line;
    }

    public function getLine(): string
    {
        return $this->line;
    }
}
