<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;
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
            'duration' => 'nullable|integer|min:1' 
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            
            // Bersihkan nama file dari spasi agar tidak error
            $cleanName = Str::slug(pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME));
            $fileName = time() . '_' . $cleanName . '.' . $image->getClientOriginalExtension();
            
            // BYPASS SYMLINK: Pindahkan file fisik LANGSUNG ke folder public/storage/sinode
            $image->move(public_path('storage/sinode'), $fileName);
            $path = 'sinode/' . $fileName; 

            Announcement::create([
                'title' => $request->title,
                'image_path' => $path, 
                'is_active' => true,
                'order_num' => $request->order_num ?? 0,
                'duration' => $request->duration ?? 5 
            ]);
        }

        return back()->with('success', 'Warta Sinode berhasil diunggah!');
    }

    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);
        
        // BYPASS SYMLINK: Hapus file fisik langsung dari folder public
        $filePath = public_path('storage/' . $announcement->image_path);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        $announcement->delete();
        
        return back()->with('success', 'Gambar warta berhasil dihapus.');
    }
}