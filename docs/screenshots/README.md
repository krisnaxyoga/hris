# Screenshots

Berkas di folder ini adalah tangkapan layar asli aplikasi yang dirujuk oleh README utama.

| Nama berkas | Halaman | Akun saat capture |
|-------------|---------|-------------------|
| `login.png` | Halaman login (`/login`) | — (belum login) |
| `dashboard.png` | Dashboard ringkasan (`/dashboard`) | admin |
| `attendance.png` | Absensi check-in/out + peta geofence (`/attendance/me`) | employee |
| `leave.png` | Pengajuan & saldo cuti (`/leave/me`) | employee |
| `leave-approvals.png` | Antrian persetujuan cuti (`/leave/approvals`) | admin |
| `employees.png` | Daftar karyawan (`/employees`) | admin |
| `timesheets.png` | Timesheet (`/timesheets`) | admin |

> Halaman self-service (`/attendance/me`, `/leave/me`) di-capture memakai akun **employee@hris.local** karena akun admin tidak terhubung ke profil karyawan. Untuk halaman absensi, lokasi GPS browser diset ke koordinat kantor (Head Office) agar peta & badge "Inside office radius" tampil.

**Memperbarui screenshot**: jalankan aplikasi (`composer run dev`), pastikan `php artisan db:seed` sudah dijalankan, lalu ambil ulang halaman terkait dan timpa berkas `.png` di sini (lebar ±1366px, PNG).
