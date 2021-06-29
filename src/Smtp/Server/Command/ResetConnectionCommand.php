<?php

declare(strict_types=1);

namespace Micoli\Smtp\Server\Command;

use Micoli\Smtp\Server\SmtpSession\ReturnCode;
use Micoli\Smtp\Server\SmtpSession\SmtpSessionInterface;

final class ResetConnectionCommand implements ConnectionCommandInterface
{
    public function getCommands(): array
    {
        return [Commands::RESET];
    }

    public function handle(SmtpSessionInterface $connection, string $data): void
    {
        $connection->reset();
        $connection->sendReply(ReturnCode::_250_REQUESTED_MAIL_ACTION_OKAY_COMPLETED, 'Reset OK');
    }
}
