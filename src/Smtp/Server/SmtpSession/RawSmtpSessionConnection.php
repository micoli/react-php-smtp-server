<?php

declare(strict_types=1);

namespace Micoli\Smtp\Server\SmtpSession;

use Psr\Log\LoggerInterface;
use React\Socket\ConnectionInterface;

final class RawSmtpSessionConnection
{
    public const DELIMITER = "\r\n";

    private ConnectionInterface $connection;
    private string $lineBuffer = '';

    /** @var callable */
    private $commandHandler;
    private LoggerInterface $logger;

    public function __construct(ConnectionInterface $connection, LoggerInterface $logger)
    {
        $this->connection = $connection;
        $this->logger = $logger;
    }

    public function on(string $eventname, callable $listener): void
    {
        $this->connection->on($eventname, $listener);
    }

    public function removeListener(string $eventname, callable $listener): void
    {
        $this->connection->removeListener($eventname, $listener);
    }

    public function end(?string $data = null): void
    {
        $this->connection->end($data);
    }

    public function write(string $data): void
    {
        $this->logger->debug('SERVER -> ['.trim($data).']');
        $this->connection->write($data);
    }

    public function getRemoteAddress(): string
    {
        $address = $this->connection->getRemoteAddress();
        $semicolonPosition = strrpos($address, ':');
        if (false === $semicolonPosition) {
            return trim($address);
        }

        return trim(substr($address, 0, $semicolonPosition), '[]');
    }

    public function listenSmtpData(callable $commandHandler): void
    {
        $this->commandHandler = $commandHandler;
        $this->connection->on('data', fn (string $line) => $this->handleRawData($line));
    }

    /**
     * We read until we find an end of line sequence for SMTP.
     * http://www.jebriggs.com/blog/2010/07/smtp-maximum-line-lengths/.
     */
    public function handleRawData(string $data): void
    {
        // Socket is raw, not using fread as it's interceptable by filters
        // See issues #192, #209, and #240
        //$data = stream_socket_recvfrom($stream, $this->bufferSize);;

        //$limit = StateService::STATUS_DATA == $this->state ? 1000 : 512;
        if ('' !== $data && false !== $data) {
            $this->lineBuffer .= $data;
            //if (strlen($data) > $limit) {
            //    $this->sendReply(500, 'Line length limit exceeded.'.strlen($this->lineBuffer));
            //    $this->lineBuffer = '';
            //}

            while (false !== $pos = strpos($this->lineBuffer, self::DELIMITER)) {
                $line = substr($this->lineBuffer, 0, $pos);
                $this->lineBuffer = substr($this->lineBuffer, $pos + strlen(self::DELIMITER));
                $this->logger->debug('CLIENT <- ['.trim($line).']');
                ($this->commandHandler)($line);
            }
        }

        if ('' === $data || false == $data) {
            $this->end();
        }
    }
}
