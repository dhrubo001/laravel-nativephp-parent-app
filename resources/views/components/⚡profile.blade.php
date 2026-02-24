<?php

use Livewire\Component;
use App\Support\AuthStorage;
use Illuminate\Support\Facades\Http;

new class extends Component {
    public $name;
    public $email;
    public $mobile;
    public $emailOtpSent = false;

    public $new_email; // new email input
    public $email_otp; // OTP input
    public $loadingEmailOtp = false;

    // Password section
    public $current_password = '';
    public $new_password = '';
    public $new_password_confirmation = '';

    public $loadingProfile = false;
    public $loadingPassword = false;

    public function mount()
    {
        $this->name = AuthStorage::get('name');
        $this->getProfile();
    }

    public function getProfile()
    {
        try {
            $response = Http::withToken(AuthStorage::get('auth_token'))->get(config('services.school_api.url') . '/profile');

            if ($response->failed()) {
                $this->toast('Failed to fetch profile details.', 'error');
                return;
            }

            $data = $response->json('data');

            $this->name = $data['name'] ?? $this->name;
            $this->email = $data['email'] ?? $this->email;
            $this->mobile = $data['phone'] ?? $this->mobile;
        } catch (\Throwable $e) {
            report($e);
            $this->toast('Unable to fetch profile details.', 'error');
        }
    }

    /* =========================
        UPDATE PROFILE DETAILS
    ========================== */
    public function updateProfile()
    {
        $this->validate([
            'name' => 'required|min:3',
            'email' => 'required|email',
            'mobile' => 'required|min:10',
        ]);

        $this->loadingProfile = true;

        try {
            $response = Http::withToken(AuthStorage::get('auth_token'))->post(config('services.school_api.url') . '/update-profile', [
                'name' => $this->name,
                'email' => $this->email,
                'mobile' => $this->mobile,
            ]);

            if ($response->failed()) {
                $this->toast('Failed to update profile.', 'error');
                return;
            }

            AuthStorage::set('name', $this->name);

            $this->toast('Profile updated successfully.', 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->toast('Something went wrong.', 'error');
        } finally {
            $this->loadingProfile = false;
        }
    }

    /* =========================
        RESET PASSWORD
    ========================== */
    public function resetPassword()
    {
        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|same:new_password_confirmation',
        ]);

        $this->loadingPassword = true;

        try {
            $response = Http::withToken(AuthStorage::get('auth_token'))->post(config('services.school_api.url') . '/change-password', [
                'current_password' => $this->current_password,
                'password' => $this->new_password,
            ]);

            if ($response->failed()) {
                $this->toast('Current password is incorrect.', 'error');
                return;
            }

            $this->toast('Password changed successfully.', 'success');

            $this->reset('current_password', 'new_password', 'new_password_confirmation');
        } catch (\Throwable $e) {
            report($e);
            $this->toast('Unable to change password.', 'error');
        } finally {
            $this->loadingPassword = false;
        }
    }

    protected function toast(string $message, string $type = 'error')
    {
        $this->dispatch('toast', compact('type', 'message'));
    }

    public function sendEmailOtp()
    {
        $this->validate([
            'new_email' => 'required|email|different:email',
        ]);

        $this->loadingEmailOtp = true;

        try {
            $response = Http::withToken(AuthStorage::get('auth_token'))->post(config('services.school_api.url') . '/send-email-otp', [
                'email' => $this->new_email,
            ]);

            // 1️⃣ HTTP / network failure
            if ($response->failed()) {
                $this->toast($response->json('message') ?? 'Failed to send OTP.', 'error');
                return;
            }

            // 2️⃣ API-level success
            if ($response->json('status') === true) {
                $this->emailOtpSent = true;

                $this->toast($response->json('message') ?? 'OTP sent to your new email address.', 'success');
                return;
            }

            // 3️⃣ API-level failure (status=false)
            $this->toast($response->json('message') ?? 'Unable to send OTP.', 'error');
        } catch (\Throwable $e) {
            report($e);

            $this->toast('Unable to send OTP. Please try again.', 'error');
        } finally {
            $this->loadingEmailOtp = false;
        }
    }

    public function verifyEmailOtp()
    {
        try {
            $response = Http::withToken(AuthStorage::get('auth_token'))->post(config('services.school_api.url') . '/verify-email-otp', [
                'otp' => $this->email_otp,
                'email' => $this->new_email,
            ]);

            // HTTP / network error
            if ($response->failed() || $response->json('status') === false) {
                $this->toast($response->json('message') ?? 'OTP verification failed.', 'error');
                return;
            }

            // Success
            if ($response->json('status') === true) {
                // Update local state
                $this->email = $response->json('email');
                AuthStorage::set('email', $this->email);

                // Reset OTP flow
                $this->reset('email_otp');
                $this->new_email = '';
                $this->emailOtpSent = false;

                $this->getProfile();

                $this->toast('Email updated successfully.', 'success');
                return;
            }

            // API-level failure
            $this->toast($response->json('message') ?? 'Invalid OTP.', 'error');
        } catch (\Throwable $e) {
            report($e);
            $this->toast('Unable to verify OTP. Try again.', 'error');
        }
    }
};

?>

