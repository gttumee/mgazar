<!DOCTYPE html>
<html lang="mn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mgazar.mn - Интерактив зарын сайт</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Leaflet CSS & JS for Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Alpine.js for lightweight UI interactivity -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* Custom styling for diagonal EGAZAR.MN ribbon */
        .ribbon-wrapper {
            position: absolute;
            top: 0;
            right: 0;
            width: 110px;
            height: 110px;
            overflow: hidden;
            z-index: 10;
            pointer-events: none;
        }
        .ribbon-emerald {
            font-size: 10px;
            font-weight: 800;
            color: #ffffff;
            text-transform: uppercase;
            text-align: center;
            line-height: 22px;
            transform: rotate(45deg);
            width: 140px;
            display: block;
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            box-shadow: 0 3px 10px rgba(0,0,0,0.3);
            position: absolute;
            top: 22px;
            right: -32px;
            letter-spacing: 0.5px;
        }
        /* Custom scrollbars */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .leaflet-container {
            font-family: inherit;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased min-h-screen flex flex-col" x-data="landApp()">

    <!-- ================= HEADER SECTION ================= -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4">
            
            <!-- Logo -->
            <div class="flex items-center gap-3 cursor-pointer shrink-0" @click="resetFilters()">
                <div class="bg-brand-600 text-white p-2.5 rounded-2xl shadow-md flex items-center justify-center">
                    <i class="fa-solid fa-map-location-dot text-2xl"></i>
                </div>
                <div>
                    <div class="flex items-center gap-1">
                        <span class="text-2xl font-black tracking-tight text-gray-900">MGazar</span>
                        <span class="text-2xl font-black tracking-tight text-brand-600">.mn</span>
                    </div>
                    <p class="text-[10px] font-semibold tracking-wider text-gray-400 uppercase">Интерактив зарын сайт</p>
                </div>
            </div>

            <!-- Top Search Bar -->
            <div class="flex-1 max-w-xl mx-4 hidden md:block">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input 
                        type="text" 
                        x-model="searchQuery"
                        placeholder="Дүүрэг, байршил эсвэл кадастрын дугаар..." 
                        class="w-full pl-11 pr-4 py-2.5 bg-gray-100 hover:bg-gray-200/70 focus:bg-white text-sm border border-transparent focus:border-brand-500 rounded-full transition-all duration-200 outline-none"
                    >
                    <button x-show="searchQuery" @click="searchQuery = ''" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-3 shrink-0">
                <div class="hidden lg:flex items-center gap-2 bg-emerald-50 text-emerald-700 px-3.5 py-1.5 rounded-full border border-emerald-200 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>egazar.gov.mn баталгаажсан: <strong x-text="verifiedCount">4</strong></span>
                </div>

                <button 
                    @click="openAddModal = true" 
                    class="bg-brand-600 hover:bg-brand-700 text-white text-xs sm:text-sm font-semibold px-4 sm:px-5 py-2.5 rounded-xl shadow-sm hover:shadow transition-all flex items-center gap-2"
                >
                    <i class="fa-solid fa-plus text-sm"></i>
                    <span>Зараа оруулах</span>
                </button>

                <button class="border border-gray-300 hover:bg-gray-100 text-gray-700 text-xs sm:text-sm font-medium px-3.5 py-2.5 rounded-xl transition-colors flex items-center gap-2">
                    <i class="fa-regular fa-user text-sm"></i>
                    <span class="hidden sm:inline">Нэвтрэх</span>
                </button>
            </div>

        </div>
    </header>

    <!-- MAIN CONTAINER -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex-1 w-full flex flex-col gap-6">

        <!-- ================= FILTER CARD SECTION ================= -->
        <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-gray-200/80 flex flex-col gap-4">
            
            <!-- Row 1: Dropdown Filters -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Байршил / Дүүрэг</label>
                    <select x-model="filters.district" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs font-medium text-gray-700 focus:ring-2 focus:ring-brand-500 outline-none">
                        <option value="">Бүх байршил</option>
                        <option value="Хан-Уул">Хан-Уул</option>
                        <option value="Баянзүрх">Баянзүрх</option>
                        <option value="Сонгинохайрхан">Сонгинохайрхан</option>
                        <option value="Сүхбаатар">Сүхбаатар</option>
                        <option value="Чингэлтэй">Чингэлтэй</option>
                        <option value="Налайх">Налайх</option>
                        <option value="Төв аймаг">Төв аймаг</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Зориулалт</label>
                    <select x-model="filters.purpose" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs font-medium text-gray-700 focus:ring-2 focus:ring-brand-500 outline-none">
                        <option value="">Бүх зориулалт</option>
                        <option value="Амины сууц">Амины сууц</option>
                        <option value="Хүлэмж">Хүлэмж / Газар тариалан</option>
                        <option value="Амралт">Амралт, аялал жуулчлал</option>
                        <option value="Үйлдвэрлэл">Үйлдвэр, агуулах</option>
                        <option value="Зуслан">Зуслан</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Эрхийн төрөл</label>
                    <select x-model="filters.rights" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs font-medium text-gray-700 focus:ring-2 focus:ring-brand-500 outline-none">
                        <option value="">Бүх эрх</option>
                        <option value="Өмчлөх">Өмчлөх</option>
                        <option value="Эзэмших">Эзэмших</option>
                        <option value="Ашиглах">Ашиглах</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Дээд үнэ (₮)</label>
                    <select x-model="filters.maxPrice" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs font-medium text-gray-700 focus:ring-2 focus:ring-brand-500 outline-none">
                        <option value="">Хязгааргүй</option>
                        <option value="50000000">50 сая хүртэл</option>
                        <option value="100000000">100 сая хүртэл</option>
                        <option value="200000000">200 сая хүртэл</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Доод хэмжээ</label>
                    <select x-model="filters.minArea" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs font-medium text-gray-700 focus:ring-2 focus:ring-brand-500 outline-none">
                        <option value="">Хамаагүй</option>
                        <option value="0.05">0.05 га-аас дээш</option>
                        <option value="0.1">0.1 га-аас дээш</option>
                        <option value="0.5">0.5 га-аас дээш</option>
                        <option value="1.0">1.0 га-аас дээш</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button 
                        @click="resetFilters()" 
                        class="w-full bg-gray-100 hover:bg-gray-200 text-gray-600 font-medium py-2 rounded-xl text-xs transition-colors flex items-center justify-center gap-1.5"
                    >
                        <i class="fa-solid fa-rotate text-xs"></i>
                        <span>Цэвэрлэх</span>
                    </button>
                </div>

            </div>

            <hr class="border-gray-100">

            <!-- Row 2: Tag toggles & Sorting -->
            <div class="flex flex-wrap items-center justify-between gap-4">
                
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    
                    <!-- Verified Filter Toggle -->
                    <button 
                        @click="filters.verifiedOnly = !filters.verifiedOnly" 
                        :class="filters.verifiedOnly ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'"
                        class="px-3.5 py-1.5 rounded-full border font-semibold transition-all flex items-center gap-1.5 shadow-sm"
                    >
                        <i class="fa-solid fa-circle-check text-emerald-300"></i>
                        <span>egazar.gov.mn баталгаажсан</span>
                    </button>

                    <span class="text-gray-300 mx-1 hidden sm:inline">|</span>
                    <span class="text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Инженерчлэл:</span>

                    <!-- Quick Traits Toggles -->
                    <button 
                        @click="filters.electricity = !filters.electricity"
                        :class="filters.electricity ? 'bg-amber-100 text-amber-800 border-amber-300' : 'bg-gray-100 text-gray-600 border-transparent hover:bg-gray-200'"
                        class="px-3 py-1 rounded-full border text-xs font-medium transition-colors flex items-center gap-1"
                    >
                        <i class="fa-solid fa-bolt text-amber-500"></i>
                        <span>Тог/Цахилгаан</span>
                    </button>

                    <button 
                        @click="filters.water = !filters.water"
                        :class="filters.water ? 'bg-blue-100 text-blue-800 border-blue-300' : 'bg-gray-100 text-gray-600 border-transparent hover:bg-gray-200'"
                        class="px-3 py-1 rounded-full border text-xs font-medium transition-colors flex items-center gap-1"
                    >
                        <i class="fa-solid fa-droplet text-blue-500"></i>
                        <span>Цэвэр ус</span>
                    </button>

                    <button 
                        @click="filters.sewage = !filters.sewage"
                        :class="filters.sewage ? 'bg-indigo-100 text-indigo-800 border-indigo-300' : 'bg-gray-100 text-gray-600 border-transparent hover:bg-gray-200'"
                        class="px-3 py-1 rounded-full border text-xs font-medium transition-colors flex items-center gap-1"
                    >
                        <i class="fa-solid fa-toilet text-indigo-500"></i>
                        <span>Бохир ус</span>
                    </button>

                    <button 
                        @click="filters.road = !filters.road"
                        :class="filters.road ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-gray-100 text-gray-600 border-transparent hover:bg-gray-200'"
                        class="px-3 py-1 rounded-full border text-xs font-medium transition-colors flex items-center gap-1"
                    >
                        <i class="fa-solid fa-road text-emerald-600"></i>
                        <span>Замтай</span>
                    </button>

                </div>

                <!-- Sort Dropdown -->
                <div class="flex items-center gap-2 ml-auto">
                    <span class="text-xs text-gray-500 font-medium whitespace-nowrap">Эрэмбэ:</span>
                    <select x-model="sortBy" class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-xs font-semibold text-gray-700 outline-none">
                        <option value="newest">Шинэ нь эхэндээ</option>
                        <option value="price_asc">Үнэ: Багаас их рүү</option>
                        <option value="price_desc">Үнэ: Ихээс бага руу</option>
                        <option value="area_desc">Хэмжээ: Ихээс бага руу</option>
                    </select>
                </div>

            </div>

        </div>

        <!-- ================= VIEW SWITCHER & RESULT BAR ================= -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            
            <div class="text-sm font-semibold text-gray-700">
                Сонгосон бүсэд <span class="text-brand-600 font-extrabold text-base" x-text="filteredListings.length"></span> газрын зар олдлоо
            </div>

            <!-- View Switcher Tabs -->
            <div class="bg-gray-200/80 p-1 rounded-xl flex items-center gap-1 self-start sm:self-auto">
                <button 
                    @click="setViewMode('split')"
                    :class="viewMode === 'split' ? 'bg-white text-brand-700 shadow-sm font-bold' : 'text-gray-600 hover:text-gray-900 font-medium'"
                    class="px-3.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5"
                >
                    <i class="fa-solid fa-table-columns"></i>
                    <span>Зураг + Жагсаалт</span>
                </button>

                <button 
                    @click="setViewMode('map')"
                    :class="viewMode === 'map' ? 'bg-white text-brand-700 shadow-sm font-bold' : 'text-gray-600 hover:text-gray-900 font-medium'"
                    class="px-3.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5"
                >
                    <i class="fa-solid fa-map"></i>
                    <span>Бүтэн Зураглал</span>
                </button>

                <button 
                    @click="setViewMode('grid')"
                    :class="viewMode === 'grid' ? 'bg-white text-brand-700 shadow-sm font-bold' : 'text-gray-600 hover:text-gray-900 font-medium'"
                    class="px-3.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5"
                >
                    <i class="fa-solid fa-border-all"></i>
                    <span>Карт Жагсаалт</span>
                </button>
            </div>

        </div>

        <!-- ================= CONTENT VIEWS ================= -->

        <!-- VIEW 1: GRID MODE (3 CARDS PER ROW - MATCHES SCREENSHOT EXACTLY) -->
        <div x-show="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="item in filteredListings" :key="item.id">
                
                <!-- SINGLE CARD -->
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 flex flex-col group">
                    
                    <!-- Image Container -->
                    <div class="relative aspect-[16/10] bg-gray-100 overflow-hidden cursor-pointer" @click="openDetail(item)">
                        <img :src="item.images[0]" :alt="item.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        
                        <!-- Property Right Badge (Top Left) -->
                        <div class="absolute top-3 left-3 z-10">
                            <span 
                                :class="item.rights === 'Өмчлөх' ? 'bg-emerald-600 text-white' : 'bg-blue-600 text-white'"
                                class="px-2.5 py-1 rounded-lg text-[11px] font-bold shadow-md tracking-wide"
                                x-text="item.rights"
                            ></span>
                        </div>

                        <!-- Diagonal Corner Ribbon: EGAZAR.MN (Top Right) -->
                        <div class="ribbon-wrapper" x-show="item.verified">
                            <span class="ribbon-emerald">EGAZAR.MN</span>
                        </div>

                        <!-- Size Badge (Bottom Left Overlay) -->
                        <div class="absolute bottom-3 left-3 z-10">
                            <span class="bg-black/70 backdrop-blur-md text-white text-xs font-semibold px-2.5 py-1 rounded-lg flex items-center gap-1.5">
                                <i class="fa-solid fa-ruler-combined text-[10px] text-gray-300"></i>
                                <span x-text="item.sizeText"></span>
                            </span>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-4 flex-1 flex flex-col justify-between gap-3">
                        
                        <div>
                            <!-- Line 1: Price and Verified Status -->
                            <div class="flex items-center justify-between gap-2 mb-1.5">
                                <div class="text-xl font-black text-brand-600 tracking-tight" x-text="formatPrice(item.price)"></div>
                                <template x-if="item.verified">
                                    <span class="bg-emerald-50 text-emerald-700 text-[11px] font-semibold px-2 py-0.5 rounded-md border border-emerald-200 flex items-center gap-1">
                                        <i class="fa-solid fa-check-circle text-emerald-500 text-xs"></i> Баталгаажсан
                                    </span>
                                </template>
                            </div>

                            <!-- Line 2: Title -->
                            <h3 
                                @click="openDetail(item)"
                                class="font-bold text-gray-900 text-sm line-clamp-2 cursor-pointer hover:text-brand-600 transition-colors leading-snug" 
                                x-text="item.title"
                            ></h3>

                            <!-- Line 3: Location -->
                            <div class="flex items-center gap-1.5 text-xs text-gray-500 mt-2">
                                <i class="fa-solid fa-location-dot text-gray-400"></i>
                                <span x-text="item.locationText"></span>
                            </div>

                            <!-- Line 4: Infrastructure Traits (Text Badges) -->
                            <div class="flex flex-wrap gap-1.5 mt-3">
                                <span :class="item.electricity ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-50 text-gray-400 border-gray-100'" class="px-2 py-0.5 rounded text-[11px] font-medium border">
                                    Тог
                                </span>
                                <span :class="item.water ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-50 text-gray-400 border-gray-100'" class="px-2 py-0.5 rounded text-[11px] font-medium border">
                                    Ус
                                </span>
                                <span :class="item.road ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-50 text-gray-400 border-gray-100'" class="px-2 py-0.5 rounded text-[11px] font-medium border">
                                    Зам
                                </span>
                                <span :class="item.fence ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-50 text-gray-400 border-gray-100'" class="px-2 py-0.5 rounded text-[11px] font-medium border">
                                    Хашаа
                                </span>
                                <span :class="!item.swamp ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-600 border-rose-200'" class="px-2 py-0.5 rounded text-[11px] font-medium border" x-text="item.swamp ? 'Намагтай' : 'Намаггүй'">
                                </span>
                            </div>
                        </div>

                        <!-- Line 5: Card Footer -->
                        <div class="pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                            <span class="text-gray-400" x-text="item.date"></span>
                            <button @click="openDetail(item)" class="text-brand-600 font-bold hover:text-brand-700 flex items-center gap-1">
                                <span>Дэлгэрэнгүй</span>
                                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                            </button>
                        </div>

                    </div>

                </div>

            </template>
        </div>


        <!-- VIEW 2: SPLIT MODE (Horizontal Cards + Map side-by-side) -->
        <div x-show="viewMode === 'split'" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Cards Column -->
            <div class="lg:col-span-6 flex flex-col gap-4">
                <template x-for="item in filteredListings" :key="item.id">
                    
                    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col sm:flex-row group">
                        
                        <!-- Image Container -->
                        <div class="relative sm:w-5/12 aspect-[4/3] sm:aspect-auto bg-gray-100 overflow-hidden cursor-pointer" @click="openDetail(item)">
                            <img :src="item.images[0]" :alt="item.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            
                            <!-- Rights Badge -->
                            <div class="absolute top-2.5 left-2.5 z-10">
                                <span 
                                    :class="item.rights === 'Өмчлөх' ? 'bg-emerald-600 text-white' : 'bg-blue-600 text-white'"
                                    class="px-2 py-0.5 rounded-md text-[10px] font-bold"
                                    x-text="item.rights"
                                ></span>
                            </div>

                            <!-- EGAZAR Ribbon -->
                            <div class="ribbon-wrapper" x-show="item.verified">
                                <span class="ribbon-emerald">EGAZAR.MN</span>
                            </div>

                            <!-- Size Overlay -->
                            <div class="absolute bottom-2.5 left-2.5 z-10">
                                <span class="bg-black/75 text-white text-[11px] font-semibold px-2 py-0.5 rounded flex items-center gap-1">
                                    <span x-text="item.sizeText"></span>
                                </span>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-4 sm:w-7/12 flex flex-col justify-between gap-2">
                            <div>
                                <div class="flex items-center justify-between gap-1 mb-1">
                                    <div class="text-lg font-black text-brand-600" x-text="formatPrice(item.price)"></div>
                                    <template x-if="item.verified">
                                        <span class="bg-emerald-50 text-emerald-700 text-[10px] font-bold px-1.5 py-0.5 rounded border border-emerald-200">
                                            Баталгаажсан
                                        </span>
                                    </template>
                                </div>

                                <h3 @click="openDetail(item)" class="font-bold text-gray-900 text-xs sm:text-sm line-clamp-2 cursor-pointer hover:text-brand-600 transition-colors" x-text="item.title"></h3>
                                
                                <div class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                    <i class="fa-solid fa-location-dot"></i>
                                    <span x-text="item.locationText"></span>
                                </div>

                                <div class="flex flex-wrap gap-1 mt-2">
                                    <span :class="item.electricity ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-400'" class="px-1.5 py-0.5 rounded text-[10px] font-medium">Тог</span>
                                    <span :class="item.water ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-400'" class="px-1.5 py-0.5 rounded text-[10px] font-medium">Ус</span>
                                    <span :class="item.road ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-400'" class="px-1.5 py-0.5 rounded text-[10px] font-medium">Зам</span>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-gray-100 flex items-center justify-between text-xs">
                                <span class="text-gray-400 text-[11px]" x-text="item.date"></span>
                                <button @click="openDetail(item)" class="text-brand-600 font-bold hover:text-brand-700 text-xs">Дэлгэрэнгүй &rarr;</button>
                            </div>
                        </div>

                    </div>

                </template>
            </div>

            <!-- Sticky Map Container -->
            <div class="lg:col-span-6 sticky top-24 h-[600px] bg-gray-200 rounded-2xl overflow-hidden border border-gray-200 shadow-sm">
                <div id="splitMap" class="w-full h-full"></div>
            </div>

        </div>


        <!-- VIEW 3: FULL MAP MODE -->
        <div x-show="viewMode === 'map'" class="w-full h-[700px] bg-gray-200 rounded-2xl overflow-hidden border border-gray-200 shadow-sm relative">
            <div id="fullMap" class="w-full h-full"></div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12 py-8 text-center text-xs text-gray-500">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="font-bold text-gray-900">Газар.mn</span>
                <span>&copy; 2026 Интерактив зарын сайт</span>
            </div>
            <div class="flex gap-4 font-medium text-gray-600">
                <a href="#" class="hover:underline">Үйлчилгээний нөхцөл</a>
                <a href="#" class="hover:underline">Нууцлалын бодлого</a>
                <a href="#" class="hover:underline">Холбоо барих</a>
            </div>
        </div>
    </footer>


    <!-- ================= MODAL 1: ЗАРАА ОРУУЛАХ (POST NEW AD) ================= -->
    <div 
        x-show="openAddModal" 
        x-transition.opacity 
        class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto"
        style="display: none;"
    >
        <div 
            @click.away="openAddModal = false" 
            class="bg-white w-full max-w-3xl rounded-3xl shadow-2xl overflow-hidden my-8 flex flex-col max-h-[90vh]"
        >
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-gray-900 text-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-up text-brand-500 text-xl"></i>
                    <h2 class="text-lg font-bold">Шинэ газрын зар оруулах</h2>
                </div>
                <button @click="openAddModal = false" class="text-gray-400 hover:text-white transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <!-- Modal Form Body -->
            <form @submit.prevent="submitNewListing()" class="p-6 overflow-y-auto flex-1 space-y-6 text-sm">
                
                <!-- Basic Info -->
                <div class="space-y-4">
                    <h3 class="font-bold text-gray-900 text-base border-b pb-2 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xs font-bold">1</span>
                        Үндсэн мэдээлэл
                    </h3>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Зарын гарчиг <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="newForm.title" required placeholder="Нүхтэд 0.07 га амины сууцны өмчлөлийн газар" class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-brand-500 outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Зарах үнэ (₮) <span class="text-rose-500">*</span></label>
                            <input type="number" x-model="newForm.price" required placeholder="150000000" class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-brand-500 outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Газрын хэмжээ (га) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" x-model="newForm.area" required placeholder="0.07" class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-brand-500 outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Эрхийн төрөл <span class="text-rose-500">*</span></label>
                            <select x-model="newForm.rights" required class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-brand-500 outline-none">
                                <option value="Өмчлөх">Өмчлөх</option>
                                <option value="Эзэмших">Эзэмших</option>
                                <option value="Ашиглах">Ашиглах</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Байршил / Дүүрэг <span class="text-rose-500">*</span></label>
                            <select x-model="newForm.district" required class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-brand-500 outline-none">
                                <option value="Хан-Уул">Хан-Уул</option>
                                <option value="Баянзүрх">Баянзүрх</option>
                                <option value="Сонгинохайрхан">Сонгинохайрхан</option>
                                <option value="Сүхбаатар">Сүхбаатар</option>
                                <option value="Чингэлтэй">Чингэлтэй</option>
                                <option value="Налайх">Налайх</option>
                                <option value="Төв аймаг">Төв аймаг</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Кадастрын дугаар</label>
                            <input type="text" x-model="newForm.cadastral" placeholder="1862304912" class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-brand-500 outline-none">
                        </div>
                    </div>
                </div>

                <!-- Mandatory 6 Traits Section -->
                <div class="space-y-4 pt-2">
                    <h3 class="font-bold text-gray-900 text-base border-b pb-2 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xs font-bold">2</span>
                        Дэд бүтэц ба Газрын төлөв (Заавал сонгох 6 үзүүлэлт)
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 bg-gray-50 p-4 rounded-2xl border border-gray-200">
                        
                        <!-- 1. Electricity -->
                        <div>
                            <label class="block font-bold text-gray-800 text-xs mb-2">1. Улсын тог <span class="text-rose-500">*</span></label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="radio" x-model="newForm.electricity" :value="true" required class="text-brand-600 focus:ring-brand-500">
                                    <span class="text-xs font-medium">Тийм (Тогтой)</span>
                                </label>
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="radio" x-model="newForm.electricity" :value="false" required class="text-brand-600 focus:ring-brand-500">
                                    <span class="text-xs font-medium">Үгүй</span>
                                </label>
                            </div>
                        </div>

                        <!-- 2. Water -->
                        <div>
                            <label class="block font-bold text-gray-800 text-xs mb-2">2. Ус / Худаг <span class="text-rose-500">*</span></label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="radio" x-model="newForm.water" :value="true" required class="text-brand-600 focus:ring-brand-500">
                                    <span class="text-xs font-medium">Тийм (Устай)</span>
                                </label>
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="radio" x-model="newForm.water" :value="false" required class="text-brand-600 focus:ring-brand-500">
                                    <span class="text-xs font-medium">Үгүй</span>
                                </label>
                            </div>
                        </div>

                        <!-- 3. Fence -->
                        <div>
                            <label class="block font-bold text-gray-800 text-xs mb-2">3. Хашаа <span class="text-rose-500">*</span></label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="radio" x-model="newForm.fence" :value="true" required class="text-brand-600 focus:ring-brand-500">
                                    <span class="text-xs font-medium">Хашаатай</span>
                                </label>
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="radio" x-model="newForm.fence" :value="false" required class="text-brand-600 focus:ring-brand-500">
                                    <span class="text-xs font-medium">Хашаагүй</span>
                                </label>
                            </div>
                        </div>

                        <!-- 4. Swamp -->
                        <div>
                            <label class="block font-bold text-gray-800 text-xs mb-2">4. Намаг, чийг <span class="text-rose-500">*</span></label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="radio" x-model="newForm.swamp" :value="false" required class="text-brand-600 focus:ring-brand-500">
                                    <span class="text-xs font-medium">Намаггүй</span>
                                </label>
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="radio" x-model="newForm.swamp" :value="true" required class="text-brand-600 focus:ring-brand-500">
                                    <span class="text-xs font-medium">Намагтай</span>
                                </label>
                            </div>
                        </div>

                        <!-- 5. Road -->
                        <div>
                            <label class="block font-bold text-gray-800 text-xs mb-2">5. Зам дагуу <span class="text-rose-500">*</span></label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="radio" x-model="newForm.road" :value="true" required class="text-brand-600 focus:ring-brand-500">
                                    <span class="text-xs font-medium">Замтай</span>
                                </label>
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="radio" x-model="newForm.road" :value="false" required class="text-brand-600 focus:ring-brand-500">
                                    <span class="text-xs font-medium">Үгүй</span>
                                </label>
                            </div>
                        </div>

                        <!-- 6. Slope -->
                        <div>
                            <label class="block font-bold text-gray-800 text-xs mb-2">6. Газрын гадаргуу <span class="text-rose-500">*</span></label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="radio" x-model="newForm.slope" value="Тэгш" required class="text-brand-600 focus:ring-brand-500">
                                    <span class="text-xs font-medium">Тэгш</span>
                                </label>
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="radio" x-model="newForm.slope" value="Налуу" required class="text-brand-600 focus:ring-brand-500">
                                    <span class="text-xs font-medium">Налуу</span>
                                </label>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Re-orderable Photo Uploader Section -->
                <div class="space-y-4 pt-2">
                    <h3 class="font-bold text-gray-900 text-base border-b pb-2 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xs font-bold">3</span>
                            <span>Зураг оруулах (Дараалал өөрчлөх боломжтой)</span>
                        </div>
                        <span class="text-xs text-gray-500 font-normal">Эхний зураг зарын НҮҮР ЗУРАГ болно</span>
                    </h3>

                    <!-- Upload Trigger Box -->
                    <div class="border-2 border-dashed border-gray-300 hover:border-brand-500 rounded-2xl p-6 text-center cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors" @click="$refs.fileInput.click()">
                        <i class="fa-solid fa-images text-3xl text-gray-400 mb-2"></i>
                        <p class="font-semibold text-gray-700 text-xs sm:text-sm">Зураг сонгох эсвэл тийш чирэж оруулна уу</p>
                        <p class="text-[11px] text-gray-400 mt-1">PNG, JPG 10MB хүртэл</p>
                        <input type="file" x-ref="fileInput" @change="handleFileUpload($event)" multiple accept="image/*" class="hidden">
                    </div>

                    <!-- Uploaded Images List with Re-ordering & Cover Marker -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4" x-show="newForm.images.length > 0">
                        <template x-for="(img, index) in newForm.images" :key="index">
                            <div class="relative group rounded-xl overflow-hidden border border-gray-200 aspect-[4/3] bg-gray-100 shadow-sm">
                                <img :src="img" class="w-full h-full object-cover">
                                
                                <!-- COVER PHOTO GOLD BADGE (IF INDEX === 0) -->
                                <template x-if="index === 0">
                                    <div class="absolute top-2 left-2 bg-amber-500 text-white font-extrabold text-[10px] px-2 py-1 rounded-md shadow-lg flex items-center gap-1 z-20 animate-pulse">
                                        <i class="fa-solid fa-star"></i> НҮҮР ЗУРАГ
                                    </div>
                                </template>

                                <!-- OVERLAY CONTROLS -->
                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-between p-2 z-10">
                                    
                                    <!-- Remove Button -->
                                    <div class="flex justify-end">
                                        <button type="button" @click="removeImage(index)" class="bg-rose-600 text-white p-1 rounded-full text-xs w-6 h-6 flex items-center justify-center shadow">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>

                                    <!-- Rearrange / Make Cover Controls -->
                                    <div class="flex items-center justify-between gap-1">
                                        <!-- Make Cover Button -->
                                        <template x-if="index !== 0">
                                            <button type="button" @click="makeCover(index)" class="bg-amber-500 hover:bg-amber-600 text-white text-[10px] font-bold px-2 py-1 rounded shadow">
                                                ★ Нүүр болгох
                                            </button>
                                        </template>

                                        <!-- Shift Left / Right Arrows -->
                                        <div class="flex gap-1 ml-auto">
                                            <button type="button" x-show="index > 0" @click="moveImage(index, index - 1)" class="bg-white/80 hover:bg-white text-gray-800 p-1 rounded text-xs w-6 h-6 flex items-center justify-center">
                                                <i class="fa-solid fa-arrow-left"></i>
                                            </button>
                                            <button type="button" x-show="index < newForm.images.length - 1" @click="moveImage(index, index + 1)" class="bg-white/80 hover:bg-white text-gray-800 p-1 rounded text-xs w-6 h-6 flex items-center justify-center">
                                                <i class="fa-solid fa-arrow-right"></i>
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </template>
                    </div>

                </div>

                <!-- Footer Submit -->
                <div class="pt-4 border-t flex items-center justify-end gap-3">
                    <button type="button" @click="openAddModal = false" class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-semibold text-xs hover:bg-gray-100">
                        Цуцлах
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-md transition-all">
                        Зар нийтлэх
                    </button>
                </div>

            </form>
        </div>
    </div>


    <!-- ================= MODAL 2: DETAIL VIEW ================= -->
    <div 
        x-show="openDetailModal && selectedItem" 
        x-transition.opacity 
        class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto"
        style="display: none;"
    >
        <template x-if="selectedItem">
            <div @click.away="openDetailModal = false" class="bg-white w-full max-w-4xl rounded-3xl shadow-2xl overflow-hidden my-8 flex flex-col max-h-[90vh]">
                
                <!-- Modal Top Bar -->
                <div class="px-6 py-4 bg-gray-900 text-white flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2">
                        <span :class="selectedItem.rights === 'Өмчлөх' ? 'bg-emerald-600' : 'bg-blue-600'" class="px-2.5 py-0.5 rounded text-xs font-bold" x-text="selectedItem.rights"></span>
                        <span class="text-xs text-gray-400">№ <span x-text="selectedItem.cadastral || '1862304912'"></span></span>
                    </div>
                    <button @click="openDetailModal = false" class="text-gray-400 hover:text-white">
                        <i class="fa-solid fa-xmark text-2xl"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 overflow-y-auto space-y-6 flex-1">
                    
                    <!-- Main Gallery Slider Preview -->
                    <div class="space-y-2">
                        <div class="relative aspect-[16/9] bg-gray-900 rounded-2xl overflow-hidden shadow-inner">
                            <img :src="selectedItem.images[activeDetailImg]" class="w-full h-full object-contain">
                            
                            <!-- EGAZAR Ribbon on detail preview -->
                            <div class="ribbon-wrapper" x-show="selectedItem.verified">
                                <span class="ribbon-emerald">EGAZAR.MN</span>
                            </div>
                        </div>

                        <!-- Thumbnails -->
                        <div class="flex gap-2 overflow-x-auto pb-2">
                            <template x-for="(img, idx) in selectedItem.images" :key="idx">
                                <button @click="activeDetailImg = idx" :class="activeDetailImg === idx ? 'ring-2 ring-brand-600 opacity-100' : 'opacity-60 hover:opacity-100'" class="w-20 h-14 rounded-lg overflow-hidden shrink-0 border border-gray-200">
                                    <img :src="img" class="w-full h-full object-cover">
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Price & Title Section -->
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 border-b pb-4">
                        <div>
                            <div class="text-2xl sm:text-3xl font-black text-brand-600" x-text="formatPrice(selectedItem.price)"></div>
                            <h1 class="text-lg font-extrabold text-gray-900 mt-1" x-text="selectedItem.title"></h1>
                            <p class="text-xs text-gray-500 mt-1"><i class="fa-solid fa-location-dot text-brand-600"></i> <span x-text="selectedItem.locationText"></span></p>
                        </div>

                        <div class="shrink-0 flex flex-col items-end gap-2">
                            <template x-if="selectedItem.verified">
                                <span class="bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1.5 rounded-full border border-emerald-300 flex items-center gap-1.5">
                                    <i class="fa-solid fa-shield-halved text-emerald-600"></i> egazar.mn Баталгаажсан
                                </span>
                            </template>
                        </div>
                    </div>

                    <!-- Specifications Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 bg-gray-50 p-4 rounded-2xl border border-gray-200 text-xs">
                        <div>
                            <span class="text-gray-400 font-medium block">Газрын хэмжээ:</span>
                            <span class="font-bold text-gray-800 text-sm" x-text="selectedItem.sizeText"></span>
                        </div>
                        <div>
                            <span class="text-gray-400 font-medium block">Зориулалт:</span>
                            <span class="font-bold text-gray-800 text-sm" x-text="selectedItem.purpose"></span>
                        </div>
                        <div>
                            <span class="text-gray-400 font-medium block">Улсын тог:</span>
                            <span class="font-bold text-sm" :class="selectedItem.electricity ? 'text-emerald-600' : 'text-gray-400'" x-text="selectedItem.electricity ? '⚡ Тогтой' : 'Үгүй'"></span>
                        </div>
                        <div>
                            <span class="text-gray-400 font-medium block">Цэвэр ус:</span>
                            <span class="font-bold text-sm" :class="selectedItem.water ? 'text-blue-600' : 'text-gray-400'" x-text="selectedItem.water ? '💧 Устай' : 'Үгүй'"></span>
                        </div>
                        <div>
                            <span class="text-gray-400 font-medium block">Хашаа:</span>
                            <span class="font-bold text-sm" :class="selectedItem.fence ? 'text-emerald-600' : 'text-gray-400'" x-text="selectedItem.fence ? '🧱 Хашаатай' : 'Хашаагүй'"></span>
                        </div>
                        <div>
                            <span class="text-gray-400 font-medium block">Зам:</span>
                            <span class="font-bold text-sm" :class="selectedItem.road ? 'text-emerald-600' : 'text-gray-400'" x-text="selectedItem.road ? '🛣️ Замтай' : 'Үгүй'"></span>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <h4 class="font-bold text-gray-900 mb-2">Дэлгэрэнгүй тайлбар:</h4>
                        <p class="text-xs text-gray-600 leading-relaxed bg-white border p-4 rounded-2xl" x-text="selectedItem.description || 'Байршил сайтай, тэгш газар. Бүх бичиг баримт бүрэн. Цахилгаан холбогдох боломжтой.'"></p>
                    </div>

                    <!-- Contact Bar -->
                    <div class="bg-brand-50 border border-brand-200 p-4 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-brand-600 text-white flex items-center justify-center font-bold text-lg">
                                <i class="fa-solid fa-phone"></i>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 font-medium">Зарын эзэнтэй холбогдох:</div>
                                <div class="text-lg font-black text-gray-900" x-text="showPhone ? '9911-2233' : '9911-****'"></div>
                            </div>
                        </div>

                        <button @click="showPhone = true" class="w-full sm:w-auto bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs px-6 py-3 rounded-xl shadow transition-all">
                            <span x-text="showPhone ? 'Утасны дугаар ил гарлаа' : 'Утасны дугаар харах'"></span>
                        </button>
                    </div>

                </div>

            </div>
        </template>
    </div>


    <!-- ================= JAVASCRIPT LOGIC ================= -->
    <script>
        function landApp() {
            return {
                viewMode: 'grid', // 'grid', 'split', 'map'
                searchQuery: '',
                openAddModal: false,
                openDetailModal: false,
                selectedItem: null,
                activeDetailImg: 0,
                showPhone: false,
                sortBy: 'newest',

                // Filters State
                filters: {
                    district: '',
                    purpose: '',
                    rights: '',
                    maxPrice: '',
                    minArea: '',
                    verifiedOnly: false,
                    electricity: false,
                    water: false,
                    sewage: false,
                    road: false
                },

                // New Ad Form State
                newForm: {
                    title: '',
                    price: '',
                    area: '',
                    rights: 'Өмчлөх',
                    district: 'Хан-Уул',
                    cadastral: '',
                    electricity: true,
                    water: true,
                    fence: true,
                    swamp: false,
                    road: true,
                    slope: 'Тэгш',
                    images: []
                },

                // Mock Initial Data matching screenshot precisely
                listings: [
                    {
                        id: 1,
                        title: "Хан-Уул дүүрэг Нүхтэд 0.07 га амины сууцны өмчлөлийн газар",
                        price: 150000000,
                        sizeText: "0.07 га (700 м²)",
                        areaNum: 0.07,
                        rights: "Өмчлөх",
                        district: "Хан-Уул",
                        locationText: "Улаанбаатар, Хан-Уул",
                        verified: true,
                        electricity: true,
                        water: true,
                        fence: true,
                        swamp: false,
                        road: true,
                        slope: "Тэгш",
                        date: "Өнөөдөр",
                        lat: 47.8421,
                        lng: 106.8712,
                        images: [
                            "https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=800&q=80",
                            "https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80"
                        ]
                    },
                    {
                        id: 2,
                        title: "Баянзүрх дүүрэг Гачууртад 0.5 га хүлэмжийн зориулалттай газар",
                        price: 85000000,
                        sizeText: "0.5 га (5,000 м²)",
                        areaNum: 0.5,
                        rights: "Эзэмших",
                        district: "Баянзүрх",
                        locationText: "Улаанбаатар, Баянзүрх",
                        verified: true,
                        electricity: true,
                        water: true,
                        fence: false,
                        swamp: false,
                        road: true,
                        slope: "Тэгш",
                        date: "Өчигдөр",
                        lat: 47.9312,
                        lng: 107.0512,
                        images: [
                            "https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=800&q=80",
                            "https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=800&q=80"
                        ]
                    },
                    {
                        id: 3,
                        title: "Төв аймаг Эрдэнэ суманд 1.0 га амралтын бүсийн газар",
                        price: 45000000,
                        sizeText: "1.0 га (10,000 м²)",
                        areaNum: 1.0,
                        rights: "Өмчлөх",
                        district: "Төв аймаг",
                        locationText: "Төв аймаг",
                        verified: true,
                        electricity: false,
                        water: true,
                        fence: false,
                        swamp: false,
                        road: true,
                        slope: "Налуу",
                        date: "3 өдрийн өмнө",
                        lat: 47.7812,
                        lng: 107.4512,
                        images: [
                            "https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&w=800&q=80",
                            "https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=800&q=80"
                        ]
                    },
                    {
                        id: 4,
                        title: "Сонгинохайрхан Эмээлтэд 0.3 га үйлдвэрлэлийн газар",
                        price: 120000000,
                        sizeText: "0.3 га (3,000 м²)",
                        areaNum: 0.3,
                        rights: "Эзэмших",
                        district: "Сонгинохайрхан",
                        locationText: "Улаанбаатар, Сонгинохайрхан",
                        verified: true,
                        electricity: true,
                        water: false,
                        fence: true,
                        swamp: false,
                        road: true,
                        slope: "Тэгш",
                        date: "4 өдрийн өмнө",
                        lat: 47.8812,
                        lng: 106.7112,
                        images: [
                            "https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80"
                        ]
                    },
                    {
                        id: 5,
                        title: "Сүхбаатар дүүрэг Шаргаморьтод 0.08 га зуслангийн газар",
                        price: 38000000,
                        sizeText: "0.08 га (800 м²)",
                        areaNum: 0.08,
                        rights: "Өмчлөх",
                        district: "Сүхбаатар",
                        locationText: "Улаанбаатар, Сүхбаатар",
                        verified: false,
                        electricity: true,
                        water: false,
                        fence: true,
                        swamp: false,
                        road: false,
                        slope: "Налуу",
                        date: "5 өдрийн өмнө",
                        lat: 47.9812,
                        lng: 106.9212,
                        images: [
                            "https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=800&q=80"
                        ]
                    }
                ],

                // Leaflet Map instances
                splitMap: null,
                fullMap: null,

                init() {
                    // Seed initial demo photo into upload form
                    this.newForm.images = [
                        "https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=800&q=80",
                        "https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=800&q=80"
                    ];
                },

                get verifiedCount() {
                    return this.listings.filter(i => i.verified).length;
                },

                get filteredListings() {
                    return this.listings.filter(item => {
                        if (this.searchQuery) {
                            const q = this.searchQuery.toLowerCase();
                            const matchTitle = item.title.toLowerCase().includes(q);
                            const matchDistrict = item.district.toLowerCase().includes(q);
                            if (!matchTitle && !matchDistrict) return false;
                        }
                        if (this.filters.district && item.district !== this.filters.district) return false;
                        if (this.filters.rights && item.rights !== this.filters.rights) return false;
                        if (this.filters.verifiedOnly && !item.verified) return false;
                        if (this.filters.electricity && !item.electricity) return false;
                        if (this.filters.water && !item.water) return false;
                        if (this.filters.road && !item.road) return false;
                        if (this.filters.maxPrice && item.price > parseInt(this.filters.maxPrice)) return false;
                        if (this.filters.minArea && item.areaNum < parseFloat(this.filters.minArea)) return false;
                        return true;
                    }).sort((a, b) => {
                        if (this.sortBy === 'price_asc') return a.price - b.price;
                        if (this.sortBy === 'price_desc') return b.price - a.price;
                        if (this.sortBy === 'area_desc') return b.areaNum - a.areaNum;
                        return b.id - a.id;
                    });
                },

                formatPrice(val) {
                    return new Intl.NumberFormat('mn-MN').format(val) + ' ₮';
                },

                resetFilters() {
                    this.searchQuery = '';
                    this.filters = {
                        district: '',
                        purpose: '',
                        rights: '',
                        maxPrice: '',
                        minArea: '',
                        verifiedOnly: false,
                        electricity: false,
                        water: false,
                        sewage: false,
                        road: false
                    };
                },

                setViewMode(mode) {
                    this.viewMode = mode;
                    this.$nextTick(() => {
                        if (mode === 'split') this.initSplitMap();
                        if (mode === 'map') this.initFullMap();
                    });
                },

                // 403-FREE LEAFLET MAP INITIALIZERS
                initSplitMap() {
                    if (this.splitMap) return;
                    const container = document.getElementById('splitMap');
                    if (!container) return;

                    this.splitMap = L.map('splitMap').setView([47.8864, 106.9057], 11);

                    // Reliable CartoDB Positron tiles (No 403 Access Blocked)
                    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                        attribution: '&copy; CartoDB & OpenStreetMap',
                        subdomains: 'abcd',
                        maxZoom: 19
                    }).addTo(this.splitMap);

                    this.addMarkersToMap(this.splitMap);
                },

                initFullMap() {
                    if (this.fullMap) return;
                    const container = document.getElementById('fullMap');
                    if (!container) return;

                    this.fullMap = L.map('fullMap').setView([47.8864, 106.9057], 11);

                    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                        attribution: '&copy; CartoDB & OpenStreetMap',
                        subdomains: 'abcd',
                        maxZoom: 19
                    }).addTo(this.fullMap);

                    this.addMarkersToMap(this.fullMap);
                },

                addMarkersToMap(mapInstance) {
                    this.listings.forEach(item => {
                        const priceM = (item.price / 1000000).toFixed(0) + ' сая ₮';
                        const customIcon = L.divIcon({
                            className: 'custom-map-badge',
                            html: `<div style="background:#16a34a; color:white; padding:4px 8px; font-size:11px; font-weight:bold; border-radius:12px; border:2px solid white; box-shadow:0 3px 8px rgba(0,0,0,0.3); whitespace:nowrap;">${priceM}</div>`,
                            iconSize: [80, 25]
                        });

                        const marker = L.marker([item.lat, item.lng], { icon: customIcon }).addTo(mapInstance);
                        marker.on('click', () => {
                            this.openDetail(item);
                        });
                    });
                },

                // PHOTO UPLOADER LOGIC (RE-ORDER & COVER MARKER)
                handleFileUpload(event) {
                    const files = event.target.files;
                    if (!files) return;
                    for (let i = 0; i < files.length; i++) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.newForm.images.push(e.target.result);
                        };
                        reader.readAsDataURL(files[i]);
                    }
                },

                removeImage(index) {
                    this.newForm.images.splice(index, 1);
                },

                moveImage(from, to) {
                    const item = this.newForm.images.splice(from, 1)[0];
                    this.newForm.images.splice(to, 0, item);
                },

                makeCover(index) {
                    const cover = this.newForm.images.splice(index, 1)[0];
                    this.newForm.images.unshift(cover);
                },

                submitNewListing() {
                    const newId = Date.now();
                    const newObj = {
                        id: newId,
                        title: this.newForm.title,
                        price: parseFloat(this.newForm.price) || 0,
                        sizeText: `${this.newForm.area} га (${(parseFloat(this.newForm.area)*10000).toLocaleString()} м²)`,
                        areaNum: parseFloat(this.newForm.area) || 0,
                        rights: this.newForm.rights,
                        district: this.newForm.district,
                        cadastral: this.newForm.cadastral,
                        locationText: `Улаанбаатар, ${this.newForm.district}`,
                        verified: true,
                        electricity: Boolean(this.newForm.electricity),
                        water: Boolean(this.newForm.water),
                        fence: Boolean(this.newForm.fence),
                        swamp: Boolean(this.newForm.swamp),
                        road: Boolean(this.newForm.road),
                        slope: this.newForm.slope,
                        date: "Дөнгөж сая",
                        lat: 47.8864 + (Math.random() - 0.5) * 0.1,
                        lng: 106.9057 + (Math.random() - 0.5) * 0.1,
                        images: this.newForm.images.length > 0 ? [...this.newForm.images] : ["https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=800&q=80"]
                    };

                    this.listings.unshift(newObj);
                    this.openAddModal = false;

                    // Reset form
                    this.newForm = {
                        title: '',
                        price: '',
                        area: '',
                        rights: 'Өмчлөх',
                        district: 'Хан-Уул',
                        cadastral: '',
                        electricity: true,
                        water: true,
                        fence: true,
                        swamp: false,
                        road: true,
                        slope: 'Тэгш',
                        images: []
                    };
                },

                openDetail(item) {
                    this.selectedItem = item;
                    this.activeDetailImg = 0;
                    this.showPhone = false;
                    this.openDetailModal = true;
                }
            };
        }
    </script>
</body>
</html>