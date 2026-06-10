<x-guest-layout>
    <div class="relative bg-white overflow-hidden min-h-screen flex items-center justify-center">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            <div class="text-center">
                <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
                    <span class="block">Selamat Datang di</span>
                    <span class="block text-black">Restoran Kami</span>
                </h1>
                
                <p class="mt-3 text-base text-gray-600 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl">
                    Nikmati hidangan lezat dengan bahan berkualitas terbaik. Silakan lihat katalog menu lengkap kami secara online melalui tombol di bawah ini.
                </p>
                
                <div class="mt-5 sm:mt-8 flex justify-center">
                    <div class="rounded-md shadow">
                        <a href="{{ route('public.menu') }}" class="w-full flex items-center justify-center px-12 py-3 border border-transparent text-base font-medium rounded-md text-white bg-black hover:bg-gray-800 md:py-4 md:text-lg md:px-16 transition-colors">
                            Lihat Menu
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
