<?php

/**
 * CAPA Upcoming Notification Template
 *
 * Used when a Corrective Action Plan task is due soon.
 */

return [
    'email' => [
        'subject' => '[UPCOMING] ISO/IEC 27001:2022 CAPA Reminder',
        'body' => <<<TEXT
[UPCOMING] ISO/IEC 27001:2022 CAPA Reminder

Pengingat tindakan perbaikan (CAPA) yang akan segera mendekati batas waktu (deadline):

Kontrol ISO: {control_code} - {control_title}
PIC: {pic}
Batas Waktu: {due_date}
Sisa Waktu: {days_left} hari lagi
Sesi Audit: {session_name}

Tindakan yang Diperlukan:
Mohon persiapkan dan unggah dokumen perbaikan (evidence) sebelum batas waktu berakhir.

Email ini dikirimkan secara otomatis oleh Sistem Audit ISO/IEC 27001:2022.
TEXT,
    ],
];
