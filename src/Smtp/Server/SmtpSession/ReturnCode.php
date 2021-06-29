<?php

declare(strict_types=1);

namespace Micoli\Smtp\Server\SmtpSession;

final class ReturnCode
{
    public const _220_SERVER_IS_READY = 220;
    public const _221_GOODBYE = 221;
    public const _221_GOODBYE_LABEL = 'Goodbye.';
    public const _235_AUTHENTICATION_SUCCEEDED = 235;
    public const _235_AUTHENTICATION_SUCCEEDED_LABEL = '2.7.0 Authentication successful';
    public const _250_REQUESTED_MAIL_ACTION_OKAY_COMPLETED = 250;

    public const _334_CONTINUE_AUTHENTICATION_REQUEST = 334;
    public const _354_START_ADDING_MAIL_INPUT = 354;

    public const _500_SYNTAX_ERROR = 500;
    public const _504_COMMAND_PARAMETER_IS_NOT_IMPLEMENTED = 504;
    public const _530_AUTHENTICATION_PROBLEM = 530;
    public const _530_AUTHENTICATION_PROBLEM_LABEL = '5.7.0 Authentication required';
    public const _535_AUTHENTICATION_CREDENTIALS_INVALID = 535;
    public const _535_AUTHENTICATION_CREDENTIALS_INVALID_LABEL = 'Authentication credentials invalid';
    public const _550_REQUESTED_ACTION_NOT_TAKEN = 550;
    public const _550_REQUESTED_ACTION_NOT_TAKEN_LABEL = 'Message not accepted';
}
