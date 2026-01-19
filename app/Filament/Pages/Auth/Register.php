<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Register as BaseRegister;
use Filament\Forms;
use App\Models\Attendance;

class Register extends BaseRegister
{
    protected static bool $shouldRegisterNavigation = false;

}
