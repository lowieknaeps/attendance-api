<x-filament-panels::auth.login-page>

    <x-slot name="form">
        <div
            class="w-full max-w-md mx-auto
                   bg-white dark:bg-gray-900
                   shadow-xl rounded-2xl
                   p-8 space-y-8">

            {{-- Title --}}
            <div class="text-center space-y-1">
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Aanwezigheid-lessen
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Log in met je KdG-account
                </p>
            </div>

            {{-- Logo --}}
            <div class="flex justify-center">
                <img
                    src="{{ asset('images/kdg-logo.png') }}"
                    class="h-12 opacity-90 dark:invert"
                    alt="KdG">
            </div>

            {{-- KdG SSO Button --}}
            <a
                href="{{ route('auth.redirect') }}"
                class="w-full inline-flex items-center justify-center
                       rounded-full px-5 py-3
                       font-semibold
                       bg-black text-white
                       dark:bg-white dark:text-black
                       border border-black dark:border-white
                       hover:bg-gray-800 dark:hover:bg-gray-200
                       transition">
                Login met KdG
            </a>

            {{-- Footer --}}
            <p class="text-center text-xs text-gray-500 dark:text-gray-400">
                Gebruik je KdG-e-mailadres om in te loggen.
            </p>

        </div>
    </x-slot>

</x-filament-panels::auth.login-page>
