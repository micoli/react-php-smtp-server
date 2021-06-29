<?php

namespace Micoli\Smtp\Server\Authentication\Login;

use Micoli\Smtp\Server\Authentication\AuthenticationDataInterface;
use Micoli\Smtp\Server\Authentication\AuthenticationMethodInterface;
use Micoli\Smtp\Server\SmtpSession\ReturnCode;
use Micoli\Smtp\Server\SmtpSession\SmtpSessionInterface;

class LoginAuthenticationMethod implements AuthenticationMethodInterface
{
    public function getType(): string
    {
        return 'LOGIN';
    }

    public function init(SmtpSessionInterface $connection, ?string $data): void
    {
        $connection->setAuthenticationData(new LoginAuthenticationData($connection));
        $connection->sendReply(ReturnCode::_334_CONTINUE_AUTHENTICATION_REQUEST, base64_encode('Username:'));
    }

    public function handleData(SmtpSessionInterface $connection, string $data): void
    {
        $authenticationData = $connection->getAuthenticationData();
        if (!$authenticationData->getUsername()) {
            $authenticationData->setUsername(base64_decode($data));
            $connection->sendReply(ReturnCode::_334_CONTINUE_AUTHENTICATION_REQUEST, base64_encode('Password:'));

            return;
        }
        $authenticationData->setPassword(base64_decode($data));
        $connection->checkAuth();
    }

    public function validateIdentity(AuthenticationDataInterface $authenticationData, string $password): bool
    {
        return $password == $authenticationData->getPassword();
    }
}
