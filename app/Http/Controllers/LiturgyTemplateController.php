<?php

namespace App\Http\Controllers;

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

        foreach ($request->blocks as $index => $block) {
            LiturgyItem::create([
                'liturgy_id' => $liturgy->id,
                'title' => $block['title'],
                'is_dynamic' => true,
                'static_content' => $block['content'] ?? null,
                'order_number' => $index + 1,
            ]);
        }

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

        foreach ($request->blocks as $index => $block) {
            LiturgyItem::create([
                'liturgy_id' => $liturgy->id,
                'title' => $block['title'],
                'is_dynamic' => true,
                'static_content' => $block['content'] ?? null,
                'order_number' => $index + 1,
            ]);
        }

        return redirect()->route('liturgy.gallery')->with('success', 'Template berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $liturgy = Liturgy::findOrFail($id);
        $liturgy->delete(); // Pastikan di model Liturgy sudah diset cascade delete atau hapus manual items-nya
        return redirect()->route('liturgy.gallery')->with('success', 'Template berhasil dihapus.');
    }
}