<?php

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Native\Laravel\Facades\SecureStorage;
use App\Support\AuthStorage;

new class extends Component {
    public string $title = 'Dashboard';
    public string $name = 'Teacher';
    public bool $showBack = false;
    public bool $showLogout = false;

    public function mount(string $title = 'Dashboard', string $name = 'Teacher', bool $showBack = false, bool $showLogout = false)
    {
        $this->title = $title;
        $this->name = $name;
        $this->showBack = $showBack;
        $this->showLogout = $showLogout;
    }

    public function logout()
    {
        try {
            $token = AuthStorage::get('auth_token');

            if (!$token) {
                return redirect()->route('teacher.login');
            }
            sleep(2);
            $response = Http::timeout(10)
                ->withToken($token) // ← Bearer token here
                ->post(config('services.school_api.url') . '/logout');

            // Optional: API may return 204 (no content)
            if ($response->failed() && $response->status() !== 204) {
                $this->addError('email', 'Logout failed. Try again.');
                return;
            }

            // Clear local secure storage
            AuthStorage::forget('auth_token');
            AuthStorage::forget('school_id');
            AuthStorage::forget('name');

            return redirect()->route('parent.login');
        } catch (\Throwable $e) {
            $this->addError('email', 'Unable to connect to server. Try again.');
        }
    }
};
?>
<div>
    <!-- 🔒 Full Page Loader (Logout) -->
    <div wire:loading wire:target="logout" class="fixed inset-0 z-[100000]
           bg-black/40 backdrop-blur-sm">

        <!-- Absolute center wrapper -->
        <div class="absolute top-1/2 left-1/2
               -translate-x-1/2 -translate-y-1/2">

            <div
                class="flex flex-col items-center justify-center
                   bg-white rounded-2xl
                   px-8 py-7
                   shadow-2xl">

                <!-- Spinner -->
                <svg class="h-10 w-10 animate-spin text-indigo-600 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4
                       l3-3-3-3v4
                       a8 8 0 00-8 8z" />
                </svg>

                <!-- Text -->
                <p class="text-sm font-semibold text-gray-700 tracking-wide">
                    Logging out…
                </p>
            </div>

        </div>
    </div>
    <header class="bg-indigo-600 text-white px-4 py-4 flex items-center justify-between shadow">

        <!-- Back Button -->


        <div class="flex items-center gap-3">

            <!-- Back Button -->
            @if ($showBack)
                <a wire:navigate href="{{ url()->previous() }}"
                    class="flex items-center justify-center h-9 w-9 rounded-full hover:bg-white/20">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
            @endif
            <!-- Title + Name -->
            <div class="flex flex-col leading-tight">
                <h1 class="text-lg font-semibold">
                    {{ $title ?? 'Dashboard' }}
                </h1>
                <p class="text-xs opacity-90">
                    {{ $name ?? 'Teacher' }}
                </p>
            </div>

        </div>


        <button wire:key='logout' wire:click="logout" wire:loading.attr="disabled" wire:target="logout"
            class="text-sm bg-white text-indigo-600 px-3 py-1.5 rounded-md font-medium
                       hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
            Logout
        </button>
    </header>


    @if (!request()->routeIs('parent.notifications*'))
        @livewire('highlighted-banner')
    @endif
</div>
