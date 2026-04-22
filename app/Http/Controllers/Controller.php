<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponsable;
use App\Traits\AuditsActions;

abstract class Controller
{
    use ApiResponsable;
    use AuditsActions;
}
