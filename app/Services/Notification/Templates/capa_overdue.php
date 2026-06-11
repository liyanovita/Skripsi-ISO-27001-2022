<?php

/**
 * CAPA Overdue Notification Template
 *
 * Used when a Corrective Action Plan task is overdue.
 */

return [
    'telegram' => [
        'subject' => '[OVERDUE] ISO 27001:2022 CAPA ALERT',
        'body' => <<<'MARKDOWN'
*[OVERDUE] ISO 27001:2022 CAPA ALERT*

*Control:* {control_code} - {control_title}
*PIC:* {pic}
*Deadline:* {due_date}
*Status:* Late by {days_overdue} day(s)!
*Session:* {session_name}

*Action Required:* Please submit the corrective evidence immediately.

_This is an automated reminder from ISO 27001:2022 Audit System_
MARKDOWN,
    ],
];
