<?php

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use App\Support\AuthStorage;

new class extends Component {
    public array $notifications = [];
    public bool $visible = false;

    public function mount()
    {
        $this->fetchNotifications();
    }

    public function fetchNotifications()
    {
        try {
            $response = Http::withToken(AuthStorage::get('auth_token'))->post(config('services.school_api.url') . '/highligted/notifications');

            if ($response->failed()) {
                return;
            }

            $data = $response->json('data.notifications', []);

            if (!empty($data)) {
                $this->notifications = $data;
                $this->visible = true;
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function dismiss(int $id)
    {
        // Local dismiss (UI-only)
        $this->notifications = collect($this->notifications)->reject(fn($n) => $n['id'] === $id)->values()->toArray();

        if (empty($this->notifications)) {
            $this->visible = false;
        }
    }
};
?>


<div>
    @if ($visible && count($notifications))
        <div class="sticky top-0 z-[9999] bg-gray-100/95 backdrop-blur">

            <div class="space-y-2 px-3 py-2">

                @foreach ($notifications as $notice)
                    @php
                        $styles = [
                            'info' => [
                                'bg' => 'bg-blue-50',
                                'border' => 'border-blue-400',
                                'text' => 'text-blue-800',
                                'icon' => 'ℹ️',
                            ],
                            'success' => [
                                'bg' => 'bg-green-50',
                                'border' => 'border-green-400',
                                'text' => 'text-green-800',
                                'icon' => '✅',
                            ],
                            'warning' => [
                                'bg' => 'bg-yellow-50',
                                'border' => 'border-yellow-400',
                                'text' => 'text-yellow-900',
                                'icon' => '⚠️',
                            ],
                            'danger' => [
                                'bg' => 'bg-red-50',
                                'border' => 'border-red-400',
                                'text' => 'text-red-800',
                                'icon' => '🚨',
                            ],
                        ];

                        $ui = $styles[$notice['type']] ?? $styles['info'];
                    @endphp

                    <div
                        class="relative flex gap-3 p-3 rounded-xl border-l-4 shadow-sm
                               {{ $ui['bg'] }} {{ $ui['border'] }} {{ $ui['text'] }}">

                        <!-- Icon -->
                        <div class="text-xl leading-none mt-0.5">
                            {{ $ui['icon'] }}
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            @if (!empty($notice['title']))
                                <p class="font-semibold text-sm leading-tight mb-0.5">
                                    {{ $notice['title'] }}
                                </p>
                            @endif

                            <p class="text-xs leading-snug line-clamp-3">
                                {{ $notice['message'] }}
                            </p>
                        </div>

                        <!-- Close -->
                        @if ($notice['is_dismissible'])
                            <button wire:click="dismiss({{ $notice['id'] }})"
                                class="absolute top-2 right-2 h-7 w-7 rounded-full
                                       flex items-center justify-center
                                       text-sm font-bold
                                       hover:bg-black/10 focus:outline-none">
                                ✕
                            </button>
                        @endif
                    </div>
                @endforeach

            </div>
        </div>
    @endif
</div>
