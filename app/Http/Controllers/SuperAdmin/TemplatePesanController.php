<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\TemplatePesan;
use Illuminate\Http\Request;

class TemplatePesanController extends Controller
{
    public function index()
    {
        $templates = TemplatePesan::orderBy('kode')->get();
        return view('super-admin.template-pesan.index', compact('templates'));
    }

    public function edit(TemplatePesan $templatePesan)
    {
        $placeholders = [
            '{nama_siswa}', '{nama_ortu}', '{jam}', '{tanggal}',
            '{status}', '{nama_sekolah}', '{kelas}', '{keterangan}'
        ];
        return view('super-admin.template-pesan.edit', compact('templatePesan', 'placeholders'));
    }

    public function update(Request $request, TemplatePesan $templatePesan)
    {
        $validated = $request->validate([
            'judul'    => 'required|string|max:100',
            'template' => 'required|string',
            'is_aktif' => 'boolean',
        ]);

        $validated['is_aktif'] = $request->boolean('is_aktif');
        $templatePesan->update($validated);

        return redirect()->route('super-admin.template-pesan.index')
            ->with('success', "Template '{$templatePesan->kode}' berhasil diperbarui.");
    }

    /**
     * Preview render template dengan data dummy.
     */
    public function preview(Request $request, TemplatePesan $templatePesan)
    {
        $previewData = [
            'nama_siswa'   => 'Ahmad Rizky Pratama',
            'nama_ortu'    => 'Bapak Rizky',
            'jam'          => '07:05',
            'tanggal'      => now()->translatedFormat('l, d F Y'),
            'status'       => strtoupper($templatePesan->kode),
            'nama_sekolah' => config('app.nama_sekolah', 'SMK'),
            'kelas'        => 'XII RPL 1',
            'keterangan'   => 'Sakit demam',
        ];

        $preview = $templatePesan->render($previewData);
        return response()->json(['preview' => $preview]);
    }
}
