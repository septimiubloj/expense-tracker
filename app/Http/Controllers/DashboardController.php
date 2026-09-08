<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('dashboard', [
            'books' => $request->user()->books()->orderBy('name')->get(['id', 'name', 'currency_code', 'timezone']),
        ]);
    }
}
