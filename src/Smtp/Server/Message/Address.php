<?php

declare(strict_types=1);

namespace Micoli\Smtp\Server\Message;

final class Address
{
    private string $address;
    private ?string $display;

    public function __construct(string $address, ?string $display)
    {
        $this->address = $address;
        $this->display = $display;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getDisplay(): ?string
    {
        return $this->display;
    }
}
