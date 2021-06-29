<?php

declare(strict_types=1);

namespace Micoli\Smtp\Server\Command;

use Micoli\Smtp\Server\Authentication\AuthenticationMethodInterface;
use Micoli\Smtp\Server\Event\ConnectionHeloReceivedEvent;
use Micoli\Smtp\Server\Event\Events;
use Micoli\Smtp\Server\SmtpSession\ReturnCode;
use Micoli\Smtp\Server\SmtpSession\SmtpSessionInterface;
use Micoli\Smtp\Server\SmtpSession\StateService;

final class EhloConnectionCommand implements ConnectionCommandInterface
{
    public function getCommands(): array
    {
        return [Commands::HELO, Commands::EHLO];
    }

    public function handle(SmtpSessionInterface $connection, string $data): void
    {
        $messages = [
            "Hello {$connection->getRemoteAddress()}",
        ];

        if (empty($connection->getAuthMethods())) {
            $connection->changeState(StateService::STATUS_INIT);
        } else {
            $connection->changeState(StateService::STATUS_AUTH);
            $messages[] = 'AUTH '.implode(' ', array_map(fn (AuthenticationMethodInterface $method) => $method->getType(), $connection->getAuthMethods()));
        }

        $connection->dispatchEvent(Events::CONNECTION_HELO_RECEIVED, new ConnectionHeloReceivedEvent($connection, trim($data)));

        $connection->sendReply(ReturnCode::_250_REQUESTED_MAIL_ACTION_OKAY_COMPLETED, $messages);
    }
}
