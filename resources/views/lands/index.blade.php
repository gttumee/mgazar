@extends('layouts.app')

@section('title', 'Газрын зарууд - Газар.mn')

@section('content')
    <!-- Filter Bar Component -->
    @include('components.filter-bar')
    <!-- View Controls & Counter -->
    <div class="flex flex-col sm:flex-row justify-between items-center my-4 gap-3">
        <div class="text-sm text-gray-600 font-medium">
            Сонгосон бүсэд <span class="text-emerald-600 font-bold" x-text="filteredLands.length"></span> газрын зар олдлоо
        </div>

        <div class="flex items-center bg-gray-200 p-1 rounded-xl gap-1 text-xs font-bold">
    <button @click="viewMode = 'split'; $nextTick(() => { if(typeof splitMap !== 'undefined') { splitMap.invalidateSize(); } })" 
            :class="viewMode === 'split' ? 'bg-white text-emerald-700 shadow-sm' : 'text-gray-600 hover:text-gray-900'"
            class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
        <i class="fa-solid fa-map"></i> Газрын Зураг + Жагсаалт
    </button>

            <button @click="viewMode = 'map'" 
                    :class="viewMode === 'map' ? 'bg-white text-emerald-700 shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                    class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                <i class="fa-solid fa-earth-asia"></i>Газрын Зураг
            </button>
            <button @click="viewMode = 'grid'" 
                    :class="viewMode === 'grid' ? 'bg-white text-emerald-700 shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                    class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                <i class="fa-solid fa-border-all"></i> Жагсаалт
            </button>
        </div>
    </div>

    <!-- Listings Container (Grid View) -->
    <div x-show="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <template x-for="land in filteredLands" :key="land.id">
            @include('components.land-card')
        </template>
    </div>

    <!-- Split Mode Layout (Map + List) -->
    <div x-show="viewMode === 'split'" class="grid grid-cols-1 lg:grid-cols-12 gap-5 min-h-[650px]">
        <!-- Left Scrollable Cards List -->
        <div class="lg:col-span-6 space-y-4 max-h-[750px] overflow-y-auto pr-1 hide-scrollbar">
            <template x-for="land in filteredLands" :key="'split-'+land.id">
                <div :id="'card-'+land.id" 
                     @mouseenter="highlightLand(land.id)" 
                     @mouseleave="unhighlightLand(land.id)"
                     @click="zoomToLand(land)"
                     class="bg-white rounded-2xl border p-3 cursor-pointer transition-all duration-200 shadow-sm hover:shadow-md flex gap-3 relative border-slate-200 overflow-hidden"
                     :class="activeLandId === land.id ? 'ring-2 ring-brand-500 border-brand-500 bg-brand-50/20' : ''">
                    
                    <!-- BOLD DIAGONAL CORNER RIBBON FOR EGAZAR VERIFIED LANDS -->
                    <template x-if="land.isEgazarVerified">
                        <div class="ribbon-wrapper">
                            <span class="egazar-ribbon">
                                <i class="fa-solid fa-shield-check mr-0.5"></i> EGAZAR
                            </span>
                        </div>
                    </template>

                    <!-- Thumbnail & Badges -->
                    <div class="relative w-32 h-32 sm:w-36 sm:h-36 rounded-xl overflow-hidden bg-slate-100 flex-shrink-0">
                        <img :src="land.image" :alt="land.title" class="w-full h-full object-cover">
                        
                        <!-- Land Status Badge -->
                        <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md text-[9px] font-black text-white shadow-sm z-10"
                              :class="land.status === 'Өмчлөх' ? 'bg-emerald-600' : 'bg-blue-600'"
                              x-text="land.status">
                        </span>
                    </div>

                    <!-- Info Body -->
                    <div class="flex-1 min-w-0 flex flex-col justify-between py-0.5 pr-2">
                        <div>
                            <div class="flex items-center justify-between">
                                <div class="text-base font-black text-brand-700" x-text="formatPrice(land.price) + ' ₮'"></div>
                                <template x-if="land.isEgazarVerified">
                                    <span class="text-[9px] text-emerald-800 font-extrabold flex items-center gap-1 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">
                                        <i class="fa-solid fa-circle-check text-emerald-600"></i> Баталгаажсан
                                    </span>
                                </template>
                            </div>
                            <h3 class="font-bold text-slate-800 text-xs mt-1 line-clamp-2 hover:text-brand-600 transition" x-text="land.title"></h3>
                            <p class="text-slate-500 text-[11px] flex items-center gap-1 mt-1">
                                <i class="fa-solid fa-location-dot text-slate-400"></i>
                                <span class="truncate" x-text="land.location"></span>
                            </p>
                        </div>

                        <div>
                            <!-- Size, Cadastral & Utilities Snippet -->
                            <div class="flex items-center gap-2 text-[10px] text-slate-600 font-medium my-1.5 flex-wrap">
                                <span class="bg-slate-100 px-2 py-0.5 rounded font-bold text-slate-800" x-text="land.sizeText"></span>
                                <span class="font-mono text-slate-400" x-text="'№ ' + land.cadastralNo"></span>
                                <div class="flex items-center gap-1.5 text-slate-400 ml-auto">
                                    <i class="fa-solid fa-bolt" :class="land.hasElectricity ? 'text-amber-500' : 'text-slate-300'"></i>
                                    <i class="fa-solid fa-droplet" :class="land.hasWater ? 'text-blue-500' : 'text-slate-300'"></i>
                                    <i class="fa-solid fa-road" :class="land.hasRoad ? 'text-emerald-600' : 'text-slate-300'"></i>
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-[10px] text-slate-400 pt-1 border-t border-slate-100">
                                <span x-text="land.date"></span>
                                <button @click.stop="openModal(land)" class="font-bold text-brand-600 hover:text-brand-700 flex items-center gap-1">
                                    <span>Дэлгэрэнгүй</span>
                                    <i class="fa-solid fa-arrow-right text-[9px]"></i>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </template>
        </div>

        <!-- Right Map Container (Давхардал арилсан ганц зөв div) -->
        <div class="lg:col-span-6 bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm sticky top-20 h-[650px] relative">
            <div id="splitMap" style="width: 100%; height: 100%;" class="z-10"></div>
            
            <!-- Floating Map Action -->
            <button @click="searchInCurrentMapArea()" class="absolute top-4 left-1/2 -translate-x-1/2 z-20 bg-white/95 backdrop-blur-md text-slate-800 hover:text-brand-700 font-bold text-xs px-4 py-2 rounded-full shadow-lg border border-slate-200 flex items-center gap-2 transition hover:scale-105">
                <i class="fa-solid fa-rotate-right text-brand-600"></i>
                <span>Энэ бүсэд хайх</span>
            </button>
        </div>

    </div>
