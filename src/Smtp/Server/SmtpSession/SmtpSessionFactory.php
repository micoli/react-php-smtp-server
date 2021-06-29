<?php

namespace Micoli\Smtp\Server\SmtpSession;

use Micoli\Smtp\Server\Authentication\IdentityValidatorInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SmtpSessionFactory implements SmtpSessionFactoryInterface
{
    private EventDispatcherInterface $eventDispatcher;
    private IdentityValidatorInterface $identityValidator;
    private SmtpSessionConfiguration $connectionConfiguration;
    private StateService $statusService;
    private LoggerInterface $logger;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        IdentityValidatorInterface $identityValidator,
        SmtpSessionConfiguration $connectionConfiguration,
        StateService $statusService,
        LoggerInterface $logger
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->identityValidator = $identityValidator;
        $this->connectionConfiguration = $connectionConfiguration;
        $this->statusService = $statusService;
        $this->logger = $logger;
    }

    public function create(
        ConnectionInterface $client,
        LoopInterface $loop
    ): SmtpSessionInterface {
        return new SmtpSession(
            new RawSmtpSessionConnection(
                $client,
                $this->logger,
            ),
            $loop,
            $this->identityValidator,
            $this->eventDispatcher,
            $this->connectionConfiguration,
            $this->statusService
        );
    }
}
