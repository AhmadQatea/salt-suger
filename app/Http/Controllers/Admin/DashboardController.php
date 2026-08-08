<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OrderManagementService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected OrderManagementService $orders,
    ) {}

    /**
     * Display the admin dashboard.
     */
    public function index(): View
    {
        return view('admin.dashboard', [
            'orderSummary' => $this->orders->dashboardSummary(),
        ]);
    }
}
