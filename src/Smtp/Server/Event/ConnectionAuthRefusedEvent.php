<?php

namespace Micoli\Smtp\Server\Event;

use Micoli\Smtp\Server\Authentication\AuthenticationDataInterface;
use Micoli\Smtp\Server\SmtpSession\SmtpSessionInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ConnectionAuthRefusedEvent extends Event
{
    protected SmtpSessionInterface $smtpSession;
    private AuthenticationDataInterface $authenticationData;

    public function __construct(SmtpSessionInterface $smtpSession, AuthenticationDataInterface $authenticationData)
    {
        $this->smtpSession = $smtpSession;
        $this->authenticationData = $authenticationData;
    }

    public function getSmtpSession(): SmtpSessionInterface
    {
        return $this->smtpSession;
    }

    public function getAuthenticationData(): AuthenticationDataInterface
    {
        return $this->authenticationData;
    }
}
