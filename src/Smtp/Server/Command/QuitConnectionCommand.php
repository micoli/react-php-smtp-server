<?php

declare(strict_types=1);

namespace Micoli\Smtp\Server\Command;

use Micoli\Smtp\Server\SmtpSession\ReturnCode;
use Micoli\Smtp\Server\SmtpSession\SmtpSessionInterface;

final class QuitConnectionCommand implements ConnectionCommandInterface
{
    public function getCommands(): array
    {
        return [Commands::QUIT];
    }

    public function handle(SmtpSessionInterface $connection, string $data): void
    {
        $connection->sendReply(ReturnCode::_221_GOODBYE, ReturnCode::_221_GOODBYE_LABEL, true);
    }
}
