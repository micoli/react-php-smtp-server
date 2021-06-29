<?php

declare(strict_types=1);

namespace Micoli\Smtp\Server\Command;

use Micoli\Smtp\Server\Authentication\AuthenticationMethodInterface;
use Micoli\Smtp\Server\SmtpSession\ReturnCode;
use Micoli\Smtp\Server\SmtpSession\SmtpSessionInterface;

final class AuthConnectionCommand implements ConnectionCommandInterface
{
    public function getCommands(): array
    {
        return [Commands::AUTH];
    }

    public function handle(SmtpSessionInterface $connection, string $data): void
    {
        $commandParts = explode(' ', trim($data), 2);
        $data = $commandParts[0];
        $token = array_key_exists(1, $commandParts) ? $commandParts[1] : null;

        $authMethod = $this->getAuthenticationMethod($connection, $data);
        if (null == $authMethod) {
            $connection->sendReply(ReturnCode::_504_COMMAND_PARAMETER_IS_NOT_IMPLEMENTED, 'Unrecognized authentication type.');

            return;
        }
        $connection->setAuthMethod($authMethod);
        $authMethod->init($connection, $token);
    }

    private function getAuthenticationMethod(SmtpSessionInterface $connection, string $methodName): ?AuthenticationMethodInterface
    {
        $authenticationMethodFound = array_filter(
            $connection->getAuthMethods(),
            fn (AuthenticationMethodInterface $method) => $method->getType() === strtoupper($methodName)
        );
        if (empty($authenticationMethodFound)) {
            return null;
        }

        return reset($authenticationMethodFound);
    }
}
