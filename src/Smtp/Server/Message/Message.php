<?php

namespace Micoli\Smtp\Server\Message;

use DateTimeImmutable;
use Micoli\Smtp\Server\SmtpSession\SmtpSession;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;

class Message
{
    private UuidV4 $id;
    private DateTimeImmutable $date;
    private ?Address $from;
    private string $rawContent = '';

    /** @var Address[] */
    private array $toRecipients = [];

    /** @var Address[] */
    private array $ccRecipients = [];

    /** @var Address[] */
    private array $bccRecipients = [];

    public function __construct(?UuidV4 $id = null)
    {
        $this->id = null === $id ? Uuid::v4() : $id;
        $this->date = new DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function setFrom(?Address $from): self
    {
        $this->from = $from;

        return $this;
    }

    public function getFrom(): Address
    {
        return $this->from;
    }

    public function addToRecipient(Address ...$addresses): self
    {
        $this->toRecipients = array_merge($this->toRecipients, $addresses);

        return $this;
    }

    /** @return Address[] */
    public function getToRecipients(): array
    {
        return $this->toRecipients;
    }

    public function addCcRecipient(Address ...$addresses): self
    {
        $this->ccRecipients = array_merge($this->ccRecipients, $addresses);

        return $this;
    }

    /** @return Address[] */
    public function getCcRecipients(): array
    {
        return $this->ccRecipients;
    }

    public function addBccRecipient(Address ...$addresses): self
    {
        $this->bccRecipients = array_merge($this->bccRecipients, $addresses);

        return $this;
    }

    /** @return Address[] */
    public function getBccRecipients(): array
    {
        return $this->bccRecipients;
    }

    public function getRawContent(): string
    {
        return $this->rawContent;
    }

    public function appendRawContent(string $content): self
    {
        $this->rawContent .= $content.SmtpSession::DELIMITER;

        return $this;
    }
}
