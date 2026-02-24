<?php

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use App\Support\AuthStorage;

new class extends Component {
    public int $count = 0;

    protected $listeners = [
        'notifications-read' => 'fetchCount',
    ];

    public function mount()
    {
        $this->fetchCount();
    }

    public function fetchCount()
    {
        try {
            $response = Http::withToken(AuthStorage::get('auth_token'))->post(config('services.school_api.url') . '/notifications/unread-count');

            if ($response->successful()) {
                $this->count = (int) $response->json('count', 0);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
};
?>

<div wire:poll.10s="fetchCount">
    @if ($count > 0)
        <span
            class="absolute -top-1 -right-2 min-w-[18px] h-[18px]
                   px-1 text-[10px] font-bold
                   flex items-center justify-center
                   rounded-full bg-red-600 text-white">
            {{ $count > 9 ? '9+' : $count }}
        </span>
    @endif
</div>
