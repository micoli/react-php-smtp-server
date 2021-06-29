<?php

declare(strict_types=1);

namespace Micoli\Smtp\Server\Command;

use Micoli\Smtp\Server\Event\ConnectionLineReceivedEvent;
use Micoli\Smtp\Server\Event\Events;
use Micoli\Smtp\Server\Event\MessageReceivedEvent;
use Micoli\Smtp\Server\SmtpSession\SmtpSessionInterface;
use Micoli\Smtp\Server\SmtpSession\StateService;

final class LineConnectionCommand implements ConnectionCommandInterface
{
    public function getCommands(): array
    {
        return [Commands::LINE];
    }

    public function handle(SmtpSessionInterface $connection, string $data): void
    {
        if ('.' === $data) {
            $connection->changeState(StateService::STATUS_PROCESSING);
            $connection->setNextDefaultActionTimer();
            $connection->dispatchEvent(Events::MESSAGE_RECEIVED, new MessageReceivedEvent($connection->getMessage()));

            return;
        }

        $connection->getMessage()->appendRawContent($data);
        $connection->dispatchEvent(Events::CONNECTION_LINE_RECEIVED, new ConnectionLineReceivedEvent($connection, $data));
    }
}
