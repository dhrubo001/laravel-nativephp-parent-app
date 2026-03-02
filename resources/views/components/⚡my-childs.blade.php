<?php

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use App\Support\AuthStorage;
use Carbon\Carbon;

new class extends Component {
    public $students = [];
    public $selectedStudentId = null;
    public $selectedStudentName = '';
    public $notifications = [];
    public string $selectedDate = '';

    // MUST be public in Livewire
    public string $apiBaseUrl = '';

    public function mount()
    {
        $this->apiBaseUrl = config('services.school_api.url');
        $this->selectedDate = Carbon::now()->format('Y-m-d');

        $this->getAllStudents();
    }

    protected function api()
    {
        return Http::timeout(20)->withToken(AuthStorage::get('auth_token'))->acceptJson();
    }

    public function getAllStudents()
    {
        try {
            $response = $this->api()->post($this->apiBaseUrl . '/students-list');

            if ($response->failed() || $response->json('status') === 'false') {
                $this->toast($response->json('message') ?? 'Unable to fetch students linked to your account.');
                return;
            }

            $this->students = $response->json('data', []);
        } catch (\Throwable $e) {
            report($e);
            $this->toast('Something went wrong. Please try again.');
        }
    }

    public function selectStudent(int $studentId, string $studentName)
    {
        $this->selectedStudentId = $studentId;
        $this->selectedStudentName = $studentName;

        $this->loadNotifications();
    }

    // public function updatedSelectedDate($value)
    // {
    //     if (!$this->selectedStudentId) {
    //         $this->toast('Please select a student first.', 'warning');
    //         $this->selectedDate = now()->format('Y-m-d');
    //         return;
    //     }

    //     $this->loadNotifications();
    // }

    public function loadNotifications()
    {
        $this->notifications = [];

        try {
            $response = $this->api()->post($this->apiBaseUrl . '/students/notifications', [
                'student_id' => $this->selectedStudentId,
                'date' => $this->selectedDate,
            ]);

            if ($response->failed() || $response->json('status') === 'false') {
                $this->toast($response->json('message') ?? 'Unable to fetch notifications.');
                return;
            }

            $this->notifications = $response->json('data.homeworks', []);
            //dd($this->notifications);
        } catch (\Throwable $e) {
            report($e);
            $this->toast('Something went wrong. Please try again.');
        }
    }

    protected function toast(string $message, string $type = 'error')
    {
        $this->dispatch('toast', compact('type', 'message'));
    }
};

?>

<div wire:key="parent-select-childs-page">

    <!-- Full Page Loader -->
    <div wire:teleport="body">
        <div wire:loading wire:target="selectStudent,loadNotifications"
            class="fixed inset-0 z-[100000] bg-white/80 backdrop-blur-sm pointer-events-auto">

            <!-- TRUE CENTER (safe-area proof) -->
            <div
                class="absolute top-1/2 left-1/2
                   -translate-x-1/2 -translate-y-1/2
                   flex flex-col items-center space-y-4">

                <div class="h-12 w-12 rounded-full border-4 border-blue-500 border-t-transparent animate-spin"></div>

                <p class="text-sm font-medium text-gray-700">
                    Loading homeworks...
                </p>
            </div>

        </div>
    </div>


    <div class="min-h-screen bg-gray-100 flex flex-col pointer-events-auto pb-20">

        <!-- Top Header -->
        @livewire('header', [
            'title' => 'My Child(s)',
            'showLogout' => true,
            'name' => $authName,
            'showBack' => true,
        ])




        <div class="p-4">

            <div class="mb-5 flex items-start justify-between gap-4">

                <!-- Left: Title -->
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">
                        👨‍👩‍👧 Your Children(s)
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Tap a student to view today’s homeworks
                    </p>
                </div>

                <!-- Right: Date Picker -->
                <div class="shrink-0">
                    <input type="date" @disabled(!$selectedStudentId) wire:model.defer="selectedDate"
                        wire:change="loadNotifications" max="{{ now()->format('Y-m-d') }}"
                        class="rounded-xl border border-gray-300 px-3 py-2 text-sm" />
                </div>

            </div>

            @if (empty($students))
                <div class="text-gray-500 text-sm">
                    No students linked to your account, contact your school admin
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($students as $student)
                        <div wire:click="selectStudent({{ $student['id'] }}, '{{ $student['name'] }}')"
                            wire:key="student-{{ $student['id'] }}"
                            class="flex items-center justify-between
                           rounded-2xl border
                           bg-white px-4 py-4
                           shadow-sm
                           cursor-pointer
                           transition-all
                           {{ $selectedStudentId === $student['id'] ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}
                           active:scale-[0.97]">
                            <div class="min-w-0">
                                <div class="font-semibold text-gray-900 truncate">
                                    {{ $student['name'] ?? '-' }}
                                </div>
                                <div class="text-sm text-gray-500 mt-0.5">
                                    Class: {{ $student['student_class']['name'] ?? 'N/A' }}
                                </div>
                            </div>

                            <div class="text-gray-400 text-xl">
                                ›
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>


        @if ($selectedStudentId)
            <div class="px-4 pb-6 mt-6">

                <div class="mb-4">
                    <h3 class="text-base font-semibold text-gray-800">
                        🔔 {{ $selectedStudentName }}’s Homeworks
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Showing homeworks for {{ date('F j, Y', strtotime($selectedDate)) }}
                    </p>
                </div>

                @if (empty($notifications))
                    <div class="text-sm text-gray-500 bg-white rounded-xl p-4 shadow-sm">
                        No homeworks found
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($notifications as $notification)
                            <div class="relative rounded-xl bg-white p-4 shadow-sm border border-gray-200">

                                <!-- Left accent bar -->
                                <span class="absolute left-0 top-0 h-full w-1 rounded-l-xl bg-blue-500"></span>

                                <div class="pl-3">

                                    <div class="mt-2 text-sm text-gray-600 leading-relaxed">
                                        <div class="flex flex-wrap items-center gap-x-2">
                                            <span class="font-medium text-gray-800">
                                                {{ $notification['subject']['subject_name'] ?? 'Subject Not Found' }}
                                            </span>

                                            <span class="text-gray-400">•</span>

                                            <span>
                                                Period {{ $notification['period']['period_number'] ?? '-' }}
                                            </span>

                                            <span>
                                                <b>{{ $notification['teacher']['name'] ?? '' }}</b>
                                            </span>
                                        </div>

                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ $notification['period']['start_time'] ?? '--:--' }}
                                            –
                                            {{ $notification['period']['end_time'] ?? '--:--' }}
                                        </div>
                                    </div>

                                    <div class="font-medium text-gray-900">
                                        {{ $notification['homework'] ?? 'Notification' }}
                                    </div>



                                    <div class="text-xs text-gray-400 mt-2">
                                        {{ date('F j, Y', strtotime($notification['due_date'])) ?? '' }}
                                    </div>
                                </div>

                            </div>
                        @endforeach
                    </div>
                @endif

            </div>
        @endif

        <!-- Bottom Navigation -->
        @include('includes.bottom_nav')

    </div>
</div>
