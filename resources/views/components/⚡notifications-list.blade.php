<?php

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use App\Support\AuthStorage;

new class extends Component {
    public array $notifications = [];
    public bool $visible = false;

    public function mount()
    {
        $this->getNotifications();
    }

    public function getNotifications()
    {
        try {
            $response = Http::timeout(20)
                ->withToken(AuthStorage::get('auth_token'))
                ->acceptJson()
                ->post(config('services.school_api.url') . '/notifications');

            if ($response->failed() || $response->json('status') === false) {
                $this->dispatch('toast', [
                    'type' => 'error',
                    'message' => $response->json('message') ?? 'Unable to fetch notifications.',
                ]);
                return;
            }
            //dd($response->json());
            $this->notifications = $response->json('data.notifications') ?? [];
            //dd($this->notifications);
            $this->visible = count($this->notifications) > 0;

            //  AUTO MARK AS READ (Option 2)
            if ($this->visible) {
                $this->markAsRead();
            }
        } catch (\Throwable $e) {
            report($e);

            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'Something went wrong. Please try again.',
            ]);
        }
    }

    public function markAsRead(): void
    {
        if (empty($this->notifications)) {
            return;
        }

        try {
            $notificationIds = collect($this->notifications)->pluck('id')->values()->toArray();

            Http::timeout(10)
                ->withToken(AuthStorage::get('auth_token'))
                ->acceptJson()
                ->post(config('services.school_api.url') . '/notifications/mark-read', [
                    'notification_ids' => $notificationIds,
                ]);
            $this->dispatch('notifications-read');
        } catch (\Throwable $e) {
            report($e);
        }
    }
};
?>

<div>
    <div class="min-h-screen bg-gray-100 flex flex-col pointer-events-auto">

        <!-- Top Header -->
        @livewire('header', [
            'title' => 'Notifications',
            'showLogout' => true,
            'name' => $authName,
            'showBack' => true,
        ])



        <!-- Main Content -->
        <main class="flex-1 p-4 space-y-4 pb-20">


            @if (count($notifications))
                <div class="space-y-3">
                    @foreach ($notifications as $notice)
                        <div
                            class="
                rounded-xl shadow p-4 transition
                {{ !$notice['is_read'] ? 'bg-indigo-50 border-l-4 border-indigo-600' : 'bg-white' }}
            ">
                            <div class="flex items-start justify-between gap-2">
                                <h2 class="font-semibold text-gray-800">
                                    {{ $notice['title'] }}
                                </h2>

                                {{-- Unread badge --}}
                                @if (!$notice['is_read'])
                                    <span
                                        class="shrink-0 text-[10px] px-2 py-0.5
                               rounded-full bg-indigo-600 text-white font-semibold">
                                        NEW
                                    </span>
                                @endif
                            </div>

                            <p class="text-sm text-gray-600 mt-1">
                                {{ $notice['message'] }}
                            </p>

                            <p class="text-xs text-gray-400 mt-2">
                                {{ \Carbon\Carbon::parse($notice['created_at'])->format('M d, Y') }}
                                · {{ \Carbon\Carbon::parse($notice['created_at'])->diffForHumans() }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center text-gray-500 mt-10">
                    No notifications found.
                </div>
            @endif

        </main>

        <!-- Bottom Navigation -->
        @include('includes.bottom_nav')

    </div>
</div>
