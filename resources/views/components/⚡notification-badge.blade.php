<?php

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use App\Support\AuthStorage;
use Native\Laravel\Facades\Notification as NativeNotification;

new class extends Component {
    public int $count = 0;
    protected int $previousCount = 0;

    protected $listeners = [
        'notifications-read' => 'fetchCount',
    ];

    public function mount()
    {
        //NativeNotification::send(title: 'Test', body: 'Native notification test');

        $this->fetchCount(true); // silent initial fetch
    }

    public function fetchCount(bool $silent = false)
    {
        try {
            $response = Http::withToken(AuthStorage::get('auth_token'))->post(config('services.school_api.url') . '/notifications/unread-count');

            if (!$response->successful()) {
                return;
            }

            $newCount = (int) $response->json('count', 0);

            if (!$silent && $newCount > $this->count) {
                $this->notifyUser($newCount - $this->count);
            }

            $this->previousCount = $this->count;
            $this->count = $newCount;
            // dd($this->count);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Decide Native vs Web notification
     */
    protected function notifyUser(int $newItems): void
    {
        if ($this->isNative()) {
            $this->fireNativeNotification($newItems);
        } else {
            $this->fireWebNotification($newItems);
        }
    }

    /**
     * NativePHP notification
     */
    protected function fireNativeNotification(int $newItems): void
    {
        if (!app()->bound('native')) {
            return;
        }

        NativeNotification::send(title: '📚 New Update', body: "{$newItems} new notification(s) received");
    }

    /**
     * Web notification (Browser)
     */
    protected function fireWebNotification(int $newItems): void
    {
        $this->dispatch('web-notification', [
            'title' => '📚 New Homework Alert',
            'body' => "{$newItems} new notification(s) received",
        ]);
    }

    /**
     * Environment check
     */
    protected function isNative(): bool
    {
        return app()->bound('native');
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
            {{ $count > 20 ? '20+' : $count }}
        </span>
    @endif
</div>
