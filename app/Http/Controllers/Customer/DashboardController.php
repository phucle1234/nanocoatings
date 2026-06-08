<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $headerWhite = 'header-secondary';
        $user = Auth::user();
        return view('customer.layout.dashboard', compact('user', 'headerWhite'));
    }
}
