<?php

declare(strict_types=1);

namespace Micoli\Smtp\Server\SmtpSession;

use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;

interface SmtpSessionFactoryInterface
{
    public function create(ConnectionInterface $client, LoopInterface $loop): SmtpSessionInterface;
}
