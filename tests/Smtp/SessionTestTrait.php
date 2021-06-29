<?php

declare(strict_types=1);

namespace Micoli\Tests\Smtp;

use Micoli\Smtp\Server\Authentication\AuthenticationDataInterface;
use Micoli\Smtp\Server\Authentication\AuthenticationMethodInterface;
use Micoli\Smtp\Server\Authentication\IdentityValidator;
use Micoli\Smtp\Server\Authentication\IdentityValidatorInterface;
use Micoli\Smtp\Server\Server;
use Micoli\Smtp\Server\SmtpSession\SmtpSessionFactory;
use Micoli\Smtp\Server\SmtpSession\StateService;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\TestLogger;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
use React\Socket\ServerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Micoli\Smtp\Server\SmtpSession\SmtpSessionConfiguration;
use Symfony\Component\HttpKernel\Log\Logger;
use Micoli\Smtp\Server\Authentication;
use Micoli\Smtp\Server\Command;

trait SessionTestTrait
{
    protected Server $server;
    protected ConnectionInterface|MockObject $connection;
    protected ServerInterface $socket;
    protected LoopInterface $loop;
    protected array $socketDataReceived;
    protected array $eventCalls = [];

    private EventDispatcherInterface $dispatcher;

    protected function resetExpectedEvents(string $eventName): void
    {
        $this->eventCalls[$eventName] = [];
    }

    protected function traceEvent(string $eventName, string $eventClassname): void
    {
        $this->resetExpectedEvents($eventName);

        $this->dispatcher->addListener($eventName, function ($event) use ($eventName) {
            $this->eventCalls[$eventName][] = $event;
        }, -1000000);
    }

    protected function setUpSession(): void
    {
        $this->dispatcher = new EventDispatcher();
        $logger = new TestLogger();
        $this->server = new Server(
            new SmtpSessionFactory(
                $this->dispatcher,
                new class($logger) implements IdentityValidatorInterface {
                    private LoggerInterface $logger;

                    public function __construct(LoggerInterface $logger)
                    {
                        $this->logger = $logger;
                    }

                    public function checkAuth(AuthenticationMethodInterface $method, ?AuthenticationDataInterface $authenticationData): bool
                    {
                        $this->logger->debug(sprintf(
                            'User AutoValidator "%s" [%s] [%s] [%s]' . PHP_EOL,
                            $method->getType(),
                            $authenticationData?->getUsername(),
                            $authenticationData?->getPassword(),
                            null === $authenticationData ? 'none' : ($method->validateIdentity($authenticationData, 'password1') ? '1' : '0')
                        ));
                        return $authenticationData?->getUsername() === 'user1';
                    }
                },
                new SmtpSessionConfiguration(
                    100,
                    0,
                    'Welcome to ReactPHP SMTP Server',
                    [
                        new Authentication\CramMd5\CramMd5AuthenticationMethod(
                            new Authentication\CramMd5\HmacMd5(),
                            'hkhkjhkjhjkh.com'
                        ),
                        new Authentication\Login\LoginAuthenticationMethod(),
                        new Authentication\Plain\PlainAuthenticationMethod(),
                    ],
                    [
                        new Command\AuthConnectionCommand(),
                        new Command\DataConnectionCommand(),
                        new Command\EhloConnectionCommand(),
                        new Command\LineConnectionCommand(),
                        new Command\LoginConnectionCommand(),
                        new Command\MailFromConnectionCommand(),
                        new Command\QuitConnectionCommand(),
                        new Command\RcptToConnectionCommand(),
                        new Command\ResetConnectionCommand(),
                    ]
                ),
                new StateService(),
                $logger
            )
        );
        $this->connection = $this->getMockBuilder('React\Socket\Connection')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'write',
                    'end',
                    'close',
                    'pause',
                    'resume',
                    'isReadable',
                    'isWritable',
                    'getRemoteAddress',
                    'getLocalAddress',
                    'pipe',
                ]
            )
            ->getMock();

        $this->connection->method('isWritable')->willReturn(true);
        $this->connection->method('isReadable')->willReturn(true);
        $this->connection->method('getRemoteAddress')->willReturn('127.0.0.1:8025');

        $this->socket = new SocketServerStub();
        $this->socketDataReceived = [];
        $this->connection
            ->method('write')
            ->will($this->returnCallback(function (string $data) {
                $this->socketDataReceived[] = $data;

                return true;
            }));
    }

    protected function sendCommands(
        string ...$commands
    ): void {
        foreach ($commands as $command) {
            $this->connection->emit('data', ["$command\r\n"]);
        }
    }

    protected function startServer(): void
    {
        $this->loop = Factory::create();
        $this->server->listen($this->socket, $this->loop);

        $this->socket->emit('connection', [$this->connection]);
    }
}
