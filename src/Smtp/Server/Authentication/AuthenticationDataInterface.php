<?php

declare(strict_types=1);

namespace Micoli\Smtp\Server\Authentication;

use Micoli\Smtp\Server\SmtpSession\SmtpSessionInterface;

interface AuthenticationDataInterface
{
    public function getSmtpSession(): SmtpSessionInterface;

    public function getUsername(): ?string;

    public function setUsername(string $username): void;

    public function getPassword(): ?string;

    public function setPassword(string $password): void;
}
