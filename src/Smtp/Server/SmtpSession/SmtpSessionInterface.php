<?php

declare(strict_types=1);

namespace Micoli\Smtp\Server\SmtpSession;

use Micoli\Smtp\Server\Authentication\AuthenticationDataInterface;
use Micoli\Smtp\Server\Authentication\AuthenticationMethodInterface;
use Micoli\Smtp\Server\Message\Message;
use Symfony\Contracts\EventDispatcher\Event;

interface SmtpSessionInterface
{
    public function run(): void;

    public function dispatchEvent(string $eventName, Event $event): void;

    public function changeState(string $state): void;

    /**
     * @param string|string[] $message
     */
    public function sendReply(int $code, string | array $message, bool $close = false): void;

    public function reset(string $state = StateService::STATUS_INIT): void;

    public function accept(string $message = 'OK'): void;

    public function reject(int $code = ReturnCode::_550_REQUESTED_ACTION_NOT_TAKEN, string $message = ReturnCode::_550_REQUESTED_ACTION_NOT_TAKEN_LABEL): void;

    public function checkAuth(): bool;

    public function setNextDefaultActionTimer(): void;

    public function delay(int $seconds): bool;

    public function setAuthenticationData(AuthenticationDataInterface $authenticationData): void;

    public function getAuthenticationData(): ?AuthenticationDataInterface;

    public function getAuthMethod(): ?AuthenticationMethodInterface;

    public function getMessage(): Message;

    public function getLogin(): ?string;

    public function getRemoteAddress(): string;

    /** @return AuthenticationMethodInterface[] */
    public function getAuthMethods(): array;

    public function setAuthMethod(AuthenticationMethodInterface $authMethod): void;
}
