<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition group flex flex-col justify-between">
    <div class="relative h-48 bg-gray-100 overflow-hidden">
        <!-- Зураг -->
        <img :src="landImage(land)" :alt="land.title" x-on:error="$el.src = placeholderImage" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
        
        <!-- Эрхийн төрөл (Өмчлөх / Эзэмших) -->
        <span class="absolute top-3 left-3 px-2.5 py-1 rounded-lg text-xs font-bold text-white shadow-md"
              :class="land.rights_type === 'Өмчлөх' ? 'bg-emerald-600' : 'bg-blue-600'"
              x-text="land.rights_type">
        </span>

        <!-- egazar.mn Баталгаажсан туузан тэмдэглэгээ (Diagonal Ribbon) -->
        <template x-if="land.is_egazar_verified">
            <div class="egazar-ribbon-wrapper">
                <div class="egazar-ribbon">EGAZAR.MN</div>
            </div>
        </template>

        <!-- Хэмжээ -->
        <div class="absolute bottom-3 left-3 bg-black/60 backdrop-blur-md text-white px-2.5 py-1 rounded-lg text-xs font-semibold">
            <i class="fa-solid fa-ruler-combined mr-1"></i> <span x-text="landSize(land)"></span>
        </div>

        <!-- Зарын төрөл (Зарах / Түрээслэх) -->
        <span class="absolute bottom-3 right-3 px-2.5 py-1 rounded-lg text-xs font-bold text-white shadow-md"
              :class="isRent(land) ? 'bg-blue-600' : 'bg-slate-700'"
              x-text="dealLabel(land)"></span>
    </div>

    <div class="p-4 flex flex-col flex-grow justify-between">
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-lg font-black" :class="isRent(land) ? 'text-blue-600' : 'text-emerald-600'" x-text="Number(land.price).toLocaleString() + ' ' + priceUnit(land)"></span>
                <span class="text-[11px] bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full font-semibold border border-emerald-200 flex items-center gap-1">
                    <i class="fa-solid fa-shield-halved text-[10px]"></i> Баталгаажсан
                </span>
            </div>

            <h3 class="font-bold text-gray-900 text-sm line-clamp-2 mb-1.5 group-hover:text-emerald-600 transition" x-text="land.title"></h3>
            
            <p class="text-xs text-gray-500 flex items-center gap-1 mb-3">
                <i class="fa-solid fa-location-dot text-red-500"></i> <span x-text="land.location"></span>
            </p>

            <!-- Дэд бүтцийн бичвэр тэмдэглэгээ (Badges) -->
            <div class="flex flex-wrap gap-1.5 mb-4">
                <span class="px-2 py-0.5 rounded text-[11px] font-semibold" :class="land.has_electricity ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-gray-100 text-gray-400 line-through'">Тог</span>
                <span class="px-2 py-0.5 rounded text-[11px] font-semibold" :class="land.has_water ? 'bg-cyan-50 text-cyan-700 border border-cyan-200' : 'bg-gray-100 text-gray-400 line-through'">Ус</span>
                <span class="px-2 py-0.5 rounded text-[11px] font-semibold" :class="land.near_road ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-gray-100 text-gray-400 line-through'">Зам</span>
                <span class="px-2 py-0.5 rounded text-[11px] font-semibold" :class="land.has_fence ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-400 line-through'">Хашаа</span>
            </div>
        </div>

        <div class="border-t border-gray-100 pt-3 flex items-center justify-between text-xs text-gray-500">
            <div class="flex items-center gap-2">
                <span x-text="land.created_at"></span>
                <span class="text-gray-300">•</span>
                <span class="flex items-center gap-1" title="Зарыг хэдэн хүн үзсэн">
                    <i class="fa-regular fa-eye text-[10px]"></i>
                    <span x-text="landViews(land)"></span>
                </span>
            </div>
            <button @click="openDetailModal(land)" class="text-emerald-600 font-bold hover:underline flex items-center gap-1">
                Дэлгэрэнгүй <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </button>
        </div>
    </div>
</div>