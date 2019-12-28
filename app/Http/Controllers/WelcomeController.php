<?php

namespace App\Http\Controllers;

use App\Services\WelcomeService;

class WelcomeController extends Controller
{
    protected WelcomeService $welcomeService;

    public function __construct(WelcomeService $welcomeService)
    {
        $this->welcomeService = $welcomeService;
    }

    public function __invoke()
    {
        $this->welcomeService->welcome();
    }
}
