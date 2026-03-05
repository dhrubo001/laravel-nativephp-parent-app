<?php

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Native\Laravel\Facades\SecureStorage;
use App\Support\AuthStorage;

new class extends Component {
    public string $email = '';
    public string $password = '';

    protected function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ];
    }

    public function login()
    {
        $this->validate();

        $device = device_info();

        try {
            $response = Http::asJson()
                ->acceptJson()
                ->timeout(20)
                ->post('http://dhrubo.info/school-app/api/login', [
                    'email' => $this->email,
                    'password' => (string) $this->password,
                    'role' => 'parent',
                    'device_name' => $device['device'],
                    'platform' => $device['platform'],
                    'device_type' => $device['is_mobile'] ? 'mobile' : ($device['is_tablet'] ? 'tablet' : 'desktop'),
                ]);

            if ($response->failed()) {
                $this->addError('email', $response->json('message') ?? 'Invalid credentials');
                return;
            }

            AuthStorage::set('auth_token', $response->json('token'));
            AuthStorage::set('school_id', data_get($response->json(), 'user.school_id'));
            AuthStorage::set('name', data_get($response->json(), 'user.name'));
            AuthStorage::set('user_id', data_get($response->json(), 'user.id'));

            return redirect('/dashboard');
        } catch (\Throwable $e) {
            logger()->error('NativePHP Login Error', [
                'error' => $e->getMessage(),
            ]);

            $this->addError('email', 'Server not reachable from app.');
        }
    }
};
?>

<div>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-600 to-green-500">
        <div class="bg-white w-full max-w-md rounded-2xl shadow-xl p-8">

            <div class="text-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800">Parent Login</h1>
                <p class="text-gray-500 mt-1">Sign in to manage notifications</p>
            </div>

            <form wire:submit.prevent="login" class="space-y-5">

                <div>
                    <label class="text-sm text-gray-600">Email</label>
                    <input type="email" wire:model.defer="email"
                        class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring focus:ring-indigo-200 focus:outline-none"
                        placeholder="teacher@school.com">
                    @error('email')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">Password</label>
                    <input type="password" wire:model.defer="password"
                        class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring focus:ring-indigo-200 focus:outline-none"
                        placeholder="••••••••">
                    @error('password')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" wire:loading.attr="disabled" wire:target="login"
                    class="w-full flex items-center justify-center gap-2
           bg-indigo-600 text-white py-2 rounded-lg
           hover:bg-indigo-700 transition font-semibold
           disabled:opacity-60 disabled:cursor-not-allowed">
                    <!-- Spinner -->
                    <svg wire:loading wire:target="login" class="animate-spin h-5 w-5 text-white"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8z">
                        </path>
                    </svg>

                    <!-- Text -->
                    <span wire:loading.remove wire:target="login">
                        Login
                    </span>

                    <span wire:loading wire:target="login">
                        Logging in...
                    </span>
                </button>

            </form>

            <div class="text-center text-sm text-gray-400 mt-6">
                © {{ date('Y') }} School Notification System
            </div>
        </div>
    </div>
</div>
