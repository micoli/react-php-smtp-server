<?php

declare(strict_types=1);

namespace Micoli\Smtp\Server\SmtpSession;

use DomainException;
use Micoli\Smtp\Server\Command\Commands;

final class StateService
{
    public const STATUS_NEW = 'STATUS_NEW';
    public const STATUS_AUTH = 'STATUS_AUTH';
    public const STATUS_INIT = 'STATUS_INIT';
    public const STATUS_FROM = 'STATUS_FROM';
    public const STATUS_TO = 'STATUS_TO';
    public const STATUS_DATA = 'STATUS_DATA';

    /**
     * This status is used when all mail data has been received and the system is deciding whether to accept or reject.
     */
    public const STATUS_PROCESSING = 'STATUS_PROCESSING';

    /** @var array<string, array<string,string>> */
    private array $states = [
        self::STATUS_NEW => [
            Commands::HELO => Commands::HELO_VERB,
            Commands::EHLO => Commands::EHLO_VERB,
            Commands::QUIT => Commands::QUIT_VERB,
        ],
        self::STATUS_AUTH => [
            Commands::AUTH => Commands::AUTH_VERB,
            Commands::QUIT => Commands::QUIT_VERB,
            Commands::RESET => Commands::RESET_VERB,
            Commands::LOGIN => '',
        ],
        self::STATUS_INIT => [
            Commands::MAIL_FROM => Commands::MAIL_FROM_VERB,
            Commands::QUIT => Commands::QUIT_VERB,
            Commands::RESET => Commands::RESET_VERB,
        ],
        self::STATUS_FROM => [
            Commands::RCPT_TO => Commands::RCPT_TO_VERB,
            Commands::QUIT => Commands::QUIT_VERB,
            Commands::RESET => Commands::RESET_VERB,
        ],
        self::STATUS_TO => [
            Commands::RCPT_TO => Commands::RCPT_TO_VERB,
            Commands::QUIT => Commands::QUIT_VERB,
            Commands::DATA => Commands::DATA_VERB,
            Commands::RESET => Commands::RESET_VERB,
        ],
        self::STATUS_DATA => [
            Commands::LINE => '', // This will match any line.
        ],
        self::STATUS_PROCESSING => [],
    ];

    /** @return array<string,string> */
    public function getCandidates(string $candidate): array
    {
        if (!array_key_exists($candidate, $this->states)) {
            throw new DomainException(sprintf('Unknown candidate state "%s"', $candidate));
        }

        return $this->states[$candidate];
    }
}
