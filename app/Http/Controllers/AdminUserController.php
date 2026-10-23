<?php
namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\CertificateSetting;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AdminUserController extends Controller
{
    private const IMPORT_PASSWORD = 'Elderwis3';
    private const MAX_IMPORT_ROWS = 500;

    /** Peran yang tersedia. 'learner' = peserta (nilai yang dipakai saat registrasi). */
    public const ROLES = ['learner' => 'Peserta', 'admin' => 'Admin'];

    /** Pesan validasi berbahasa Indonesia untuk form pengguna. */
    private function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama terlalu panjang (maksimal 120 karakter).',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah dipakai akun lain.',
            'role.required' => 'Peran wajib dipilih.',
            'role.in' => 'Peran yang dipilih tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Pengulangan kata sandi tidak sama.',
        ];
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $role = $request->query('role');
        $certificateStatus = $request->query('certificate_status', '');
        if (! in_array($certificateStatus, ['not_requested', 'pending', 'approved'], true)) {
            $certificateStatus = '';
        }

        $users = User::query()
            ->when($q !== '', fn ($query) => $query->where(
                fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")
            ))
            ->when(array_key_exists((string) $role, self::ROLES), fn ($query) => $query->where('role', $role))
            ->when($certificateStatus === 'not_requested', fn ($query) => $query->whereDoesntHave('certificate'))
            ->when($certificateStatus === 'pending', fn ($query) => $query->whereHas(
                'certificate',
                fn ($certificate) => $certificate->whereNull('approved_at')->orWhereNull('sequence')
            ))
            ->when($certificateStatus === 'approved', fn ($query) => $query->whereHas(
                'certificate',
                fn ($certificate) => $certificate->whereNotNull('approved_at')->whereNotNull('sequence')
            ))
            ->withCount(['lessonProgress as done_count' => fn ($p) => $p->whereNotNull('completed_at')])
            ->withSum(['lessonProgress as xp_total' => fn ($p) => $p->whereNotNull('completed_at')], 'xp')
            ->with('certificate')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'q' => $q,
            'role' => array_key_exists((string) $role, self::ROLES) ? $role : '',
            'certificateStatus' => $certificateStatus,
            'totalLessons' => Lesson::count(),
            'countAll' => User::count(),
            'countLearners' => User::where('role', '!=', 'admin')->count(),
            'countAdmins' => User::where('role', 'admin')->count(),
            'certSetting' => CertificateSetting::current(),
        ]);
    }

    public function downloadImportTemplate()
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()
            ->fromArray([['nama', 'email']], null, 'A1');
        $spreadsheet->getActiveSheet()->setTitle('Pengguna');

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, 'template-import-pengguna.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ], [
            'file.required' => 'Pilih file Excel yang akan diimpor.',
            'file.mimes' => 'File harus berformat XLSX, XLS, atau CSV.',
            'file.max' => 'Ukuran file maksimal 5 MB.',
        ]);

        try {
            $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
        } catch (\PhpOffice\PhpSpreadsheet\Exception $exception) {
            report($exception);

            return back()->withErrors(['file' => 'File Excel tidak dapat dibaca. Pastikan file tidak rusak dan memakai templat yang disediakan.']);
        }

        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestDataRow();

        if ($highestRow < 2) {
            $spreadsheet->disconnectWorksheets();

            return back()->withErrors(['file' => 'File tidak berisi data pengguna. Tambahkan data di bawah baris judul.']);
        }

        if ($highestRow - 1 > self::MAX_IMPORT_ROWS) {
            $spreadsheet->disconnectWorksheets();

            return back()->withErrors(['file' => 'Maksimal ' . self::MAX_IMPORT_ROWS . ' pengguna dapat diimpor sekaligus.']);
        }

        $rows = $sheet->toArray(null, true, true, false);
        $spreadsheet->disconnectWorksheets();
        $headers = array_map(
            fn ($header) => mb_strtolower(trim((string) $header)),
            $rows[0] ?? []
        );
        $nameColumn = array_search('nama', $headers, true);
        if ($nameColumn === false) {
            $nameColumn = array_search('name', $headers, true);
        }
        $emailColumn = array_search('email', $headers, true);

        if ($nameColumn === false || $emailColumn === false) {
            return back()->withErrors(['file' => 'Baris pertama harus memiliki judul kolom "nama" dan "email". Unduh templat untuk format yang benar.']);
        }

        $users = [];
        $emails = [];
        $errors = [];
        $skippedCount = 0;

        $importEmails = [];
        foreach (array_slice($rows, 1) as $row) {
            $email = mb_strtolower(trim((string) ($row[$emailColumn] ?? '')));
            if ($email !== '') {
                $importEmails[] = $email;
            }
        }

        $existingEmails = $importEmails === []
            ? []
            : User::query()
                ->whereIn(DB::raw('LOWER(email)'), array_unique($importEmails))
                ->pluck('email')
                ->map(fn ($email) => mb_strtolower($email))
                ->all();
        $existingEmailSet = array_fill_keys($existingEmails, true);

        foreach (array_slice($rows, 1, null, true) as $index => $row) {
            $name = trim((string) ($row[$nameColumn] ?? ''));
            $email = mb_strtolower(trim((string) ($row[$emailColumn] ?? '')));

            if ($name === '' && $email === '') {
                continue;
            }

            $rowNumber = $index + 1;
            if ($email !== '' && (isset($emails[$email]) || isset($existingEmailSet[$email]))) {
                $emails[$email] = true;
                $skippedCount++;

                continue;
            }
            if ($email !== '') {
                $emails[$email] = true;
            }

            $validator = Validator::make(
                ['name' => $name, 'email' => $email],
                ['name' => ['required', 'string', 'max:120'], 'email' => ['required', 'email', 'max:190']],
                [
                    'name.required' => "Baris {$rowNumber}: nama wajib diisi.",
                    'name.max' => "Baris {$rowNumber}: nama maksimal 120 karakter.",
                    'email.required' => "Baris {$rowNumber}: email wajib diisi.",
                    'email.email' => "Baris {$rowNumber}: format email tidak valid.",
                    'email.max' => "Baris {$rowNumber}: email maksimal 190 karakter.",
                ]
            );

            if ($validator->fails()) {
                array_push($errors, ...$validator->errors()->all());

                continue;
            }

            $users[] = ['name' => $name, 'email' => $email, 'row' => $rowNumber];
        }

        if ($users === [] && $errors === [] && $skippedCount === 0) {
            $errors[] = 'Tidak ada baris pengguna yang dapat diimpor.';
        }

        if ($errors !== []) {
            return back()->withErrors(['file' => $errors]);
        }

        if ($users === []) {
            return redirect()->route('admin.users.index')
                ->with('warning', $skippedCount > 0
                    ? "{$skippedCount} baris dilewati karena email sudah terdaftar atau berulang. Tidak ada pengguna baru yang diimpor."
                    : 'Tidak ada baris pengguna yang dapat diimpor.');
        }

        DB::transaction(function () use ($users) {
            $timestamp = now();
            $records = array_map(fn ($data) => [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make(self::IMPORT_PASSWORD),
                'role' => 'learner',
                'email_verified_at' => $timestamp,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], $users);

            foreach (array_chunk($records, 100) as $batch) {
                User::insert($batch);
            }
        });

        $message = count($users) . ' pengguna berhasil diimpor';
        if ($skippedCount > 0) {
            $message .= "; {$skippedCount} baris dilewati karena email sudah terdaftar atau berulang";
        }

        return redirect()->route('admin.users.index')
            ->with('success', $message . '. Kata sandi awal: ' . self::IMPORT_PASSWORD . '.');
    }

    public function create()
    {
        return view('admin.users.form', ['user' => new User(['role' => 'learner']), 'roles' => self::ROLES, 'certSetting' => CertificateSetting::current()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'role' => ['required', Rule::in(array_keys(self::ROLES))],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], $this->messages());

        // Akun yang dibuat admin dianggap terverifikasi (admin yang menjamin emailnya).
        // Password di-hash otomatis oleh cast 'hashed' pada model User.
        $user = new User($data);
        $user->forceFill(['email_verified_at' => now()])->save();

        return redirect()->route('admin.users.index')->with('success', 'Pengguna "' . $data['name'] . '" berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $user->loadCount(['lessonProgress as done_count' => fn ($p) => $p->whereNotNull('completed_at')]);
        $user->loadSum(['lessonProgress as xp_total' => fn ($p) => $p->whereNotNull('completed_at')], 'xp');
        $user->load('certificate');

        return view('admin.users.form', [
            'user' => $user,
            'roles' => self::ROLES,
            'totalLessons' => Lesson::count(),
            'certSetting' => CertificateSetting::current(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(array_keys(self::ROLES))],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], $this->messages());

        // Admin tidak boleh menurunkan perannya sendiri (mencegah sistem tanpa admin sama sekali).
        if ($user->id === $request->user()->id && $data['role'] !== 'admin') {
            return back()->withErrors(['role' => 'Peran akun Anda sendiri tidak bisa diturunkan dari Admin. Minta admin lain jika perlu.'])->withInput();
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']); // kosong = kata sandi tidak diubah
        }

        $emailChanged = $data['email'] !== $user->email;

        $user->update($data);

        if ($emailChanged && $user->id !== $request->user()->id) {
            // Email baru belum terbukti milik pengguna: minta verifikasi ulang (kecuali akun admin yang sedang dipakai).
            $user->forceFill(['email_verified_at' => null])->save();
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()->route('admin.users.index')->with('success', 'Data pengguna "' . $user->name . '" berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return redirect()->route('admin.users.index')->with('error', 'Akun yang sedang Anda pakai tidak bisa dihapus.');
        }

        $name = $user->name;

        DB::transaction(function () use ($user) {
            LessonProgress::where('user_id', $user->id)->delete();
            Certificate::where('user_id', $user->id)->delete();
            $user->delete();
        });

        return redirect()->route('admin.users.index')->with('success', 'Pengguna "' . $name . '" beserta progres belajarnya telah dihapus.');
    }

    public function resetAllLearning()
    {
        [$progressCount, $certificateCount] = DB::transaction(function () {
            $learnerIds = User::query()->where('role', 'learner')->select('id');
            $progressCount = LessonProgress::query()->whereIn('user_id', $learnerIds)->delete();
            $certificateCount = Certificate::query()
                ->whereIn('user_id', User::query()->where('role', 'learner')->select('id'))
                ->delete();

            return [$progressCount, $certificateCount];
        });

        return redirect()->route('admin.users.index')->with(
            'success',
            "Reset semua peserta selesai: {$progressCount} catatan progres dan {$certificateCount} pengajuan sertifikat dihapus."
        );
    }

    public function resetLearning(User $user)
    {
        if ($user->role !== 'learner') {
            return redirect()->route('admin.users.index')->with('error', 'Reset progres hanya tersedia untuk akun peserta.');
        }

        [$progressCount, $certificateCount] = DB::transaction(function () use ($user) {
            return [
                $user->lessonProgress()->delete(),
                $user->certificate()->delete(),
            ];
        });

        return redirect()->route('admin.users.index')->with(
            'success',
            "Progres dan pengajuan sertifikat {$user->name} direset: {$progressCount} catatan progres dan {$certificateCount} pengajuan dihapus."
        );
    }
}
