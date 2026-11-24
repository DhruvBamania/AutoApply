<?php

namespace App\Http\Controllers;

use App\Models\SavedApplication;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class ChromeExtensionController extends Controller
{
    /**
     * Display Chrome Extension information and installation guide.
     */
    public function index()
    {
        return view('pages.chrome_extension');
    }

    /**
     * Package and stream the Chrome Extension as a ZIP archive.
     */
    public function downloadZip()
    {
        $extensionDir = base_path('chrome_extension');

        if (!is_dir($extensionDir)) {
            return back()->with('error', 'Chrome extension directory not found.');
        }

        $zipPath = storage_path('app/autoapply-ai-companion.zip');

        // Ensure parent directory exists
        if (!is_dir(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }

        if (file_exists($zipPath)) {
            @unlink($zipPath);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Unable to create ZIP archive.');
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($extensionDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($extensionDir) + 1);
                // Normalize directory separators for zip compatibility
                $relativePath = str_replace('\\', '/', $relativePath);
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();

        return response()->download($zipPath, 'autoapply-ai-companion.zip', [
            'Content-Type' => 'application/zip',
            'Cache-Control' => 'no-cache, must-revalidate',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Display candidate external application tracker dashboard.
     */
    public function tracker(Request $request)
    {
        $user = $request->user();
        $query = $user->savedApplications();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('platform') && $request->platform !== 'all') {
            $query->where('platform', $request->platform);
        }

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('job_title', 'like', $term)
                  ->orWhere('company_name', 'like', $term)
                  ->orWhere('location', 'like', $term);
            });
        }

        $applications = $query->latest('id')->paginate(12)->withQueryString();

        $stats = [
            'total' => $user->savedApplications()->count(),
            'bookmarked' => $user->savedApplications()->where('status', 'bookmarked')->count(),
            'applied' => $user->savedApplications()->where('status', 'applied')->count(),
            'interviewing' => $user->savedApplications()->where('status', 'interviewing')->count(),
            'offered' => $user->savedApplications()->where('status', 'offered')->count(),
            'rejected' => $user->savedApplications()->where('status', 'rejected')->count(),
        ];

        return view('candidate.applications.index', compact('applications', 'stats'));
    }

    /**
     * Update application status from web UI.
     */
    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:bookmarked,applied,interviewing,offered,rejected',
            'notes' => 'nullable|string',
        ]);

        $application = $request->user()->savedApplications()->findOrFail($id);

        if ($validated['status'] === 'applied' && !$application->applied_at) {
            $application->applied_at = now();
        }

        $application->update($validated);

        return back()->with('success', 'Application status updated to ' . ucfirst($validated['status']) . '.');
    }

    /**
     * Delete application from tracker.
     */
    public function destroy(Request $request, int $id)
    {
        $application = $request->user()->savedApplications()->findOrFail($id);
        $application->delete();

        return back()->with('success', 'Application removed from tracker.');
    }
}

