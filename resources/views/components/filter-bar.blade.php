<!-- resources/views/components/filter-bar.blade.php -->
<div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm mb-4 space-y-3">
    <div class="flex items-center justify-between gap-2">
        <p class="text-[11px] font-black text-gray-500 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-sliders text-emerald-600"></i> Шүүлтүүр
            <template x-if="activeFilterCount > 0">
                <span class="bg-emerald-600 text-white rounded-full px-2 py-0.5 text-[10px] font-bold normal-case"
                      x-text="activeFilterCount + ' идэвхтэй'"></span>
            </template>
        </p>
        <button x-show="activeFilterCount > 0" x-cloak @click="resetFilters()"
                class="text-[11px] font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
            <i class="fa-solid fa-xmark"></i> Бүгдийг арилгах
        </button>
    </div>

    <!-- Зарын төрөл: Бүгд / Зарах / Түрээслэх -->
    <div class="flex flex-wrap items-center gap-2">
        <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Зарын төрөл:</span>
        <div class="inline-flex bg-gray-100 rounded-xl p-1 gap-1">
            <button type="button" id="filter-all" @click="filters.dealType = 'all'"
                    :class="filters.dealType === 'all' ? 'bg-white text-emerald-700 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1">
                <i class="fa-solid fa-layer-group text-[10px]"></i> Бүгд
            </button>
            <button type="button" id="filter-sale" @click="filters.dealType = 'sale'"
                    :class="filters.dealType === 'sale' ? 'bg-white text-emerald-700 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1">
                <i class="fa-solid fa-tag text-[10px]"></i> Зарах
            </button>
            <button type="button" id="filter-rent" @click="filters.dealType = 'rent'"
                    :class="filters.dealType === 'rent' ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1">
                <i class="fa-solid fa-key text-[10px]"></i> Түрээслэх
            </button>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Байршил / Дүүрэг -->
        <div>
            <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Байршил / Дүүрэг</label>
            <select id="filter-district" x-model="filters.district" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold text-gray-700 focus:outline-none focus:border-emerald-500">
                <option value="">Бүх байршил</option>
                <option value="Хан-Уул">Хан-Уул</option>
                <option value="Баянзүрх">Баянзүрх</option>
                <option value="Сүхбаатар">Сүхбаатар</option>
                <option value="Чингэлтэй">Чингэлтэй</option>
                <option value="Баянгол">Баянгол</option>
                <option value="Сонгинохайрхан">Сонгинохайрхан</option>
            </select>
        </div>

        <!-- Зориулалт -->
        <div>
            <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Зориулалт</label>
            <select id="filter-purpose" x-model="filters.purpose" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold text-gray-700 focus:outline-none focus:border-emerald-500">
                <option value="">Бүх зориулалт</option>
                <option value="Амины сууц">Амины сууц</option>
                <option value="Хүлэмж">Хүлэмж</option>
                <option value="Үйлдвэрлэл">Үйлдвэрлэл</option>
                <option value="Амралтын бүс">Амралтын бүс</option>
            </select>
        </div>

        <!-- Эрхийн төрөл -->
        <div>
            <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Эрхийн төрөл</label>
            <select id="filter-rights" x-model="filters.rightsType" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold text-gray-700 focus:outline-none focus:border-emerald-500">
                <option value="">Бүх эрх</option>
                <option value="Өмчлөх">Өмчлөх</option>
                <option value="Эзэмших">Эзэмших</option>
            </select>
        </div>

        <!-- Дээд үнэ -->
        <div>
            <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Дээд үнэ (₮)</label>
            <select id="filter-price" x-model="filters.maxPrice" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold text-gray-700 focus:outline-none focus:border-emerald-500">
                <option value="">Хязгааргүй</option>
                <option value="50000000">50 сая ₮ хүртэл</option>
                <option value="100000000">100 сая ₮ хүртэл</option>
                <option value="200000000">200 сая ₮ хүртэл</option>
            </select>
        </div>

        <!-- Доод хэмжээ -->
        <div>
            <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Доод хэмжээ</label>
            <select id="filter-size" x-model="filters.minSize" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold text-gray-700 focus:outline-none focus:border-emerald-500">
                <option value="">Хамаагүй</option>
                <option value="0.05">0.05 га-аас дээш</option>
                <option value="0.1">0.1 га-аас дээш</option>
                <option value="0.5">0.5 га-аас дээш</option>
            </select>
        </div>

        <!-- Цэвэрлэх товч -->
        <div class="flex items-end">
            <button @click="resetFilters()"
                    :class="activeFilterCount > 0 ? 'bg-emerald-600 text-white hover:bg-emerald-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    class="w-full font-bold py-2 px-3 rounded-xl text-xs transition flex items-center justify-center gap-1">
                <i class="fa-solid fa-rotate-right"></i> Цэвэрлэх
            </button>
        </div>
    </div>

    <!-- Дэд бүтцийн шүүлтүүр чекбоссууд -->
    <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-gray-100 text-xs">
        <span class="text-gray-500 font-semibold mr-2">egazar.mn / Инженерчлэл:</span>
        <label class="flex items-center gap-1.5 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-200 cursor-pointer hover:bg-emerald-50 transition">
            <input type="checkbox" x-model="filters.egazarVerified" class="rounded text-emerald-600 focus:ring-emerald-500">
            <span class="font-medium text-gray-700">egazar.mn баталгаажсан</span>
        </label>
        <label class="flex items-center gap-1.5 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-200 cursor-pointer hover:bg-amber-50 transition">
            <input type="checkbox" x-model="filters.electricity" class="rounded text-amber-600 focus:ring-amber-500">
            <span class="font-medium text-gray-700">⚡ Тог/Цахилгаан</span>
        </label>
        <label class="flex items-center gap-1.5 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-200 cursor-pointer hover:bg-cyan-50 transition">
            <input type="checkbox" x-model="filters.water" class="rounded text-cyan-600 focus:ring-cyan-500">
            <span class="font-medium text-gray-700">💧 Ус</span>
        </label>
        <label class="flex items-center gap-1.5 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-200 cursor-pointer hover:bg-indigo-50 transition">
            <input type="checkbox" x-model="filters.road" class="rounded text-indigo-600 focus:ring-indigo-500">
            <span class="font-medium text-gray-700">🚧 Зам дагуу</span>
        </label>
        <label class="flex items-center gap-1.5 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-200 cursor-pointer hover:bg-emerald-50 transition">
            <input type="checkbox" x-model="filters.fence" class="rounded text-emerald-600 focus:ring-emerald-500">
            <span class="font-medium text-gray-700">🏡 Хашаатай</span>
        </label>
    </div>
</div>
