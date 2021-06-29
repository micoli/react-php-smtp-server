<?php

namespace Micoli\Smtp\Server\Authentication;

use Micoli\Smtp\Server\SmtpSession\SmtpSessionInterface;

abstract class AbstractAuthenticationData implements AuthenticationDataInterface
{
    private SmtpSessionInterface $smtpSession;
    private ?string $username;
    private ?string $password;

    public function __construct(SmtpSessionInterface $smtpSession)
    {
        $this->smtpSession = $smtpSession;
        $this->username = null;
        $this->password = null;
    }

    public function getSmtpSession(): SmtpSessionInterface
    {
        return $this->smtpSession;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }
}
