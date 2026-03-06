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
        // Validasi
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:102400', 
            'title' => 'nullable|string|max:255',
            'order_num' => 'nullable|integer',
            'duration' => 'nullable|integer|min:1' 
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            
            // Bersihkan nama file
            $cleanName = Str::slug(pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME));
            $fileName = time() . '_' . $cleanName . '.' . $image->getClientOriginalExtension();
            
            // =======================================================
            // SOLUSI DEPLOY HOSTING (BYPASS SYMLINK)
            // Memaksa file masuk ke folder asli server (public_html/storage/sinode)
            // =======================================================
            $destinationPath = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/storage/sinode';
            
            // Jika folder belum ada di hosting, buat otomatis
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            // Pindahkan file fisik ke folder tersebut
            $image->move($destinationPath, $fileName);
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
        
        // SOLUSI DEPLOY HOSTING: Hapus file fisik dari akar server
        $filePath = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/storage/' . $announcement->image_path;
        
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        $announcement->delete();
        
        return back()->with('success', 'Gambar warta berhasil dihapus.');
    }
}