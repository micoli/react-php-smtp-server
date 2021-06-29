<?php

namespace Micoli\Smtp\Server\Authentication\Plain;

use Micoli\Smtp\Server\Authentication\AuthenticationDataInterface;
use Micoli\Smtp\Server\Authentication\AuthenticationMethodInterface;
use Micoli\Smtp\Server\SmtpSession\ReturnCode;
use Micoli\Smtp\Server\SmtpSession\SmtpSessionInterface;

class PlainAuthenticationMethod implements AuthenticationMethodInterface
{
    public function getType(): string
    {
        return 'PLAIN';
    }

    public function init(SmtpSessionInterface $connection, ?string $data): void
    {
        $connection->setAuthenticationData(new PlainAuthenticationData($connection));

        if (null === $data) {
            // Ask for token.
            $connection->sendReply(ReturnCode::_334_CONTINUE_AUTHENTICATION_REQUEST, '');

            return;
        }

        // Plain auth accepts token in the same call.
        $this->handleData($connection, $data);
    }

    public function handleData(SmtpSessionInterface $connection, string $data): void
    {
        $authenticationData = $connection->getAuthenticationData();
        $parts = explode("\000", base64_decode($data));
        $authenticationData->setUsername($parts[1]);
        $authenticationData->setPassword($parts[2]);
        $connection->checkAuth();
    }

    public function validateIdentity(AuthenticationDataInterface $authenticationData, string $password): bool
    {
        return $password == $authenticationData->getPassword();
    }
}
