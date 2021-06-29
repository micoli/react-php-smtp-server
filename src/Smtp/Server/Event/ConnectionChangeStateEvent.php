<?php

namespace Micoli\Smtp\Server\Event;

use Micoli\Smtp\Server\SmtpSession\SmtpSessionInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ConnectionChangeStateEvent extends Event
{
    protected SmtpSessionInterface $smtpSession;
    protected string $oldState;
    protected string $newState;

    public function __construct(SmtpSessionInterface $smtpSession, string $oldState, string $newState)
    {
        $this->smtpSession = $smtpSession;
        $this->oldState = $oldState;
        $this->newState = $newState;
    }

    public function getSmtpSession(): SmtpSessionInterface
    {
        return $this->smtpSession;
    }

    public function getOldState(): string
    {
        return $this->oldState;
    }

    public function getNewState(): string
    {
        return $this->newState;
    }
}
