<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Display the storefront landing page (pure brand & marketing showcase).
     */
    public function index(): Response
    {
        return Inertia::render('Landing/Index');
    }
}
