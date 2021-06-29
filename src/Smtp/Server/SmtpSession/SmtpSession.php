<?php

namespace Micoli\Smtp\Server\SmtpSession;

use DomainException;
use Micoli\Smtp\Server\Authentication\AuthenticationDataInterface;
use Micoli\Smtp\Server\Authentication\AuthenticationMethodInterface;
use Micoli\Smtp\Server\Authentication\IdentityValidatorInterface;
use Micoli\Smtp\Server\Event\ConnectionAuthAcceptedEvent;
use Micoli\Smtp\Server\Event\ConnectionAuthRefusedEvent;
use Micoli\Smtp\Server\Event\ConnectionChangeStateEvent;
use Micoli\Smtp\Server\Event\Events;
use Micoli\Smtp\Server\Message\Message;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;

class SmtpSession implements SmtpSessionInterface
{
    public const DELIMITER = "\r\n";

    private string $state = StateService::STATUS_NEW;
    private string $lastCommand = '';
    private bool $acceptByDefault = true;

    /**
     * If there are event listeners, how long will they get to accept or reject a message?
     */
    private int $defaultActionTimeout = 0;

    /**
     * The timer for the default action, canceled in [accept] and [reject].
     */
    private TimerInterface $defaultActionTimer;

    private ?AuthenticationMethodInterface $authMethod;
    private ?AuthenticationDataInterface $authenticationData;
    private IdentityValidatorInterface $identityValidator;
    private EventDispatcherInterface $dispatcher;
    private SmtpSessionConfiguration $connectionConfiguration;

    private RawSmtpSessionConnection $client;
    private LoopInterface $loop;
    private Message $message;
    private StateService $statusService;

    public function __construct(
        RawSmtpSessionConnection $client,
        LoopInterface $loop,
        IdentityValidatorInterface $identityValidator,
        EventDispatcherInterface $dispatcher,
        SmtpSessionConfiguration $connectionConfiguration,
        StateService $statusService
    ) {
        $this->client = $client;
        $this->loop = $loop;
        $this->identityValidator = $identityValidator;
        $this->dispatcher = $dispatcher;
        $this->connectionConfiguration = $connectionConfiguration;
        $this->statusService = $statusService;
    }

    public function run(): void
    {
        $this->reset(StateService::STATUS_NEW);
        $this->handleBanner();

        $this->client->listenSmtpData(fn (string $line) => $this->handleCommand($line));
    }

    public function handleBanner(): void
    {
        if (0 === $this->connectionConfiguration->getBannerDelay()) {
            $this->sendReply(ReturnCode::_220_SERVER_IS_READY, $this->connectionConfiguration->getBanner());

            return;
        }

        $disconnect = function (string $data) {
            $this->client->end("I can break rules too, bye.\n");
        };
        $this->client->on('data', $disconnect);

        $this->loop->addTimer($this->connectionConfiguration->getBannerDelay(), function () use ($disconnect) {
            $this->sendReply(ReturnCode::_220_SERVER_IS_READY, $this->connectionConfiguration->getBanner());
            $this->client->removeListener('data', $disconnect);
        });
    }

    public function dispatchEvent(string $eventName, Event $event): void
    {
        $this->dispatcher->dispatch($event, $eventName);
    }

    public function changeState(string $state): void
    {
        if ($this->state !== $state) {
            $oldState = $this->state;
            $this->state = $state;

            $this->dispatchEvent(Events::CONNECTION_CHANGE_STATE, new ConnectionChangeStateEvent($this, $oldState, $state));
        }
    }

    protected function parseCommand(string &$line): ?string
    {
        $command = null;

        if ($line) {
            foreach ($this->statusService->getCandidates($this->state) as $key => $candidate) {
                if (0 == strncasecmp($candidate, $line, strlen($candidate))) {
                    $line = substr($line, strlen($candidate));
                    $this->lastCommand = $key;
                    $command = $key;

                    break;
                }
            }
        }

        if (!$command && 'Line' == $this->lastCommand) {
            $command = $this->lastCommand;
        }

        return $command;
    }

    protected function handleCommand(string $line): void
    {
        $commandName = $this->parseCommand($line);

        if (null == $commandName) {
            $this->sendReply(ReturnCode::_500_SYNTAX_ERROR, 'Unexpected or unknown command: '.$line);
            $this->sendReply(ReturnCode::_500_SYNTAX_ERROR, $this->statusService->getCandidates($this->state));

            return;
        }

        foreach ($this->connectionConfiguration->getCommands() as $command) {
            if (in_array(strtoupper($commandName), array_map('strtoupper', $command->getCommands()))) {
                $command->handle($this, $line);

                return;
            }
        }
    }

