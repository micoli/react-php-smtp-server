<?php

namespace Micoli\Smtp\Server\Event;

use Micoli\Smtp\Server\SmtpSession\SmtpSessionInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ConnectionHeloReceivedEvent extends Event
{
    protected SmtpSessionInterface $smtpSession;

    protected string $domain;

    public function __construct(SmtpSessionInterface $smtpSession, string $domain)
    {
        $this->smtpSession = $smtpSession;
        $this->domain = $domain;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }
}
