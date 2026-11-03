<!-- resources/views/components/header.blade.php -->
<header class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 gap-4">
            
            <!-- Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-2 flex-shrink-0">
                <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center text-white text-xl shadow-md">
                    <i class="fa-solid fa-map-location-dot"></i>
                </div>
                <div>
                    <span class="text-xl font-black text-gray-900 tracking-tight leading-none block">MGazar<span class="text-emerald-600">.mn</span></span>
                    <span class="text-[10px] text-gray-500 font-semibold tracking-wider uppercase block">ИНТЕРАКТИВ ЗАРЫН САЙТ</span>
                </div>
            </a>

            <!-- Search Bar -->
            <div class="flex-1 max-w-xl hidden md:block">
                <div class="relative">
                    <input type="text" 
                           x-model="searchQuery" 
                           placeholder="Дүүрэг, байршил эсвэл кадастрын дугаар..." 
                           class="w-full bg-gray-50 border border-gray-200 rounded-full py-2.5 pl-10 pr-4 text-sm focus:outline-none focus:border-emerald-500 focus:bg-white transition shadow-inner">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-gray-400"></i>
                </div>
            </div>

            <!-- Header Actions -->
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-2 bg-emerald-50 text-emerald-800 px-3 py-1.5 rounded-full text-xs font-semibold border border-emerald-200">
                    <i class="fa-solid fa-shield-halved text-emerald-600"></i>
                    <span>egazar.mn баталгаажсан: <strong x-text="lands.filter(l => l.is_egazar_verified).length"></strong></span>
                </div>

                <button @click="openCreateModal = true" 
                        class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 py-2 rounded-xl text-sm transition flex items-center gap-2 shadow-md hover:shadow-lg">
                    <i class="fa-solid fa-plus"></i>
                    <span class="hidden sm:inline">Зараа оруулах</span>
                </button>

                <!-- Нэвтрэх хэсэг -->
                <a href="#" class="text-sm font-semibold text-gray-700 hover:text-emerald-600 flex items-center gap-1.5">
                    <i class="fa-regular fa-circle-user text-base"></i>
                    <span>Нэвтрэх</span>
                </a>
            </div>
        </div>
    </div>
</header>