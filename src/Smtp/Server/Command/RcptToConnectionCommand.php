<?php

declare(strict_types=1);

namespace Micoli\Smtp\Server\Command;

use Micoli\Smtp\Server\Event\ConnectionRcptReceivedEvent;
use Micoli\Smtp\Server\Event\Events;
use Micoli\Smtp\Server\Message\Address;
use Micoli\Smtp\Server\SmtpSession\ReturnCode;
use Micoli\Smtp\Server\SmtpSession\SmtpSessionInterface;
use Micoli\Smtp\Server\SmtpSession\StateService;

final class RcptToConnectionCommand implements ConnectionCommandInterface
{
    public function getCommands(): array
    {
        return [Commands::RCPT_TO];
    }

    public function handle(SmtpSessionInterface $connection, string $data): void
    {
        if (1 == !preg_match('/:\s*(?<name>.*?)?<(?<email>.*)>( .*)?/', $data, $matches)) {
            $connection->sendReply(ReturnCode::_500_SYNTAX_ERROR, 'Invalid RCPT TO argument.');

            return;
        }

        // Always set to STATUS_TO, since this command might occur multiple times.
        $connection->changeState(StateService::STATUS_TO);
        $connection->getMessage()->addToRecipient(new Address($matches['email'], $matches['name']));
        $connection->sendReply(ReturnCode::_250_REQUESTED_MAIL_ACTION_OKAY_COMPLETED, 'Accepted');

        $connection->dispatchEvent(Events::CONNECTION_RCPT_RECEIVED, new ConnectionRcptReceivedEvent($connection, $matches['email'], $matches['name']));
    }
}
