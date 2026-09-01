<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * Every controller action authorises. A ChecksAuthorization test asserts that
 * no public controller method exists without an authorize() call or an explicit
 * exemption, so "we forgot the policy" cannot ship.
 */
abstract class Controller
{
    use AuthorizesRequests;
    use ValidatesRequests;
}
