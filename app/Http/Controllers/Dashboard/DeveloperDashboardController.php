<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\RoleService;

class DeveloperDashboardController extends Controller
{
    public function index()
    {
        // Developer dashboard only allowed in non-production and only for the real Developer account
        if (app()->environment('production')) {
            abort(403, 'Developer dashboard not available in production.');
        }

        if (! RoleService::isRealDeveloper()) {
            abort(403, 'AKSES DITOLAK: Hanya Developer nyata yang boleh melihat halaman ini.');
        }

        return view('dashboards.developer');
    }
}
