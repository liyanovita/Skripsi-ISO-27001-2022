<?php

/**
 * CAPA Overdue Notification Template
 *
 * Used when a Corrective Action Plan task is overdue.
 */

return [
    'email' => [
        'subject' => '[OVERDUE] ISO/IEC 27001:2022 CAPA ALERT',
        'body' => <<<TEXT
[OVERDUE] ISO/IEC 27001:2022 CAPA ALERT

Tindakan perbaikan (CAPA) berikut telah melewati batas waktu (deadline):

Kontrol ISO: {control_code} - {control_title}
PIC: {pic}
Batas Waktu: {due_date}
Status: Terlambat {days_overdue} hari!
Sesi Audit: {session_name}

Tindakan yang Diperlukan:
Mohon segera unggah dokumen bukti perbaikan (evidence) ke dalam sistem AuditGuard.

Email ini dikirimkan secara otomatis oleh Sistem Audit ISO/IEC 27001:2022.
TEXT,
    ],
];
