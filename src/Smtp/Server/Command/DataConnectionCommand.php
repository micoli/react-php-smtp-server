<?php

declare(strict_types=1);

namespace Micoli\Smtp\Server\Command;

use Micoli\Smtp\Server\SmtpSession\ReturnCode;
use Micoli\Smtp\Server\SmtpSession\SmtpSessionInterface;
use Micoli\Smtp\Server\SmtpSession\StateService;

final class DataConnectionCommand implements ConnectionCommandInterface
{
    public function getCommands(): array
    {
        return [Commands::DATA];
    }

    public function handle(SmtpSessionInterface $connection, string $data): void
    {
        $connection->changeState(StateService::STATUS_DATA);
        $connection->sendReply(ReturnCode::_354_START_ADDING_MAIL_INPUT, 'Enter message, end with CRLF . CRLF');
    }
}
