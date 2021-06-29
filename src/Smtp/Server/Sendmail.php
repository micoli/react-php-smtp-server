<?php

namespace Micoli\Smtp\Server;

use Micoli\Smtp\Server\Event\Events;
use Micoli\Smtp\Server\Event\MessageSentEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Sendmail
{
    protected ?EventDispatcherInterface $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher = null)
    {
        $this->dispatcher = $dispatcher;
    }

    public function run(): bool
    {
        if (0 === ftell(STDIN)) {
            $message = '';

            while (!feof(STDIN)) {
                $message .= fread(STDIN, 1024);
            }

            if (!is_null($this->dispatcher)) {
                $event = new MessageSentEvent($this, $message);
                $this->dispatcher->dispatch($event, Events::MESSAGE_SENT);
            }

            return true;
        }

        return false;
    }
}
