<!-- resources/views/components/modal-detail.blade.php -->
<div x-show="openDetail"
     x-cloak
     @keydown.escape.window="openDetail = false"
     @click.self="openDetail = false"
     class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">

    <div class="bg-white rounded-3xl max-w-4xl w-full shadow-2xl relative max-h-[92vh] flex flex-col overflow-hidden">

        <!-- Гарчиг -->
        <div class="flex items-start justify-between gap-3 px-5 sm:px-6 py-4 border-b border-slate-100 bg-white">
            <div class="min-w-0">
                <h3 class="text-base sm:text-lg font-black text-slate-900 leading-tight truncate"
                    x-text="selectedLand ? selectedLand.title : ''"></h3>
                <p class="text-[11px] text-slate-500 font-semibold mt-0.5 flex items-center gap-1.5">
                    <i class="fa-solid fa-location-dot text-red-500"></i>
                    <span x-text="selectedLand ? selectedLand.location : ''"></span>
                </p>
                <span x-show="selectedLand"
                      class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-black text-white mt-1.5"
                      :class="isRent(selectedLand) ? 'bg-blue-600' : 'bg-slate-700'"
                      x-text="selectedLand ? dealLabel(selectedLand) : ''"></span>

                <span x-show="selectedLand"
                      class="inline-flex items-center gap-1.5 ml-2 px-2 py-0.5 rounded-md text-[10px] font-bold text-slate-600 bg-slate-100 border border-slate-200 align-middle"
                      title="Зарыг хэдэн хүн үзсэн">
                    <i class="fa-regular fa-eye text-[10px]"></i>
                    <span x-text="landViews(selectedLand) + ' хүн үзсэн'"></span>
                </span>
            </div>

            <button @click="openDetail = false"
                    class="shrink-0 w-9 h-9 bg-slate-100 hover:bg-slate-200 rounded-full flex items-center justify-center text-slate-500 transition">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        <template x-if="selectedLand">
            <div class="overflow-y-auto hide-scrollbar">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-0 md:gap-5 p-4 sm:p-6">

                    <!-- Зургийн цомог -->
                    <div>
                        <div class="relative rounded-2xl overflow-hidden bg-slate-100 border border-slate-200">
                            <img :src="detailImage"
                                 :alt="selectedLand.title"
                                 x-on:error="$el.src = placeholderImage"
                                 class="w-full h-64 sm:h-80 object-cover">

                            <template x-if="selectedLand.is_egazar_verified">
                                <div class="egazar-ribbon-wrapper">
                                    <div class="egazar-ribbon">EGAZAR.MN</div>
                                </div>
                            </template>

                            <!-- Өмнөх / Дараах -->
                            <template x-if="detailImages.length > 1">
                                <div>
                                    <button type="button" @click.stop="stepDetailImage(-1)"
                                            class="absolute left-2 top-1/2 -translate-y-1/2 z-20 w-9 h-9 rounded-full bg-white/90 hover:bg-white text-slate-700 shadow-lg border border-slate-200 flex items-center justify-center transition">
                                        <i class="fa-solid fa-chevron-left text-xs"></i>
                                    </button>
                                    <button type="button" @click.stop="stepDetailImage(1)"
                                            class="absolute right-2 top-1/2 -translate-y-1/2 z-20 w-9 h-9 rounded-full bg-white/90 hover:bg-white text-slate-700 shadow-lg border border-slate-200 flex items-center justify-center transition">
                                        <i class="fa-solid fa-chevron-right text-xs"></i>
                                    </button>

                                    <span class="absolute bottom-3 right-3 z-20 bg-black/60 text-white text-[10px] font-bold px-2 py-1 rounded-lg"
                                          x-text="(detailImageIndex + 1) + ' / ' + detailImages.length"></span>
                                </div>
                            </template>

                            <!-- Хэмжээ -->
                            <span class="absolute bottom-3 left-3 z-20 bg-black/60 backdrop-blur-md text-white px-2.5 py-1 rounded-lg text-[11px] font-semibold">
                                <i class="fa-solid fa-ruler-combined mr-1"></i><span x-text="landSize(selectedLand)"></span>
                            </span>
                        </div>

                        <!-- Жижиг зургууд -->
                        <template x-if="detailImages.length > 1">
                            <div class="flex gap-2 mt-2.5 overflow-x-auto hide-scrollbar pb-1">
                                <template x-for="(image, index) in detailImages" :key="'thumb-' + index">
                                    <button type="button" @click.stop="showDetailImage(index)"
                                            class="w-20 h-16 shrink-0 rounded-xl overflow-hidden border-2 transition"
                                            :class="detailImageIndex === index ? 'border-emerald-500 ring-2 ring-emerald-500/20' : 'border-slate-200 hover:border-emerald-300'">
                                        <img :src="image" class="w-full h-full object-cover" alt="Зургийн жижиг хувилбар">
                                    </button>
                                </template>
                            </div>
                        </template>

                        <!-- egazar.mn кадастрын зураг харах -->
                        <template x-if="selectedLand.cadastral_code">
                            <a :href="'https://www.egazar.gov.mn/map?parcel_no=' + encodeURIComponent(selectedLand.cadastral_code)"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="mt-2.5 flex items-center justify-center gap-2 bg-white hover:bg-emerald-50 text-emerald-700 font-bold px-4 py-2.5 rounded-xl border border-emerald-200 transition shadow-sm">
                                <i class="fa-solid fa-map-location-dot text-xs"></i>
                                <span>egazar.mn харах</span>
                                <i class="fa-solid fa-arrow-up-right-from-square text-[10px] opacity-70"></i>
                            </a>
                        </template>
                    </div>

                    <!-- Мэдээлэл -->
                    <div class="flex flex-col gap-3 text-xs mt-4 md:mt-0">

                        <!-- Үнэ -->
                        <div class="rounded-2xl p-4 border"
                             :class="isRent(selectedLand) ? 'bg-blue-50 border-blue-200' : 'bg-emerald-50 border-emerald-200'">
                            <div class="flex items-end justify-between gap-3">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider block mb-1"
                                          :class="isRent(selectedLand) ? 'text-blue-700' : 'text-emerald-700'"
                                          x-text="isRent(selectedLand) ? 'Түрээсийн үнэ (сард)' : 'Зарах үнэ'"></span>
                                    <span class="text-xl sm:text-2xl font-black leading-none"
                                          :class="isRent(selectedLand) ? 'text-blue-700' : 'text-emerald-700'"
                                          x-text="Number(selectedLand.price).toLocaleString() + ' ' + priceUnit(selectedLand)"></span>
                                </div>

                                <template x-if="pricePerM2(selectedLand)">
                                    <span class="text-[11px] font-bold rounded-lg px-2 py-1 border"
                                          :class="isRent(selectedLand) ? 'text-blue-700 bg-white/70 border-blue-200' : 'text-emerald-700 bg-white/70 border-emerald-200'"
                                          x-text="Number(pricePerM2(selectedLand)).toLocaleString() + ' ₮/м²' + (isRent(selectedLand) ? '/сар' : '')"></span>
                                </template>
                            </div>
                        </div>

                        <!-- Үндсэн үзүүлэлтүүд -->
                        <div class="grid grid-cols-2 gap-2">
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3">
                                <span class="text-[10px] font-bold text-slate-500 uppercase block mb-1">Хэмжээ</span>
                                <span class="font-bold text-slate-800" x-text="landSize(selectedLand)"></span>
                            </div>

                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3">
                                <span class="text-[10px] font-bold text-slate-500 uppercase block mb-1">Эрхийн төрөл</span>
                                <span class="inline-flex items-center gap-1 font-bold px-2 py-0.5 rounded-md text-[11px] text-white"
                                      :class="selectedLand.rights_type === 'Өмчлөх' ? 'bg-emerald-600' : 'bg-blue-600'"
                                      x-text="selectedLand.rights_type"></span>
                            </div>

                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3">
                                <span class="text-[10px] font-bold text-slate-500 uppercase block mb-1">Кадастрын дугаар</span>
                                <span class="font-mono font-bold text-slate-800"
                                      x-text="selectedLand.cadastral_code ? selectedLand.cadastral_code : '—'"></span>
                            </div>

                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3">
                                <span class="text-[10px] font-bold text-slate-500 uppercase block mb-1">Оруулсан огноо</span>
                                <span class="font-bold text-slate-800" x-text="selectedLand.created_at"></span>
                            </div>
                        </div>

                        <!-- egazar баталгаажуулалт -->
                        <template x-if="selectedLand.is_egazar_verified">
                            <div class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 rounded-xl px-3 py-2.5">
                                <i class="fa-solid fa-shield-halved text-emerald-600"></i>
                                <span class="font-bold text-emerald-800">egazar.mn баталгаажсан зар</span>
                            </div>
                        </template>

                        <!-- Дэд бүтэц -->
                        <div>
                            <span class="text-[10px] font-bold text-slate-500 uppercase block mb-1.5">Инженерчлэл & Дэд бүтэц</span>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5">
                                <span class="px-2 py-1.5 rounded-lg border text-[11px] font-semibold flex items-center gap-1"
                                      :class="selectedLand.has_electricity ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-slate-50 text-slate-400 border-slate-200 line-through'">
                                    ⚡ Тог
                                </span>
                                <span class="px-2 py-1.5 rounded-lg border text-[11px] font-semibold flex items-center gap-1"
                                      :class="selectedLand.has_water ? 'bg-cyan-50 text-cyan-700 border-cyan-200' : 'bg-slate-50 text-slate-400 border-slate-200 line-through'">
                                    💧 Ус
                                </span>
                                <span class="px-2 py-1.5 rounded-lg border text-[11px] font-semibold flex items-center gap-1"
                                      :class="selectedLand.near_road ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-slate-50 text-slate-400 border-slate-200 line-through'">
                                    🚧 Зам
                                </span>
                                <span class="px-2 py-1.5 rounded-lg border text-[11px] font-semibold flex items-center gap-1"
                                      :class="selectedLand.has_fence ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-50 text-slate-400 border-slate-200 line-through'">
                                    🏡 Хашаа
                                </span>
                                <span class="px-2 py-1.5 rounded-lg border text-[11px] font-semibold flex items-center gap-1"
                                      :class="selectedLand.is_swamp ? 'bg-sky-50 text-sky-700 border-sky-200' : 'bg-slate-50 text-slate-400 border-slate-200 line-through'">
                                    🌊 Намагтай
                                </span>
                                <span class="px-2 py-1.5 rounded-lg border text-[11px] font-semibold flex items-center gap-1"
                                      :class="selectedLand.is_sloped ? 'bg-orange-50 text-orange-700 border-orange-200' : 'bg-slate-50 text-slate-400 border-slate-200 line-through'">
                                    📐 Налуу
                                </span>
                            </div>
                        </div>

                        <!-- Тайлбар -->
                        <template x-if="selectedLand.description">
                            <div>
                                <span class="text-[10px] font-bold text-slate-500 uppercase block mb-1.5">Дэлгэрэнгүй мэдээлэл</span>
                                <p class="text-slate-700 leading-relaxed whitespace-pre-line bg-slate-50 border border-slate-200 rounded-xl p-3"
                                   x-text="selectedLand.description"></p>
                            </div>
                        </template>

                        <!-- Холбоо барих -->
                        <div class="mt-auto flex flex-col sm:flex-row gap-2 pt-1">
                            <template x-if="selectedLand.phone">
                                <a :href="'tel:' + selectedLand.phone"
                                   class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 py-2.5 rounded-xl transition shadow-md flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-phone text-xs"></i>
                                    <span x-text="selectedLand.phone"></span>
                                </a>
                            </template>

                            <button type="button" @click="showLandOnMap(selectedLand)"
                                    class="flex-1 bg-white hover:bg-slate-50 text-slate-800 font-bold px-4 py-2.5 rounded-xl border border-slate-200 transition flex items-center justify-center gap-2">
                                <i class="fa-solid fa-map-location-dot text-emerald-600 text-xs"></i>
                                Газрын зураг дээр харах
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Үнийн санал илгээх -->
                <div class="px-4 sm:px-6 pb-5">
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs font-black text-slate-800 flex items-center gap-1.5">
                                    <i class="fa-solid fa-hand-holding-dollar text-emerald-600"></i>
                                    Үнийн санал илгээх
                                </p>
                                <p class="text-[11px] text-slate-500 font-semibold mt-1">
                                    Санал болгож буй үнэ, утас, тайлбараа үлдээнэ үү. Зар эзэмшигч тантай холбогдоно.
                                </p>
                                <p class="text-[11px] text-slate-400 font-semibold mt-1 flex items-center gap-1">
                                    <i class="fa-regular fa-eye text-[10px]"></i>
                                    <span x-text="landViews(selectedLand) + ' хүн үзсэн'"></span>
                                </p>
                            </div>

                            <button type="button" @click="toggleOfferForm()"
                                    class="shrink-0 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-3.5 py-2 rounded-xl transition shadow-sm flex items-center gap-1.5">
                                <i class="fa-solid" :class="offerOpen ? 'fa-xmark' : 'fa-paper-plane'"></i>
                                <span x-text="offerOpen ? 'Хаах' : 'Санал илгээх'"></span>
                            </button>
                        </div>

                        <!-- Амжилттай илгээсэн -->
                        <template x-if="offerSent">
                            <div class="mt-3 flex items-start gap-2 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-3 py-2.5">
                                <i class="fa-solid fa-circle-check text-emerald-600 mt-0.5"></i>
                                <span class="text-[11px] font-bold" x-text="offerSent"></span>
                            </div>
                        </template>

                        <!-- Форм -->
                        <div x-show="offerOpen" x-cloak x-transition.opacity
                             class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Санал болгох үнэ (₮) <span class="text-red-500">*</span></label>
                                <input type="number" min="0" step="1000" x-model="offerPrice"
                                       @keydown.enter.prevent="submitOffer()"
                                       placeholder="Жишээ: 14500000"
                                       class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2.5 text-xs font-semibold text-slate-800 focus:outline-none focus:border-emerald-500">
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Утас <span class="text-red-500">*</span></label>
                                <input type="tel" x-model="offerPhone"
                                       @keydown.enter.prevent="submitOffer()"
                                       placeholder="Жишээ: 99112233"
                                       class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2.5 text-xs font-semibold text-slate-800 focus:outline-none focus:border-emerald-500">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Тайлбар</label>
                                <textarea x-model="offerMessage" rows="3"
                                          placeholder="Нэмэлт тайлбар, тохиролцох нөхцөл..."
                                          class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2.5 text-xs font-semibold text-slate-800 focus:outline-none focus:border-emerald-500 resize-none"></textarea>
                            </div>

                            <div class="sm:col-span-2 flex flex-col sm:flex-row sm:items-center gap-2">
                                <button type="button" @click="submitOffer()" :disabled="offerSubmitting"
                                        class="bg-slate-900 hover:bg-slate-800 disabled:opacity-60 disabled:cursor-not-allowed text-white font-bold text-xs px-4 py-2.5 rounded-xl transition flex items-center justify-center gap-2">
                                    <i class="fa-solid" :class="offerSubmitting ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i>
                                    <span x-text="offerSubmitting ? 'Илгээж байна...' : 'Илгээх'"></span>
                                </button>

                                <template x-if="offerError">
                                    <p class="text-[11px] font-bold text-rose-600 bg-rose-50 border border-rose-200 rounded-lg px-3 py-2" x-text="offerError"></p>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
