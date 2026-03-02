<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? config('app.name') }}</title>

    <!-- Tailwind CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    @livewireStyles

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] {
            display: none
        }
    </style>
</head>

<body class="nativephp-safe-area">

    @yield('content')


    <!-- Toast -->
    <div x-data="{
        show: false,
        message: '',
        type: 'success'
    }"
        x-on:toast.window="
        const p = $event.detail?.[0] ?? {};
        message = p.message ?? '';
        type = p.type ?? 'success';
        show = true;
        setTimeout(() => show = false, 3000);
    "
        x-on:redirect-after-toast.window="
        const p = $event.detail?.[0] ?? {};
        if(p.url){
            setTimeout(() => window.location.href = p.url, p.delay ?? 2000);
        }
    "
        x-show="show" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-y-full opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-full opacity-0" x-cloak class="fixed inset-x-0 bottom-0 z-50">
        <div class="w-full px-4 py-4 text-white text-center text-sm font-medium"
            :class="{
                'bg-green-600': type === 'success',
                'bg-red-600': type === 'error',
                'bg-yellow-500': type === 'warning'
            }">
            <span x-text="message"></span>
        </div>
    </div>




    @livewireScripts


    <script>
        document.addEventListener('livewire:init', () => {


            /* ------------------------------
             | Web Notification setup
             |------------------------------ */

            // Ask permission only once
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }

            Livewire.on('web-notification', ({
                title,
                body
            }) => {
                if (!('Notification' in window)) return;
                if (Notification.permission !== 'granted') return;

                new Notification(title, {
                    body,
                    icon: '', // optional
                });
            });

        });
    </script>

</body>

</html>
