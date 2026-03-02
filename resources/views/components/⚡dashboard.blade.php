<?php

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Native\Laravel\Facades\SecureStorage;
use App\Support\AuthStorage;

new class extends Component {
    public int $notificationCount = 0;
    public int $studentCount = 0;
    public int $homeworkCount = 0;

    public function mount()
    {
        $this->getNotificationCountAndStudentCount();
    }

    public function getNotificationCountAndStudentCount()
    {
        $this->notificationCount = 0;
        $this->studentCount = 0;
        $this->homeworkCount = 0;

        try {
            $response = Http::timeout(20)
                ->withToken(AuthStorage::get('auth_token'))
                ->acceptJson()
                ->post(config('services.school_api.url') . '/notificationcount-studentcount');

            if ($response->json('status') === 'false' || $response->failed()) {
                $message = $response->json('message') ?? 'Unable to fetch notification count and student count.';

                $this->dispatch('toast', [
                    'type' => 'error',
                    'message' => $message,
                ]);

                $this->notificationCount = 0;
                $this->studentCount = 0;
                $this->homeworkCount = 0;
                return;
            }

            $this->notificationCount = $response->json('data.notificationsCount') ?? 0;
            $this->studentCount = $response->json('data.studentsCount') ?? 0;
            $this->homeworkCount = $response->json('data.homeworksCount') ?? 0;
        } catch (\Throwable $e) {
            report($e);

            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'Something went wrong. Please try again.',
            ]);

            $this->notificationCount = 0;
            $this->studentCount = 0;
        } finally {
        }
    }

    public function goToMyChilds()
    {
        return $this->redirectRoute('parent.select-childs');
    }

    public function goToNotifications()
    {
        return $this->redirectRoute('parent.notifications');
    }
};
?>

<div class="relative">



    <!-- 📱 Main App -->
    <div class="min-h-screen bg-gray-100 flex flex-col pointer-events-auto">

        <!-- Top Header -->
        @livewire('header', [
            'title' => 'Dashboard',
            'showLogout' => true,
            'name' => $authName,
            'showBack' => false,
        ])



        <!-- Main Content -->
        <main class="flex-1 p-4 space-y-4 pb-20">

            <!-- Quick Stats -->
            <div class="grid grid-cols-3 gap-4">

                <!-- Homework -->
                <div class="bg-white rounded-xl shadow p-4 text-center cursor-pointer" wire:click="goToMyChilds">
                    <p class="text-xs text-gray-500">Today's Homework</p>
                    <p class="text-2xl font-bold text-gray-800">
                        {{ $homeworkCount }}
                    </p>
                </div>

                <!-- Notifications -->
                <div class="bg-white rounded-xl shadow p-4 text-center cursor-pointer" wire:click="goToNotifications">
                    <p class="text-xs text-gray-500">Today's Notifications</p>
                    <p class="text-2xl font-bold text-gray-800">
                        {{ $notificationCount }}
                    </p>
                </div>

                <!-- Students -->
                <div class="bg-white rounded-xl shadow p-4 text-center cursor-pointer
               hover:bg-indigo-50 transition"
                    wire:click="goToMyChilds">

                    <p class="text-xs text-gray-500">Your Students</p>
                    <p class="text-2xl font-bold text-gray-800">
                        {{ $studentCount }}
                    </p>
                </div>

            </div>

            <!-- Actions -->
            <div class="space-y-6">



                <!-- My Classes Section -->
                <div>
                    <div class="flex items-center gap-2 mb-3 px-1">
                        <span class="text-lg">🏫</span>
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                            My Notifications
                        </h3>
                    </div>

                    <div class="bg-white rounded-xl shadow divide-y mb-5">
                        <button wire:click="goToMyChilds"
                            class="w-full flex items-center gap-3 px-4 py-4 hover:bg-gray-50 cursor-pointer">
                            <div
                                class="h-10 w-10 flex items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                👩‍🏫
                            </div>

                            <div class="text-left">
                                <p class="font-medium text-gray-800">View Homeworks</p>
                                <p class="text-xs text-gray-500">Check your child's homeworks</p>
                            </div>
                        </button>
                    </div>

                    <div class="bg-white rounded-xl shadow divide-y">
                        <button wire:click="goToNotifications"
                            class="w-full flex items-center gap-3 px-4 py-4 hover:bg-gray-50">
                            <div
                                class="h-10 w-10 flex items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                👩‍🏫
                            </div>

                            <div class="text-left">
                                <p class="font-medium text-gray-800">View Notifications</p>
                                <p class="text-xs text-gray-500">Latest updates and notices</p>
                            </div>
                        </button>
                    </div>
                </div>

            </div>


        </main>

        <!-- Bottom Navigation -->
        @include('includes.bottom_nav')

    </div>
</div>
