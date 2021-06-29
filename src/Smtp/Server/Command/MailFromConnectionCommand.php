<?php

declare(strict_types=1);

namespace Micoli\Smtp\Server\Command;

use Micoli\Smtp\Server\Event\ConnectionFromReceivedEvent;
use Micoli\Smtp\Server\Event\Events;
use Micoli\Smtp\Server\Message\Address;
use Micoli\Smtp\Server\SmtpSession\ReturnCode;
use Micoli\Smtp\Server\SmtpSession\SmtpSessionInterface;
use Micoli\Smtp\Server\SmtpSession\StateService;

final class MailFromConnectionCommand implements ConnectionCommandInterface
{
    public function getCommands(): array
    {
        return [Commands::MAIL_FROM];
    }

    public function handle(SmtpSessionInterface $connection, string $data): void
    {
        if (1 !== preg_match('/:\s*<(?<email>.*)>( .*)?/', $data, $matches)) {
            $connection->sendReply(ReturnCode::_500_SYNTAX_ERROR, 'Invalid mail argument');

            return;
        }
        if (null === $connection->getLogin() && !empty($connection->getAuthMethods())) {
            $connection->sendReply(ReturnCode::_530_AUTHENTICATION_PROBLEM, ReturnCode::_530_AUTHENTICATION_PROBLEM_LABEL);
            $connection->reset();

            return;
        }

        $connection->changeState(StateService::STATUS_FROM);
        $connection->getMessage()->setFrom(new Address($matches['email'], null));
        $connection->sendReply(ReturnCode::_250_REQUESTED_MAIL_ACTION_OKAY_COMPLETED, 'MAIL OK');

        $connection->dispatchEvent(Events::CONNECTION_FROM_RECEIVED, new ConnectionFromReceivedEvent($connection, $matches['email'], null));
    }
}