    /**
     * @param string|string[] $message
     */
    public function sendReply(int $code, string | array $message, bool $close = false): void
    {
        $out = '';

        if (is_array($message)) {
            $last = array_pop($message);

            foreach ($message as $line) {
                $out .= "$code-$line".self::DELIMITER;
            }

            $this->client->write($out);
            $message = $last;
        }

        if ($close) {
            $this->client->end("$code $message".self::DELIMITER);

            return;
        }
        $this->client->write("$code $message".self::DELIMITER);
    }

    /**
     * Default action, using timer so that callbacks above can be called asynchronously.
     */
    public function setNextDefaultActionTimer(): void
    {
        $this->defaultActionTimer = $this->loop->addTimer($this->defaultActionTimeout, function () {
            if ($this->acceptByDefault) {
                $this->accept();

                return;
            }
            $this->reject();
        });
    }

    /**
     * By default goes to the initialized state (ie no new EHLO or HELO is required / possible.).
     */
    public function reset(string $state = StateService::STATUS_INIT): void
    {
        $this->state = $state;
        $this->lastCommand = '';
        $this->message = new Message();
        $this->authMethod = null;
        $this->authenticationData = null;
    }

    public function accept(string $message = 'OK'): void
    {
        if (StateService::STATUS_PROCESSING != $this->state) {
            throw new DomainException('SMTP Connection not in a valid state to accept a message.');
        }
        $this->cancelDefaultTimer();
        $this->sendReply(ReturnCode::_250_REQUESTED_MAIL_ACTION_OKAY_COMPLETED, $message);
        $this->reset();
    }

    public function reject(int $code = ReturnCode::_550_REQUESTED_ACTION_NOT_TAKEN, string $message = ReturnCode::_550_REQUESTED_ACTION_NOT_TAKEN_LABEL): void
    {
        if (StateService::STATUS_PROCESSING != $this->state) {
            throw new DomainException('SMTP Connection not in a valid state to reject message.');
        }
        $this->cancelDefaultTimer();
        $this->sendReply($code, $message);
        $this->reset();
    }

    private function cancelDefaultTimer(): void
    {
        $this->loop->cancelTimer($this->defaultActionTimer);
        unset($this->defaultActionTimer);
    }

    public function checkAuth(): bool
    {
        if (!$this->identityValidator->checkAuth($this->authMethod, $this->authenticationData)) {
            $this->sendReply(ReturnCode::_535_AUTHENTICATION_CREDENTIALS_INVALID, ReturnCode::_535_AUTHENTICATION_CREDENTIALS_INVALID_LABEL);

            $this->dispatchEvent(Events::CONNECTION_AUTH_REFUSED, new ConnectionAuthRefusedEvent($this, $this->getAuthenticationData()));

            return false;
        }
        $this->changeState(StateService::STATUS_INIT);
        $this->sendReply(ReturnCode::_235_AUTHENTICATION_SUCCEEDED, ReturnCode::_235_AUTHENTICATION_SUCCEEDED_LABEL);

        $this->dispatchEvent(Events::CONNECTION_AUTH_ACCEPTED, new ConnectionAuthAcceptedEvent($this, $this->getAuthenticationData()));

        return true;
    }

    public function delay(int $seconds): bool
    {
        if (!isset($this->defaultActionTimer)) {
            return false;
        }
        $this->loop->cancelTimer($this->defaultActionTimer);
        unset($this->defaultActionTimer);
        $this->defaultActionTimer = $this->loop->addTimer($seconds, $this->defaultActionTimer->getCallback());

        return true;
    }

    public function setAuthenticationData(AuthenticationDataInterface $authenticationData): void
    {
        $this->authenticationData = $authenticationData;
    }

    public function getAuthenticationData(): ?AuthenticationDataInterface
    {
        return $this->authenticationData;
    }

    public function getAuthMethod(): ?AuthenticationMethodInterface
    {
        return $this->authMethod;
    }

    public function getMessage(): Message
    {
        return $this->message;
    }

    public function getLogin(): ?string
    {
        return $this->authenticationData?->getUsername();
    }

    public function getRemoteAddress(): string
    {
        return $this->client->getRemoteAddress();
    }

    /** @return AuthenticationMethodInterface[] */
    public function getAuthMethods(): array
    {
        return $this->connectionConfiguration->getAuthMethods();
    }

    public function setAuthMethod(AuthenticationMethodInterface $authMethod): void
    {
        $this->authMethod = $authMethod;
    }
}
