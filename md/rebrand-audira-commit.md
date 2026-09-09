# Commit: Rebrand AuditGuard → Audira

Status: **SELESAI — commit sudah dibuat dan di-push.**
Tanggal: 2026-08-18

## Ringkasan

Seluruh perubahan rebrand (logo baru + penggantian teks "AuditGuard" → "Audira" di seluruh
aplikasi) di-commit dan di-push sebagai satu commit terpisah dari pekerjaan lain yang sedang
berjalan di sesi yang sama (fitur translate AI, evidence extraction, perbaikan hash-guard,
perbaikan sidebar-collapse).

## Detail Commit

- **Branch**: `feat/rebrand-audira-branding` (dibuat dari `main`, sudah di-push ke `origin`)
- **Commit hash**: `f1ff6a0`
- **Commit message**:
  ```
  feat: rebrand branding from AuditGuard to Audira

  - Add new Audira logo asset and swap it in across the sidebar (user +
    admin layouts), auth pages, PDF exports, and transactional emails
  - Replace remaining AuditGuard text references with Audira in page
    titles, notifications, export filenames, and Indonesian translations
  - Resize logo containers to fit the new wordmark's aspect ratio and
    drop the now-redundant "Audit"/"Guard" text lockup next to it
  ```
- **PR (belum dibuat, tinggal klik kalau mau)**:
  https://github.com/liyanovita/Skripsi-ISO-27001-2022/pull/new/feat/rebrand-audira-branding

## File yang Ikut Commit Ini (25 file)

Logo asset baru:
- `public/images/logo-audira.png`

Layout & halaman (logo + teks brand):
- `resources/views/layouts/app.blade.php`, `layouts/admin.blade.php`
- `resources/views/auth/login.blade.php`, `forgot-password.blade.php`, `reset-password.blade.php`
- `resources/views/sessions/index.blade.php`
- `resources/views/admin/notifications/index.blade.php`

Template PDF (logo + footer teks):
- `resources/views/admin/reports/pdf_template.blade.php`
- `resources/views/pages/reports/pdf_template.blade.php`
- `resources/views/pages/kb/pdf.blade.php`
- `resources/views/pages/workspace/soa_pdf.blade.php`

Email:
- `resources/views/emails/notification.blade.php`, `reset-password.blade.php`, `task-assigned.blade.php`

Backend (teks brand di kode: nama export, notifikasi, doc comment):
- `app/Http/Controllers/Assessment/SessionController.php`
- `app/Mail/TaskAssignedMail.php`
- `app/Models/User.php`
- `app/Notifications/AuditSessionAssignedNotification.php`, `CorrectiveActionRequiredNotification.php`, `ResetPasswordNotification.php`
- `app/Services/Assessment/SessionService.php`
- `app/Services/Notification/Templates/capa_overdue.php`

Terjemahan:
- `lang/id.json` (2 key: "Make sure the file was exported from..." dan "This email was sent automatically by...")

Sengaja **tidak** ikut commit ini (atas pilihan user): folder `update/` (file logo sumber yang
diberikan user) dan folder `md/` (catatan investigasi pribadi ini).

## Catatan Teknis: Pemisahan Perubahan yang Bercampur

2 file (`layouts/app.blade.php` dan `layouts/admin.blade.php`) ternyata isinya bercampur antara
rebrand DAN perbaikan bug sidebar-collapse (dikerjakan di sesi yang sama, di baris yang sama
persis — blok header logo). Supaya commit ini murni rebrand saja, dilakukan langkah manual:

1. Backup isi file saat ini (rebrand + fix sidebar) ke folder scratchpad.
2. `git checkout HEAD -- <file>` — reset file ke versi sebelum sesi ini (pre-rebrand).
3. Terapkan ulang HANYA 3 perubahan rebrand (title tag, blok logo header, heading welcome-guide)
   secara manual persis seperti sebelumnya.
4. `git add <file>` — stage versi rebrand-only ini.
5. Kembalikan isi file dari backup (rebrand + fix sidebar lengkap) ke working tree.

Hasilnya: index (staged) berisi rebrand-only, working tree tetap utuh dengan perbaikan
sidebar-collapse yang masih menunggu commit terpisah. Pola yang sama diterapkan untuk
`lang/id.json` (dipisah pakai `git apply --cached` dengan patch parsial, karena isinya campur
2 key rebrand + 19 key baru dari fitur evidence extraction/translate).

## Yang Masih Belum Di-commit (working tree, di branch `feat/rebrand-audira-branding`)

Setelah commit ini, repo tetap berada di branch `feat/rebrand-audira-branding` (bukan pindah balik
ke `main`), supaya tidak ada risiko konflik saat checkout dengan perubahan yang belum di-commit.
Pekerjaan lain dari sesi ini masih utuh sebagai uncommitted changes, menunggu commit/branch
terpisah kalau diminta:

- Perbaikan tampilan sidebar saat collapsed (logo overflow) — `layouts/app.blade.php` & `admin.blade.php`
- Fitur translate AI otomatis mengikuti bahasa (bilingual generation + lazy translate) — banyak file
- Fitur evidence extraction (baca dokumen via OCR/AI) — migrations, `ResultService`, n8n workflows
- Perbaikan bug hash-guard "No Data Change" yang salah blokir Regenerate AI — `ResultService.php`
- Folder `n8n/` (3 workflow yang diedit + 1 workflow baru `translate-content.json`), masih untracked
