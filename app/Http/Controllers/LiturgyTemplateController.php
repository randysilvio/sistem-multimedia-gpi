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

        $liturgy = Liturgy::create([
            'name' => $request->name,
        ]);

        $order = 1;
        foreach ($request->blocks as $block) {
            LiturgyItem::create([
                'liturgy_id' => $liturgy->id,
                'title' => $block['title'],
                'is_dynamic' => true, // Semua dibuat dinamis agar bisa diisi saat jadwal dibuat
                'static_content' => $block['content'] ?? null,
                'order_number' => $order++,
            ]);
        }

        return redirect()->route('liturgy.gallery')->with('success', 'Template Tata Ibadah baru berhasil disimpan!');
    }

    public function destroy($id)
    {
        $liturgy = Liturgy::findOrFail($id);
        $liturgy->items()->delete(); 
        $liturgy->delete(); 

        return redirect()->route('liturgy.gallery')->with('success', 'Template berhasil dihapus dari Master Database.');
    }
}