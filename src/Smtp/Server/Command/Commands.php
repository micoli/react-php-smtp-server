<?php

declare(strict_types=1);

namespace Micoli\Smtp\Server\Command;

final class Commands
{
    public const HELO = 'Helo';
    public const HELO_VERB = 'HELO';

    public const EHLO = 'Ehlo';
    public const EHLO_VERB = 'EHLO';

    public const QUIT = 'Quit';
    public const QUIT_VERB = 'QUIT';

    public const AUTH = 'Auth';
    public const AUTH_VERB = 'AUTH';

    public const MAIL_FROM = 'MailFrom';
    public const MAIL_FROM_VERB = 'MAIL FROM';

    public const RCPT_TO = 'RcptTo';
    public const RCPT_TO_VERB = 'RCPT TO';

    public const DATA = 'Data';
    public const DATA_VERB = 'DATA';

    public const LOGIN = 'Login';
    public const LOGIN_VERB = 'LOGIN';

    public const RESET = 'Reset';
    public const RESET_VERB = 'RSET';

    public const LINE = 'Line';
}
