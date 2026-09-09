# Investigasi: Evidence Upload Alert "Success" Padahal Gagal (500)

Status: **SELESAI — fix alert sudah diimplementasikan.**
Terakhir dibahas: 2026-08-14 (fix diterapkan)

## Ringkasan Masalah

Di halaman Sesi Audit (`results.edit` / `resources/views/sessions/_item_list.blade.php`), bagian
**Evidence Repository**: upload pertama kali sukses (200, file tersimpan di storage & kolom
`evidence_file` di tabel `assessment_results`). Setelah file dihapus (delete sukses, 200), upload
ulang (file sama atau beda) selalu menampilkan alert **"Artifact uploaded!" (success)**, tapi:
- Response server sebenarnya **HTTP 500**.
- File **tidak** tersimpan di storage maupun di kolom `evidence_file`.

## Root Cause (terkonfirmasi via `storage/logs/laravel.log`)

Ada **dua bug yang bertumpuk**:

### 1. Business-rule guard di server (perilaku yang DIPERTAHANKAN, bukan bug — lihat Keputusan)
`app/Services/Assessment/ResultService.php:71-75` (method `updateResult()`):
```php
$hasAnyAnswer = array_key_exists('maturity_rating', $data) || $answeredCount > 0;
if (!$hasAnyAnswer && $result->status !== 'completed') {
    throw new \Exception('Please select a score before saving this control.');
}
```
Upload evidence memakai endpoint yang SAMA dengan simpan rating (`POST /results/update/{id}`,
`ResultController::update()`). Guard ini menolak **apa pun** disimpan — termasuk evidence-only
upload — kalau kontrol belum pernah diberi `maturity_rating`/`answers` dan `status` belum
`completed`. `handleEvidenceUpload()` (baris 92, setelah guard) tidak pernah tereksekusi kalau
guard ini melempar exception — makanya file tidak pernah sampai ke storage/DB.

Dikonfirmasi lewat log: SEMUA error 500 di `storage/logs/laravel.log` persis berbunyi
`"Please select a score before saving this control."` (exception dilempar dari
`ApiException::internalError()` di `app/Exceptions/ApiException.php:106-109`, hardcode status 500).

Dikonfirmasi juga via reproduksi manual: data seed (`database/seeders/DummyAuditSessionSeeder.php:277-300`)
selalu mengisi `maturity_rating` & `answers` sejak awal (jadi guard tidak pernah aktif di sesi
demo) — tapi sesi baru yang dibuat user (kolom default `maturity_rating=null`,
`status='not_started'`) langsung kena guard ini kalau evidence diupload sebelum kontrol dirating.

### 2. Alert sukses tidak dicek dari response server (BUG — perlu diperbaiki)
`resources/views/sessions/_item_list.blade.php:545`:
```html
<input type="file" name="evidence_file"
  @change="submitForm().then(() => { $el.value = ''; ...notify 'Artifact uploaded!'... })">
```
Toast "Artifact uploaded!" terpasang di `.then()` milik Promise `submitForm()`
(`_item_list.blade.php:170-219`). `fetch()` di JS **hanya reject kalau ada masalah jaringan**,
BUKAN kalau server balas HTTP error (403/409/422/500). Jadi walau server menolak (500) dan
mengembalikan pesan error asli (`data.success === false`, `data.message === "Please select a
score before saving this control."`), Promise tetap **resolve**, `.then()` tetap jalan, toast
sukses tetap tampil — padahal `this.evidenceFiles` (yang menentukan file apa yang tampil di UI)
hanya di-update di dalam blok `if (data.success) {...}` (baris 190-217) yang di-skip karena
`data.success` false.

## Keputusan yang Sudah Disepakati dengan User

- **Aturan bisnis #1 (guard "harus rating dulu") DIPERTAHANKAN.** User mengonfirmasi ini memang
  perilaku yang diinginkan: evidence memang seharusnya tidak boleh diunggah sebelum kontrol
  diberi rating. **Tidak perlu diubah.**
- **Solusi yang perlu dikerjakan hanyalah bug #2**: perbaiki alert supaya tidak menyesatkan —
  toast sukses harus dicek dari `data.success`/HTTP status, dan kalau gagal, tampilkan pesan
  error asli dari server (mis. via `Swal.fire` seperti pola yang sudah dipakai di `generateAi()`
  untuk kasus `NO_DATA_CHANGE`, lihat baris ~297-329) alih-alih toast sukses palsu.
- **Belum ada kode yang diubah sama sekali** — user secara eksplisit minta jangan ubah kode dulu,
  investigasi ini murni penjelasan. Implementasi fix alert masih PENDING, menunggu user siap.

## Topik Lain yang Sudah Dibahas (bukan bug, sekadar penjelasan)

### Field "User Findings" (kolom kiri, sebelah Evidence Repository)
- Memetakan ke kolom `notes` di `assessment_results` (text, maks 5000 karakter,
  `app/Http/Requests/Assessment/UpdateResultRequest.php:28`).
- Auto-save via debounce 2 detik (`@input.debounce.2000ms="submitForm()"`,
  `_item_list.blade.php:533`) — beda mekanisme dari evidence file.
- Fungsi: narasi/temuan tertulis manusia (beda dari evidence = bukti file). Dikirim ke AI
  (n8n) baik di level per-kontrol (`ResultService::sendToN8n()`, `ResultService.php:391`)
  maupun level sesi (`AiSummaryService`, `AiSummaryService.php:206`) sebagai konteks analisis.
- Termasuk salah satu dari 3 komponen hash (`maturity_rating`, `is_applicable`, `notes`) yang
  menentukan apakah tombol "Regenerate AI" boleh aktif lagi (`computeDataHash()` /
  `computeResultHash()` / `computeSessionHash()`).
- Muncul di PDF report (kolom "Audit Findings & Justification Notes",
  `resources/views/exports/assessment_result_pdf.blade.php:383,443`) dan Excel export
  (`app/Exports/AssessmentReportExport.php:112`).
- Catatan: perubahan pada `notes` **tidak** tercatat di Audit Trail (field ini tidak ada di
  `$trackedFields` pada `app/Models/AssessmentResult.php:43-48`, beda dari `maturity_rating` dkk
  yang tercatat).

## Fix yang diimplementasikan (2026-08-14)

Di `resources/views/sessions/_item_list.blade.php`:
- `submitForm()` (baris ~170-228): sekarang mengembalikan `{ success, data }`; menampilkan toast
  `type: 'error'` berisi `data.message` asli dari server saat `data.success` false; menambahkan
  `catch` untuk kegagalan jaringan (sebelumnya silent).
- Input evidence upload (baris ~554): toast "Artifact uploaded!" hanya muncul kalau
  `result.success === true`. `$el.value = ''` tetap jalan di kedua kasus.
- Efek samping (disengaja, bukan scope creep — root cause sama): semua caller `submitForm()`
  lain (rating, notes, applicability toggle, finalize) otomatis ikut dapat notifikasi error yang
  jujur kalau gagal tersimpan, karena mereka semua berbagi fungsi yang sama.
- Aturan bisnis (guard "harus rating dulu") **tidak diubah**, sesuai kesepakatan user.

Belum diverifikasi visual di browser oleh Claude (sandbox tidak bisa jalankan server) — user akan
verifikasi sendiri via Herd.
