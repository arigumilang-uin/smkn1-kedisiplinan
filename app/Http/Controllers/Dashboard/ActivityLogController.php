<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * List activity logs dengan filter
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Deny access for Kepala Sekolah — audit & logs are for Operator/System maintenance only
        if ($user->hasRole('Kepala Sekolah')) {
            return redirect()->route('dashboard.kepsek')->with('error', 'Akses fitur Audit & Log dibatasi untuk Kepala Sekolah.');
        }

        // Default: full activity log access (Operator / Admin flows use different routes)
        $query = Activity::query();

        // Filter by log name (cacat, approval, etc)
        if ($request->filled('type')) {
            $query->where('log_name', $request->type);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id);
        }

        // Filter by date range
        if ($request->filled('dari_tanggal')) {
            $query->whereDate('created_at', '>=', $request->dari_tanggal);
        }

        if ($request->filled('sampai_tanggal')) {
            $query->whereDate('created_at', '<=', $request->sampai_tanggal);
        }

        // Search in description
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $logs = $query->with('causer')
                      ->orderBy('created_at', 'desc')
                      ->paginate(20);

        $activityTypes = Activity::distinct('log_name')->pluck('log_name');

        return view('kepala_sekolah.activity.index', [
            'logs' => $logs,
            'activityTypes' => $activityTypes,
        ]);
    }

    /**
     * Show detail log
     */
    public function show(Activity $activity)
    {
        $user = auth()->user();
        if ($user->hasRole('Kepala Sekolah')) {
            return redirect()->route('dashboard.kepsek')->with('error', 'Akses fitur Audit & Log dibatasi untuk Kepala Sekolah.');
        }

        return view('kepala_sekolah.activity.show', [
            'log' => $activity,
        ]);
    }

    /**
     * Export logs to CSV
     */
    public function exportCsv(Request $request)
    {
        $user = auth()->user();
        if ($user->hasRole('Kepala Sekolah')) {
            return redirect()->route('dashboard.kepsek')->with('error', 'Akses fitur Audit & Log dibatasi untuk Kepala Sekolah.');
        }

        $query = Activity::query();

        // Apply same filters as index
        if ($request->filled('type')) {
            $query->where('log_name', $request->type);
        }
        if ($request->filled('dari_tanggal')) {
            $query->whereDate('created_at', '>=', $request->dari_tanggal);
        }
        if ($request->filled('sampai_tanggal')) {
            $query->whereDate('created_at', '<=', $request->sampai_tanggal);
        }

        $logs = $query->with('causer')->orderBy('created_at', 'desc')->get();

        $filename = 'audit_logs_' . now()->format('Ymd_His') . '.csv';

        $callback = function() use ($logs) {
            echo "\xFF\xFE"; // UTF-16LE BOM
            
            $headerRow = "Tanggal\tJenis\tDilakukan Oleh\tDeskripsi\tProperti\n";
            echo mb_convert_encoding($headerRow, 'UTF-16LE', 'UTF-8');
            
            foreach ($logs as $log) {
                $properties = json_encode($log->properties ?? []);
                $dataRow = (
                    ($log->created_at->format('d M Y H:i') ?? '') . "\t" .
                    ($log->log_name ?? '') . "\t" .
                    ($log->causer->nama ?? 'System') . "\t" .
                    ($log->description ?? '') . "\t" .
                    (substr($properties, 0, 50) . '...') . "\n"
                );
                echo mb_convert_encoding($dataRow, 'UTF-16LE', 'UTF-8');
            }
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-16LE',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
