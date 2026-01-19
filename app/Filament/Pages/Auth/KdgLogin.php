<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Actions\Action;
use Illuminate\Support\HtmlString;
use Filament\Support\Colors\Color;
use Filament\Facades\Filament;

class KdgLogin extends BaseLogin
{
    protected static bool $shouldRegisterNavigation = false;

    protected function getFormSchema(): array
    {
        return [];
    }
    protected function hasRememberMe(): bool
    {
        return false;
    }
    protected function getFormActions(): array
    {
        return [
            Action::make('kdgLogin')
                ->label(new HtmlString('
                   <span class="flex items-center justify-center gap-3">
                        <span>Log in met KdG</span>
                    </span>
                '))
                ->url(route('auth.redirect'))
                ->extraAttributes([
                    'class' => 'w-full mt-2 inline-flex items-center justify-center
                                px-4 py-3 rounded-xl font-semibold
                                bg-white text-white border border-black
                                hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-black/20',
                ]),
        ];
    }
   /* protected function getHeading(): string
    {
        return 'Sign in';
    }

    protected function getSubheading(): ?string
    {
        return 'Log in met je KdG-account';
    }*/
}