<div>

    <div wire:teleport="body">
        <div wire:loading wire:target="updateProfile" class="fixed inset-0 z-[9999] bg-white/80 backdrop-blur-sm">
            <div class="absolute inset-0 flex flex-col items-center justify-center">
                <div class="h-12 w-12 rounded-full border-4 border-indigo-600 border-t-transparent animate-spin"></div>
                <p class="text-sm mt-3">Saving profile…</p>
            </div>
        </div>

        <div wire:loading wire:target="sendEmailOtp" class="fixed inset-0 z-[9999] bg-white/80 backdrop-blur-sm">
            <div class="absolute inset-0 flex flex-col items-center justify-center">
                <div class="h-12 w-12 rounded-full border-4 border-blue-600 border-t-transparent animate-spin"></div>
                <p class="text-sm mt-3">Sending OTP…</p>
            </div>
        </div>


        <div wire:loading wire:target="verifyEmailOtp" class="fixed inset-0 z-[9999] bg-white/80 backdrop-blur-sm">
            <div class="absolute inset-0 flex flex-col items-center justify-center">
                <div class="h-12 w-12 rounded-full border-4 border-blue-600 border-t-transparent animate-spin"></div>
                <p class="text-sm mt-3">Verifying OTP…</p>
            </div>
        </div>

        <div wire:loading wire:target="resetPassword" class="fixed inset-0 z-[9999] bg-white/80 backdrop-blur-sm">
            <div class="absolute inset-0 flex flex-col items-center justify-center">
                <div class="h-12 w-12 rounded-full border-4 border-red-600 border-t-transparent animate-spin"></div>
                <p class="text-sm mt-3">Updating password…</p>
            </div>
        </div>
    </div>

    <div class="min-h-screen bg-gray-100 pb-24">

        @livewire('header', [
            'title' => 'Profile',
            'showBack' => true,
            'showLogout' => false,
            'name' => $authName,
        ])





        <div class="p-4 space-y-6">

            <!-- =====================
                    PROFILE DETAILS
                ====================== -->
            <div class="bg-white rounded-2xl shadow p-4 space-y-4">
                <h2 class="text-base font-semibold text-gray-800">Basic Profile</h2>

                <div>
                    <label class="text-sm text-gray-600">Name</label>
                    <input type="text" wire:model.defer="name"
                        class="mt-1 w-full px-4 py-3 rounded-xl border focus:ring focus:ring-indigo-200">
                    @error('name')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button wire:click="updateProfile" wire:loading.attr="disabled"
                    class="w-full bg-indigo-600 text-white py-3 rounded-xl font-semibold hover:bg-indigo-700 disabled:opacity-60">
                    <span wire:loading.remove>Save Name</span>
                    <span wire:loading>Saving...</span>
                </button>
            </div>

            <div class="bg-white rounded-2xl shadow p-4 space-y-4 border border-blue-100">
                <h2 class="text-base font-semibold text-gray-800">Update Email</h2>

                <div>
                    <label class="text-sm text-gray-600">Current Email</label>
                    <input type="email" value="{{ $email }}" disabled
                        class="mt-1 w-full px-4 py-3 rounded-xl border bg-gray-100 text-gray-500">
                </div>

                <div>
                    <label class="text-sm text-gray-600">New Email</label>
                    <input type="email" wire:model.lazy="new_email" autocomplete="false"
                        class="mt-1 w-full px-4 py-3 rounded-xl border focus:ring focus:ring-blue-200">
                    @error('new_email')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button wire:click="sendEmailOtp"
                    class="w-full bg-blue-600 text-white py-3 rounded-xl font-semibold hover:bg-blue-700">
                    Send OTP
                </button>
            </div>

            @if ($emailOtpSent)
                <div class="mt-3">
                    <label class="text-sm text-gray-600">Enter OTP</label>
                    <input type="text" wire:model.lazy="email_otp"
                        class="mt-1 w-full px-4 py-3 rounded-xl border focus:ring focus:ring-blue-200">


                    <button wire:click="verifyEmailOtp"
                        class="mt-3 w-full bg-green-600 text-white py-3 rounded-xl font-semibold">
                        Verify & Update Email
                    </button>
                </div>
            @endif

            <!-- =====================
                    PASSWORD RESET
                ====================== -->
            {{-- <div class="bg-white rounded-2xl shadow p-4 space-y-4 border border-red-100">

                <h2 class="text-base font-semibold text-gray-800">
                    Reset Password
                </h2>

                @if (session()->has('password_success'))
                    <div class="bg-green-100 text-green-700 px-4 py-2 rounded-lg text-sm">
                        {{ session('password_success') }}
                    </div>
                @endif

                <div>
                    <label class="text-sm text-gray-600">Current Password</label>
                    <input type="password" wire:model.defer="current_password"
                        class="mt-1 w-full px-4 py-3 rounded-xl border focus:ring focus:ring-red-200">
                    @error('current_password')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">New Password</label>
                    <input type="password" wire:model.defer="new_password"
                        class="mt-1 w-full px-4 py-3 rounded-xl border focus:ring focus:ring-red-200">
                    @error('new_password')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">Confirm New Password</label>
                    <input type="password" wire:model.defer="new_password_confirmation"
                        class="mt-1 w-full px-4 py-3 rounded-xl border focus:ring focus:ring-red-200">
                </div>

                @error('password')
                    <div class="bg-red-100 text-red-600 px-4 py-2 rounded-lg text-sm">
                        {{ $message }}
                    </div>
                @enderror

                <button wire:click="resetPassword" wire:loading.attr="disabled"
                    class="w-full bg-red-600 text-white py-3 rounded-xl font-semibold
                           hover:bg-red-700 disabled:opacity-60">
                    <span wire:loading.remove>Change Password</span>
                    <span wire:loading>Updating...</span>
                </button>
            </div> --}}

        </div>

        <!-- Bottom Navigation -->
        @include('includes.bottom_nav')
    </div>
</div>
