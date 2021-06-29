<?php

declare(strict_types=1);

namespace Micoli\Smtp\Server\Command;

use Micoli\Smtp\Server\SmtpSession\SmtpSessionInterface;

interface ConnectionCommandInterface
{
    /** @return string[] */
    public function getCommands(): array;

    public function handle(SmtpSessionInterface $connection, string $data): void;
}
