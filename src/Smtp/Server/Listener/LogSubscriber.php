<?php

namespace Micoli\Smtp\Server\Listener;

use Micoli\Smtp\Server\Event\ConnectionAuthAcceptedEvent;
use Micoli\Smtp\Server\Event\ConnectionAuthRefusedEvent;
use Micoli\Smtp\Server\Event\ConnectionChangeStateEvent;
use Micoli\Smtp\Server\Event\ConnectionFromReceivedEvent;
use Micoli\Smtp\Server\Event\ConnectionHeloReceivedEvent;
use Micoli\Smtp\Server\Event\ConnectionLineReceivedEvent;
use Micoli\Smtp\Server\Event\ConnectionRcptReceivedEvent;
use Micoli\Smtp\Server\Event\Events;
use Micoli\Smtp\Server\Event\MessageReceivedEvent;
use Micoli\Smtp\Server\Event\MessageSentEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LogSubscriber implements EventSubscriberInterface
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return array<string,string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            Events::CONNECTION_CHANGE_STATE => 'onConnectionChangeState',
            Events::CONNECTION_HELO_RECEIVED => 'onConnectionHeloReceived',
            Events::CONNECTION_AUTH_ACCEPTED => 'onConnectionAuthAccepted',
            Events::CONNECTION_AUTH_REFUSED => 'onConnectionAuthRefused',
            Events::CONNECTION_FROM_RECEIVED => 'onConnectionFromReceived',
            Events::CONNECTION_RCPT_RECEIVED => 'onConnectionRcptReceived',
            Events::CONNECTION_LINE_RECEIVED => 'onConnectionLineReceived',
            Events::MESSAGE_RECEIVED => 'onMessageReceived',
            Events::MESSAGE_SENT => 'onMessageSent',
        ];
    }

    public function onConnectionChangeState(ConnectionChangeStateEvent $event): void
    {
        $this->logger->debug('State changed from '.$event->getOldState().' to '.$event->getNewState());
    }

    public function onConnectionHeloReceived(ConnectionHeloReceivedEvent $event): void
    {
        $this->logger->debug('Domain: '.$event->getDomain());
    }

    public function onConnectionFromReceived(ConnectionFromReceivedEvent $event): void
    {
        $mail = $event->getMail();
        $name = $event->getName() ?: $mail;
        $this->logger->debug('From: '.$name.' <'.$mail.'>');
    }

    public function onConnectionRcptReceived(ConnectionRcptReceivedEvent $event): void
    {
        $mail = $event->getMail();
        $name = $event->getName() ?: $mail;
        $this->logger->debug('Rcpt: '.$name.' <'.$mail.'>');
    }

    public function onConnectionLineReceived(ConnectionLineReceivedEvent $event): void
    {
        $this->logger->debug('Line: '.$event->getLine());
    }

    public function onConnectionAuthAccepted(ConnectionAuthAcceptedEvent $event): void
    {
        $this->logger->debug('Auth used: '.get_class($event->getAuthenticationData()));
        $this->logger->info('User granted: '.$event->getAuthenticationData()->getUsername());
    }

    public function onConnectionAuthRefused(ConnectionAuthRefusedEvent $event): void
    {
        $this->logger->debug('Auth used: '.get_class($event->getAuthenticationData()));
        $this->logger->error('User refused: '.$event->getAuthenticationData()->getUsername());
    }

    public function onMessageReceived(MessageReceivedEvent $event): void
    {
        $this->logger->info('Message received via smtp: '.$event->getMessage()->getRawContent());
    }

    public function onMessageSent(MessageSentEvent $event): void
    {
        $this->logger->info('Message sent via sendmail: '.strlen($event->getMessage()).' bytes');
    }
}
