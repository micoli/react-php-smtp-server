<?php

declare(strict_types=1);

namespace Micoli\Smtp\Server\Command;

use Micoli\Smtp\Server\SmtpSession\ReturnCode;
use Micoli\Smtp\Server\SmtpSession\SmtpSessionInterface;

final class LoginConnectionCommand implements ConnectionCommandInterface
{
    public function getCommands(): array
    {
        return [Commands::LOGIN];
    }

    public function handle(SmtpSessionInterface $connection, string $data): void
    {
        if (!$connection->getAuthMethod()) {
            $connection->sendReply(ReturnCode::_530_AUTHENTICATION_PROBLEM, ReturnCode::_530_AUTHENTICATION_PROBLEM_LABEL);
            $connection->reset();

            return;
        }

        $connection->getAuthMethod()->handleData($connection, $data);
    }
}
