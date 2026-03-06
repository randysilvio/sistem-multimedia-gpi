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
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:102400', 
            'title' => 'nullable|string|max:255',
            'order_num' => 'nullable|integer',
            'duration' => 'nullable|integer|min:1' 
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            
            $cleanName = Str::slug(pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME));
            $fileName = time() . '_' . $cleanName . '.' . $image->getClientOriginalExtension();
            
            // SIMPAN LANGSUNG KE FOLDER PUBLIC DENGAN CARA PALING AMAN UNTUK SHARED HOSTING
            $destinationPath = public_path('uploads/warta');
            
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $image->move($destinationPath, $fileName);
            
            // Simpan nama path yang bisa diakses URL secara langsung
            $path = 'uploads/warta/' . $fileName; 

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
        
        $filePath = public_path($announcement->image_path);
        
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        $announcement->delete();
        
        return back()->with('success', 'Gambar warta berhasil dihapus.');
    }
}