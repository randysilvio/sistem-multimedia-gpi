<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::orderBy('order_num', 'asc')->orderBy('created_at', 'desc')->get();
        return view('liturgy.announcement', compact('announcements'));
    }

    public function store(Request $request)
    {
        // Validasi: Tambah format GIF, limit 100MB, dan kolom durasi
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:102400', 
            'title' => 'nullable|string|max:255',
            'order_num' => 'nullable|integer',
            'duration' => 'nullable|integer|min:1' // Tambahan validasi durasi
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            
            // PENTING: Bersihkan nama file dari spasi agar tidak error / broken image
            $cleanName = Str::slug(pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME));
            $fileName = time() . '_' . $cleanName . '.' . $image->getClientOriginalExtension();
            
            // Simpan menggunakan disk 'public' langsung ke folder sinode
            $path = $image->storeAs('sinode', $fileName, 'public'); 

            Announcement::create([
                'title' => $request->title,
                'image_path' => $path, 
                'is_active' => true,
                'order_num' => $request->order_num ?? 0,
                'duration' => $request->duration ?? 5 // Simpan durasi, default 5 detik
            ]);
        }

        return back()->with('success', 'Warta Sinode berhasil diunggah!');
    }

    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);
        
        // Hapus file fisik dari folder storage/public
        if (Storage::disk('public')->exists($announcement->image_path)) {
            Storage::disk('public')->delete($announcement->image_path);
        }
        
        $announcement->delete();
        
        return back()->with('success', 'Gambar warta berhasil dihapus.');
    }
}