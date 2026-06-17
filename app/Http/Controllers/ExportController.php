<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\ExportResource;
use App\Models\Export;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Handles HTTP actions for export file downloads.
 */
class ExportController extends Controller
{
    /**
     * Display all available exports.
     */
    public function index(): Response
    {
        $exports = Export::orderBy('created_at', 'desc')
            ->cursorPaginate(20);

        return Inertia::render('Downloads/Index', [
            'exports' => ExportResource::collection($exports),
        ]);
    }

    /**
     * Download a completed export file.
     */
    public function download(Export $export)
    {
        $this->authorize('download', $export);

        if ($export->status !== 'completed' || ! $export->file_path || ! Storage::disk('public')->exists($export->file_path)) {
            abort(404, 'File not found or still processing.');
        }

        return Storage::disk('public')->download($export->file_path, $export->file_name);
    }

    /**
     * Delete an export record and its file from storage.
     */
    public function destroy(Export $export): RedirectResponse
    {
        $this->authorize('delete', $export);

        if ($export->file_path && Storage::disk('public')->exists($export->file_path)) {
            Storage::disk('public')->delete($export->file_path);
        }

        $export->delete();

        return redirect()->back()->with('success', 'Export deleted successfully.');
    }
}
