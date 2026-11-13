<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class InertiaTestController extends Controller
{
    public function show()
    {
        return Inertia::render('Test', [
            'message' => 'If you can see this, Inertia + React is working.',
        ]);
    }
}