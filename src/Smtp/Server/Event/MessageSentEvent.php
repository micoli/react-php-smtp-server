<?php

namespace Micoli\Smtp\Server\Event;

use Micoli\Smtp\Server\Sendmail;
use Symfony\Contracts\EventDispatcher\Event;

class MessageSentEvent extends Event
{
    protected Sendmail $sendmail;
    protected string $message;

    public function __construct(Sendmail $sendmail, string $message)
    {
        $this->sendmail = $sendmail;
        $this->message = $message;
    }

    public function getSendmail(): Sendmail
    {
        return $this->sendmail;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
