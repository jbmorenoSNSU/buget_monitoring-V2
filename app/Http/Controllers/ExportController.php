<?php

namespace App\Http\Controllers;

use App\Models\Export;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ExportController extends Controller
{
    public function index(Request $request)
    {
        // For simplicity, returning all exports. If it's a multi-user app, filter by user.
        $exports = Export::orderBy('created_at', 'desc')->paginate(20);

        return Inertia::render('Downloads/Index', [
            'exports' => $exports,
        ]);
    }

    public function download(Export $export)
    {
        // Check if file exists and status is completed
        if ($export->status !== 'completed' || ! $export->file_path || ! Storage::disk('public')->exists($export->file_path)) {
            abort(404, 'File not found or still processing.');
        }

        return Storage::disk('public')->download($export->file_path, $export->file_name);
    }

    public function destroy(Export $export)
    {
        if ($export->file_path && Storage::disk('public')->exists($export->file_path)) {
            Storage::disk('public')->delete($export->file_path);
        }

        $export->delete();

        return redirect()->back()->with('success', 'Export deleted successfully.');
    }
}
