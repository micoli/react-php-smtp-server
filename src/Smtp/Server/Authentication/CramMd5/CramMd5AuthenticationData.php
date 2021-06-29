<?php

namespace Micoli\Smtp\Server\Authentication\CramMd5;

use Micoli\Smtp\Server\Authentication\AbstractAuthenticationData;
use Micoli\Smtp\Server\SmtpSession\SmtpSessionInterface;

final class CramMd5AuthenticationData extends AbstractAuthenticationData
{
    private string $challenge;

    public function __construct(SmtpSessionInterface $smtpSession, string $challenge)
    {
        parent::__construct($smtpSession);
        $this->challenge = $challenge;
    }

    public function getChallenge(): string
    {
        return $this->challenge;
    }
}
