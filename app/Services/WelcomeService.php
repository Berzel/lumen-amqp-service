<?php

namespace App\Services;

use App\Events\Welcomed;

class WelcomeService
{
    public function welcome()
    {
        event(new Welcomed());
    }
}
