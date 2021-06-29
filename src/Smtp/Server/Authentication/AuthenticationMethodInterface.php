<?php

namespace Micoli\Smtp\Server\Authentication;

use Micoli\Smtp\Server\SmtpSession\SmtpSessionInterface;

interface AuthenticationMethodInterface
{
    public function getType(): string;

    public function init(SmtpSessionInterface $connection, string $data): void;

    public function handleData(SmtpSessionInterface $connection, string $data): void;

    public function validateIdentity(AuthenticationDataInterface $authenticationData, string $password): bool;
}
