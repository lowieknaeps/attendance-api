<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Actions\Action;

class Login extends BaseLogin
{
    protected static bool $shouldRegisterNavigation = false;
    protected function hasLoginForm(): bool
    {
        return false;
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('kdgLogin')
                ->label('Log in met KdG')
                ->url(route('auth.redirect'))
                ->extraAttributes([
                    'class' => '
                        w-full
                        h-12
                        mt-6
                        flex items-center justify-center
                        font-semibold
                        rounded-lg
                        border

                        bg-black text-white border-black
                        hover:bg-gray-800

                        dark:bg-white dark:text-black dark:border-white
                        dark:hover:bg-gray-200

                        transition
                    ',
                ]),
        ];
    }
}
