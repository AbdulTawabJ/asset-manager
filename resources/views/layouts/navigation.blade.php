<nav class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <!-- Left: Logo and Title with Role -->
            <div class="flex items-center space-x-4">
                <a href="{{ route('dashboard') }}">
                    <img src="{{ asset('images/tmf-logo.png') }}" alt="TMF Logo" class="h-12 w-auto mx-auto">
                </a>
                <div class="text-xl font-bold text-gray-800">
                    TMF Assets Management
                     <!-- - {{ ucfirst(Auth::user()->role) }} Portal -->
                </div>
            </div>

            <!-- Right: Username and Logout Button -->
            <div class="flex items-center space-x-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"  class="bg-gray-100 text-red hover:bg-red-600 hover:text-white px-3 py-2 rounded-full text-sm shadow hover:shadow-xl">
                        {{ Auth::user()->full_name ?? Auth::user()->email }}
                        <i class="fa-solid fa-power-off ml-2"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
