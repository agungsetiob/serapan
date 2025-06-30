<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NotaSkpdRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $ignoreId = optional($this->route('nota_skpd'))->id;

        return [
            'nomor_nota'      => [
                'required','string','max:100',
                Rule::unique('nota_dinas', 'nomor_nota')
                    ->ignore($ignoreId),
            ],
            'perihal'         => 'required|string|max:255',
            'anggaran'        => 'nullable|numeric|min:0',
            'tanggal_pengajuan' => 'required|date',
            'jenis'           => 'required|in:Perda,Perbup,Surat,Rekomendasi,Telaah,Edaran,Instruksi,SK',
            'skpd_id'         => 'required|exists:skpds,id',
            'parent_ids'      => 'nullable|array',
            'parent_ids.*'    => 'exists:nota_dinas,id',
            'lampirans.*'     => 'nullable|file|max:3072|mimes:pdf',
        ];
    }

    public function messages()
    {
        return [
            'nomor_nota.unique'     => 'Nomor Nota ini sudah ada.',
            'lampirans.*.max'       => 'Ukuran setiap file lampiran maksimal 3MB.',
            'lampirans.*.mimes'     => 'Setiap file lampiran harus berupa file PDF.',
            'skpd_id.required'      => 'SKPD harus dipilih.',
            'skpd_id.exists'        => 'SKPD tidak valid.',
            'anggaran.numeric'      => 'Anggaran harus berupa angka.',
            'anggaran.min'          => 'Anggaran tidak boleh kurang dari 0.',
            'jenis.in' => 'Nota Dinas Pelaksanaan, TU, LS, GU hanya dapat dibuat dan diedit melalui menu SKPD',
        ];
    }
}
