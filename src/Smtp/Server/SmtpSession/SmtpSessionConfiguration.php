<?php

namespace Micoli\Smtp\Server\SmtpSession;

use Micoli\Smtp\Server\Authentication\AuthenticationMethodInterface;
use Micoli\Smtp\Server\Command\ConnectionCommandInterface;

class SmtpSessionConfiguration
{
    private int $recipientLimit;
    private int $bannerDelay;

    /** @var array<int, ConnectionCommandInterface> */
    private array $commands;

    /** @var array<int, AuthenticationMethodInterface> */
    public array $authMethods = [];
    private string $banner;

    /**
     * @param array<int, ConnectionCommandInterface>    $commands
     * @param array<int, AuthenticationMethodInterface> $authMethods
     */
    public function __construct(
        int $recipientLimit,
        int $bannerDelay,
        string $banner,
        array $authMethods,
        array $commands,
    ) {
        $this->recipientLimit = $recipientLimit;
        $this->bannerDelay = $bannerDelay;
        $this->authMethods = $authMethods;
        $this->commands = $commands;
        $this->banner = $banner;
    }

    public function getRecipientLimit(): int
    {
        return $this->recipientLimit;
    }

    public function getBannerDelay(): int
    {
        return $this->bannerDelay;
    }

    /** @return array<int, AuthenticationMethodInterface> */
    public function getAuthMethods(): array
    {
        return $this->authMethods;
    }

    /** @return array<int, ConnectionCommandInterface> */
    public function getCommands(): array
    {
        return $this->commands;
    }

    public function getBanner(): string
    {
        return $this->banner;
    }
}
