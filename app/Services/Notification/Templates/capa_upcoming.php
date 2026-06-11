<?php

/**
 * CAPA Upcoming Notification Template
 *
 * Used when a Corrective Action Plan task is due soon.
 */

return [
    'telegram' => [
        'subject' => '[UPCOMING] ISO 27001:2022 CAPA Reminder',
        'body' => <<<'MARKDOWN'
*[UPCOMING] ISO 27001:2022 CAPA Reminder*

*Control:* {control_code} - {control_title}
*PIC:* {pic}
*Deadline:* {due_date}
*Time Left:* {days_left} day(s) remaining
*Session:* {session_name}

*Action Required:* Please prepare the corrective action documents.

_This is an automated reminder from ISO 27001:2022 Audit System_
MARKDOWN,
    ],
];
