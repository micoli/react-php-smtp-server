<?php

namespace Micoli\Smtp\Server;

use Micoli\Smtp\Server\SmtpSession\SmtpSessionFactoryInterface;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
use React\Socket\ServerInterface;

class Server
{
    private SmtpSessionFactoryInterface $smtpSessionFactory;

    public function __construct(
        SmtpSessionFactoryInterface $smtpSessionFactory,
    ) {
        $this->smtpSessionFactory = $smtpSessionFactory;
    }

    public function listen(ServerInterface $server, LoopInterface $loop): void
    {
        $server->on(
            'connection',
            function (ConnectionInterface $client) use ($loop) {
                $connection = $this->smtpSessionFactory->create(
                    $client,
                    $loop
                );
                $connection->run();
            }
        );
    }
}
