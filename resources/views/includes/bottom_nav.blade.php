<nav
    class="bg-white border-t flex justify-around py-2 fixed bottom-0 inset-x-0 z-40
           pb-[env(safe-area-inset-bottom)]">

    <!-- Home -->
    <a href="{{ route('parent.dashboard') }}"
        class="flex flex-col items-center gap-1
              {{ request()->routeIs('parent.dashboard') ? 'text-indigo-600' : 'text-gray-500 hover:text-indigo-600' }}">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M3 9.75L12 4l9 5.75V20a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1V9.75z" />
        </svg>
        <span class="text-xs">Home</span>
    </a>

    <a href="{{ route('parent.notifications') }}"
        class="relative flex flex-col items-center gap-1
              {{ request()->routeIs('parent.notifications*') ? 'text-indigo-600' : 'text-gray-500 hover:text-indigo-600' }}">

        <!-- Bell Icon -->
        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 01-6 0" />
        </svg>

        <!-- Badge -->
        @livewire('notification-badge')

        <span class="text-xs">Alerts</span>
    </a>



    <!-- Profile -->
    <a href="{{ route('parent.profile') }}"
        class="flex flex-col items-center gap-1
              {{ request()->routeIs('parent.profile*') ? 'text-indigo-600' : 'text-gray-500 hover:text-indigo-600' }}">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M5.121 17.804A9 9 0 1118.88 17.8M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <span class="text-xs">Profile</span>
    </a>

</nav>
