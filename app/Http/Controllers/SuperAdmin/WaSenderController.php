<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\WaLog;
use App\Models\WaSender;
use App\Services\FonnteService;
use Illuminate\Http\Request;

class WaSenderController extends Controller
{
    public function __construct(private FonnteService $fonnteService) {}

    public function index()
    {
        $waSenders = WaSender::with('kelas.jurusan')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Kelas yang belum punya sender
        $kelasTanpaSender = Kelas::whereDoesntHave('waSender')->with('jurusan')->get();

        return view('super-admin.wa-sender.index', compact('waSenders', 'kelasTanpaSender'));
    }

    public function create()
    {
        $kelasList = Kelas::whereDoesntHave('waSender')->with('jurusan')->get();
        return view('super-admin.wa-sender.create', compact('kelasList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kelas_id'    => 'required|exists:kelas,id|unique:wa_senders,kelas_id',
            'nama_device' => 'required|string|max:100',
            'token_fonnte' => 'required|string',
            'nomor_wa'    => 'nullable|string|max:20',
        ]);

        $waSender = WaSender::create($validated);

        // Cek status langsung
        $status = $this->fonnteService->cekStatus($waSender->token_fonnte);
        $waSender->update(['status' => $status, 'last_check_at' => now()]);

        return redirect()->route('super-admin.wa-sender.index')
            ->with('success', "WA Sender berhasil ditambahkan! Status: {$status}");
    }

    public function show(WaSender $waSender)
    {
        $waSender->load('kelas.jurusan');
        $logs = WaLog::where('wa_sender_id', $waSender->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('super-admin.wa-sender.show', compact('waSender', 'logs'));
    }

    public function edit(WaSender $waSender)
    {
        $kelasList = Kelas::with('jurusan')->get();
        return view('super-admin.wa-sender.edit', compact('waSender', 'kelasList'));
    }

    public function update(Request $request, WaSender $waSender)
    {
        $validated = $request->validate([
            'kelas_id'     => 'required|exists:kelas,id|unique:wa_senders,kelas_id,' . $waSender->id,
            'nama_device'  => 'required|string|max:100',
            'token_fonnte' => 'nullable|string',
            'nomor_wa'     => 'nullable|string|max:20',
            'status'       => 'required|in:aktif,nonaktif,terputus',
        ]);

        // Hanya update token jika diisi (tidak mau overwrite token lama dengan string kosong)
        if (empty($validated['token_fonnte'])) {
            unset($validated['token_fonnte']);
        }

        $waSender->update($validated);

        // ✅ Otomatis tes koneksi & update status device via Fonnte API setelah diubah
        $status = $this->fonnteService->cekStatus($waSender->token_fonnte);
        $waSender->update([
            'status'        => $status,
            'last_check_at' => now(),
        ]);

        return redirect()->route('super-admin.wa-sender.index')
            ->with('success', "WA Sender {$waSender->nama_device} berhasil diperbarui! Status device: {$status}");
    }

    public function destroy(WaSender $waSender)
    {
        $waSender->delete();
        return redirect()->route('super-admin.wa-sender.index')
            ->with('success', 'WA Sender berhasil dihapus.');
    }

    /**
     * Test kirim pesan ke nomor tertentu menggunakan sender ini.
     */
    public function testKirim(Request $request, WaSender $waSender)
    {
        $request->validate([
            'target_nomor' => 'required|string',
            'pesan'        => 'required|string|max:500',
        ]);

        $hasil = $this->fonnteService->kirim(
            $waSender->token_fonnte,
            $request->target_nomor,
            $request->pesan
        );

        // ✅ Simpan ke WaLog agar tercatat di halaman Log WA
        WaLog::create([
            'wa_sender_id'    => $waSender->id,
            'absensi_id'      => null, // Test manual, bukan dari absensi
            'target_nomor'    => $request->target_nomor,
            'pesan'           => '[TEST] ' . $request->pesan,
            'status'          => $hasil['success'] ? 'terkirim' : 'gagal',
            'response_fonnte' => is_array($hasil['response'])
                                    ? json_encode($hasil['response'])
                                    : (string) $hasil['response'],
            'sent_at'         => now(),
        ]);

        // Update last_check_at sender
        $waSender->touch();

        $status  = $hasil['success'] ? 'success' : 'error';
        $message = $hasil['success']
            ? '✅ Pesan test berhasil terkirim ke ' . $request->target_nomor
            : '❌ Gagal kirim: ' . (is_string($hasil['response']) ? $hasil['response'] : json_encode($hasil['response']));

        return back()->with($status, $message);
    }

    /**
     * Cek status device WA sender ini via Fonnte API.
     */
    public function cekStatus(WaSender $waSender)
    {
        $status = $this->fonnteService->cekStatus($waSender->token_fonnte);
        $waSender->update(['status' => $status, 'last_check_at' => now()]);

        $nomor = $waSender->nomor_wa ?? $waSender->nama_device;

        $labels = [
            'aktif'    => "✅ {$nomor}: Terhubung & siap kirim WA",
            'terputus' => "❌ {$nomor}: Terputus — cek log Laravel untuk detail response",
            'nonaktif' => "⚠️ {$nomor}: Nonaktif",
        ];

        $sessionType = $status === 'aktif' ? 'success' : ($status === 'terputus' ? 'error' : 'warning');

        return back()->with($sessionType, $labels[$status] ?? "Status: {$status}");
    }

    /**
     * Log WA seluruh sistem.
     */
    public function logIndex(Request $request)
    {
        $logs = WaLog::with(['waSender.kelas', 'absensi.siswa'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->kelas_id, fn($q) => $q->whereHas('waSender', fn($q2) => $q2->where('kelas_id', $request->kelas_id)))
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        $kelasList = Kelas::with('jurusan')->get();

        return view('super-admin.wa-log.index', compact('logs', 'kelasList'));
    }
}
