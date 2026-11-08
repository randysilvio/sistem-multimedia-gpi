<?php

namespace App\Http\Controllers;

use App\Services\ActivityLog;
use Illuminate\Http\Request;
use App\Models\Liturgy;
use App\Models\LiturgyItem;

class LiturgyTemplateController extends Controller
{
    public function create()
    {
        return view('liturgy.template_create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'blocks' => 'required|array|min:1',
        ]);

        $liturgy = Liturgy::create(['name' => $request->name]);

        // Perbaikan 1: Gunakan $order manual agar terhindar dari bentrok huruf (new_1) & angka saat edit
        $order = 1;
        foreach ($request->blocks as $block) {
            LiturgyItem::create([
                'liturgy_id' => $liturgy->id,
                // Perbaikan 2: Jika judul kosong (null), otomatis isi dengan 'Slide Bebas' agar DB tidak menolak
                'title' => !empty($block['title']) ? $block['title'] : 'Slide Bebas',
                'is_dynamic' => true,
                'static_content' => $block['content'] ?? null,
                'order_number' => $order++,
            ]);
        }

        ActivityLog::record('template.tambah', $liturgy, "Membuat template {$liturgy->name}");
        return redirect()->route('liturgy.gallery')->with('success', 'Template baru berhasil disimpan!');
    }

    public function edit($id)
    {
        $liturgy = Liturgy::with(['items' => function($q) {
            $q->orderBy('order_number', 'asc');
        }])->findOrFail($id);

        return view('liturgy.template_edit', compact('liturgy'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'blocks' => 'required|array|min:1',
        ]);

        $liturgy = Liturgy::findOrFail($id);
        $liturgy->update(['name' => $request->name]);

        // Hapus item lama dan ganti dengan yang baru (cara termudah untuk re-order)
        $liturgy->items()->delete();

        // Perbaikan 1: Gunakan $order manual
        $order = 1;
        foreach ($request->blocks as $block) {
            LiturgyItem::create([
                'liturgy_id' => $liturgy->id,
                // Perbaikan 2: Cegah kolom title bernilai null
                'title' => !empty($block['title']) ? $block['title'] : 'Slide Bebas',
                'is_dynamic' => true,
                'static_content' => $block['content'] ?? null,
                'order_number' => $order++,
            ]);
        }

        ActivityLog::record('template.ubah', $liturgy, "Mengubah template {$liturgy->name}");
        return redirect()->route('liturgy.gallery')->with('success', 'Template berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $liturgy = Liturgy::findOrFail($id);

        // Jadwal memakai restrictOnDelete: tanpa pengecekan ini hasilnya error 500.
        $used = $liturgy->schedules()->count();
        if ($used > 0) {
            return back()->with('error', "Template \"{$liturgy->name}\" masih dipakai oleh {$used} jadwal ibadah. Hapus jadwal tersebut terlebih dahulu.");
        }

        $liturgy->delete();

        ActivityLog::record('template.hapus', null, "Menghapus template {$liturgy->name}");
        return back()->with('success', 'Template berhasil dihapus.');
    }
}