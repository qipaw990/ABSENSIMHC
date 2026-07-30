<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\WaLog;
use Illuminate\Http\Request;

class WaLogController extends Controller
{
    public function index(Request $request)
    {
        $waLogs = WaLog::with(['absensi.siswa.kelas', 'waSender'])
            ->when($request->status,  fn($q) => $q->where('status', $request->status))
            ->when($request->tanggal, fn($q) => $q->whereDate('created_at', $request->tanggal))
            ->orderBy('created_at', 'desc')
            ->paginate(30)
            ->withQueryString();

        $stats = WaLog::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('super-admin.wa-log.index', compact('waLogs', 'stats'));
    }
}
