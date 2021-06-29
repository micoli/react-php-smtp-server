<?php

namespace Micoli\Smtp\Server\Event;

final class Events
{
    public const CONNECTION_CHANGE_STATE = 'smtp_server.connection.change_state';

    public const CONNECTION_HELO_RECEIVED = 'smtp_server.connection.helo_received';

    public const CONNECTION_FROM_RECEIVED = 'smtp_server.connection.from_received';

    public const CONNECTION_RCPT_RECEIVED = 'smtp_server.connection.rcpt_received';

    public const CONNECTION_LINE_RECEIVED = 'smtp_server.connection.line_received';

    public const CONNECTION_AUTH_ACCEPTED = 'smtp_server.connection.auth_accepted';

    public const CONNECTION_AUTH_REFUSED = 'smtp_server.connection.auth_refused';

    public const MESSAGE_SENT = 'smtp_server.message.sent';

    public const MESSAGE_RECEIVED = 'smtp_server.message.received';
}
