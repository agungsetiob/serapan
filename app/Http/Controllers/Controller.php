<?php

namespace App\Http\Controllers;

abstract class Controller
{
    //
}


    // public function update(Request $request, NotaDinas $notaDina)
    // {
    //     $subKegiatan = SubKegiatan::find($request->sub_kegiatan_id);
        
    //     if (!$subKegiatan) {
    //         return redirect()->back()->with('error', 'Sub kegiatan tidak ditemukan')->withInput();
    //     }

    //     $totalSerapan = $subKegiatan->notaDinas()->where('id', '!=', $notaDina->id)->sum('anggaran');
    //     $sisaPagu = $subKegiatan->pagu - $totalSerapan;

    //     $validated = $request->validate([
    //         'nomor_nota' => 'required|string|max:255',
    //         'perihal' => 'required|string|max:255',
    //         'anggaran' => [
    //             'required',
    //             'numeric',
    //             'min:0',
    //             function ($attribute, $value, $fail) use ($sisaPagu) {
    //                 if ($value > $sisaPagu) {
    //                     $fail('Anggaran melebihi sisa pagu sub kegiatan. Sisa pagu: Rp ' . number_format($sisaPagu, 2, ',', '.'));
    //                 }
    //             },
    //         ],
    //         'tanggal_pengajuan' => 'required|date',
    //         'sub_kegiatan_id' => 'required|exists:sub_kegiatans,id',
    //         'lampirans.*' => 'nullable|file|max:3072|mimes:pdf,doc,docx,xls,xlsx',
    //         'existing_lampirans' => 'nullable|array',
    //         'existing_lampirans.*' => 'exists:nota_lampirans,id',
    //     ]);

    //     try {
    //         return DB::transaction(function () use ($validated, $request, $notaDina) {
    //             // Update the nota dinas
    //             $notaDina->update([
    //                 'nomor_nota' => $validated['nomor_nota'],
    //                 'perihal' => $validated['perihal'],
    //                 'anggaran' => $validated['anggaran'],
    //                 'tanggal_pengajuan' => $validated['tanggal_pengajuan'],
    //                 'sub_kegiatan_id' => $validated['sub_kegiatan_id'],
    //             ]);

    //             if ($request->has('existing_lampirans')) {
    //                 $notaDina->lampirans()
    //                     ->whereNotIn('id', $request->existing_lampirans)
    //                     ->delete();
    //             } else {
    //                 $notaDina->lampirans()->delete();
    //             }

    //             if ($request->hasFile('lampirans')) {
    //                 $attachments = [];
    //                 foreach ($request->file('lampirans') as $file) {
    //                     $path = $file->store('nota_lampirans', 'public');
    //                     $attachments[] = [
    //                         'nota_dinas_id' => $notaDina->id,
    //                         'nama_file' => $file->getClientOriginalName(),
    //                         'path' => $path,
    //                         'created_at' => now(),
    //                         'updated_at' => now(),
    //                     ];
    //                 }
    //                 NotaLampiran::insert($attachments);
    //             }

    //             $this->updateBudgetAbsorption($notaDina);

    //             return redirect()->back()->with('success', 'Nota dinas berhasil diperbarui.');
    //         });
    //     } catch (\Exception $e) {
    //         return redirect()->back()
    //             ->with('error', 'Gagal memperbarui nota dinas: ' . $e->getMessage())
    //             ->withInput();
    //     }
    // }