@endsection
<script>

(function () {
    function initSplitMap() {
        const container = document.getElementById('splitMap');

        if (!container || typeof L === 'undefined') {
            return;
        }

        // Давхар газрын зураг үүсгэхгүй
        if (container._leaflet_id) {
            return;
        }

        const splitMap = L.map(container, {
            center: [47.9188, 106.9176],
            zoom: 13,
            zoomControl: true
        });

        // Хиймэл дагуулын зураг — URL зөв форматтай
        const satelliteLayer = L.tileLayer(
            'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
            {
                maxZoom: 19,
                attribution: 'Tiles &copy; Esri'
            }
        );

        // Энгийн газрын зураг
        const streetLayer = L.tileLayer(
            'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }
        );

        satelliteLayer.addTo(splitMap);

        L.control.layers({
            'Хиймэл дагуул': satelliteLayer,
            'Энгийн зураг': streetLayer
        }).addTo(splitMap);

        // Зураг ачаалагдахгүй байвал Console дээр харуулна
        satelliteLayer.on('tileerror', function (event) {
            console.error('Satellite tile алдаа:', event);
        });

        streetLayer.on('tileerror', function (event) {
            console.error('Street tile алдаа:', event);
        });

        // Гаднаас ашиглах боломж
        window.splitMapInstance = splitMap;

        // Газрын зураг харагдах үед хэмжээг шинэчилнэ
        function refreshMap() {
            if (!container.isConnected) return;

            if (container.offsetWidth > 0 &&
                container.offsetHeight > 0) {
                splitMap.invalidateSize({
                    pan: false,
                    debounceMoveend: true
                });
            }
        }

        // Alpine x-show болон responsive layout өөрчлөгдөхөд
        if (typeof ResizeObserver !== 'undefined') {
            const observer = new ResizeObserver(refreshMap);
            observer.observe(container);
        }

        requestAnimationFrame(() => {
            requestAnimationFrame(refreshMap);
        });

        // Зар сонгоход marker тавихад ашиглаж болно
        window.zoomToMapLocation = function (lat, lng, zoom = 16) {
            splitMap.setView([lat, lng], zoom);
        };
    }

    function startMap() {
        if (typeof L === 'undefined') {
            console.error(
                'Leaflet ачаалагдаагүй байна. leaflet.js файлыг шалгана уу.'
            );
            return;
        }

        initSplitMap();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startMap, {
            once: true
        });
    } else {
        startMap();
    }
})();

</script>