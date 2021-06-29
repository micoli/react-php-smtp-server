<?php

namespace Micoli\Smtp\Server\Event;

use Micoli\Smtp\Server\Message\Message;
use Symfony\Contracts\EventDispatcher\Event;

class MessageReceivedEvent extends Event
{
    protected Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function getMessage(): Message
    {
        return $this->message;
    }
}
