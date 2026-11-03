@extends('layouts.app')

@section('title', 'Газрын зарууд - Газар.mn')

@section('content')
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-cloak
             class="mb-4 flex items-start gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl p-4 shadow-sm">
            <i class="fa-solid fa-circle-check text-emerald-600 text-lg mt-0.5"></i>
            <div class="flex-1">
                <p class="font-black text-sm">Амжилттай</p>
                <p class="text-xs font-semibold text-emerald-700 mt-0.5">{{ session('success') }}</p>
            </div>
            <button @click="show = false" class="text-emerald-600 hover:text-emerald-800 transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    <!-- Filter Bar Component -->
    @include('components.filter-bar')


    <!-- View Controls & Counter -->
    <div class="flex flex-col sm:flex-row justify-between items-center my-4 gap-3">
        <div class="flex items-center flex-wrap gap-2 text-sm text-gray-600 font-medium">
            <span>
                Сонгосон бүсэд <span class="text-brand-600 font-bold" x-text="filteredLands.length"></span> газрын зар олдлоо
            </span>
            <button x-show="filterByMapArea" @click="clearMapAreaFilter()" x-cloak
                    class="flex items-center gap-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold px-2.5 py-1 rounded-full hover:bg-emerald-100 transition">
                <i class="fa-solid fa-map-location-dot"></i>
                Зургийн бүсээр шүүсэн
                <i class="fa-solid fa-xmark text-[10px]"></i>
            </button>
        </div>

        <div class="flex flex-wrap items-center justify-center bg-gray-200 p-1 rounded-xl gap-1 text-xs font-bold">
            <button id="btn-split" @click="viewMode = 'split'; $nextTick(() => syncMap())"
                    :class="viewMode === 'split' ? 'bg-white text-brand-700 shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                    class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                <i class="fa-solid fa-map"></i> Зураг + Жагсаалт
            </button>

            <button @click="viewMode = 'map'; $nextTick(() => syncMap())"
                    :class="viewMode === 'map' ? 'bg-white text-brand-700 shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                    class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                <i class="fa-solid fa-earth-asia"></i> Бүтэн Зураглал
            </button>

            <button @click="viewMode = 'grid'"
                    :class="viewMode === 'grid' ? 'bg-white text-brand-700 shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                    class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                <i class="fa-solid fa-border-all"></i> Карт Жагсаалт
            </button>
        </div>
    </div>

    <!-- Listings Container (Grid View) -->
    <div x-show="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <template x-for="land in filteredLands" :key="land.id">
            @include('components.land-card')
        </template>

        <!-- Хоосон төлөв -->
        <div x-show="filteredLands.length === 0" x-cloak
             class="col-span-full bg-white border border-dashed border-slate-300 rounded-2xl p-10 text-center">
            <i class="fa-solid fa-magnifying-glass-location text-3xl text-slate-300 mb-3"></i>
            <p class="text-sm font-semibold text-slate-500">Шүүлтүүрт тохирох газрын зар олдсонгүй</p>
            <button @click="resetFilters()"
                    class="mt-3 text-xs font-bold text-emerald-600 hover:underline">
                Шүүлтүүрийг цэвэрлэх
            </button>
        </div>
    </div>

    <!-- Split / Full Map Layout -->
    <div x-show="viewMode === 'split' || viewMode === 'map'" x-cloak class="flex flex-col lg:flex-row gap-5 items-start">

        <!-- Left Scrollable Cards List -->
        <div x-show="viewMode === 'split'"
             class="w-full lg:w-1/2 space-y-3 lg:max-h-[750px] lg:overflow-y-auto pr-1 hide-scrollbar order-2 lg:order-1">

            <template x-for="land in filteredLands" :key="'split-' + land.id">
                <div :id="'card-' + land.id"
                     @mouseenter="highlightLand(land.id)"
                     @mouseleave="unhighlightLand(land.id)"
                     @click="zoomToLand(land)"
                     class="bg-white rounded-2xl border p-3 cursor-pointer transition-all duration-200 shadow-sm hover:shadow-md flex gap-3 relative overflow-hidden"
                     :class="activeLandId === land.id ? 'ring-2 ring-emerald-500 border-emerald-500 bg-emerald-50/30' : 'border-slate-200'">

                    <!-- Зураг & Хэмжээ -->
                    <div class="relative w-28 h-28 sm:w-32 sm:h-32 rounded-xl overflow-hidden bg-slate-100 flex-shrink-0">
                        <img :src="landImage(land)" :alt="land.title" loading="lazy"
                             x-on:error="$el.src = placeholderImage"
                             class="w-full h-full object-cover transition duration-300"
                             :class="activeLandId === land.id ? 'scale-105' : ''">

                        <!-- EGAZAR.MN туузан тэмдэглэгээ -->
                        <template x-if="land.is_egazar_verified">
                            <div class="egazar-ribbon-wrapper">
                                <div class="egazar-ribbon">EGAZAR.MN</div>
                            </div>
                        </template>

                        <span class="absolute bottom-1.5 left-1.5 bg-black/60 backdrop-blur-sm text-white px-2 py-0.5 rounded-md text-[9px] font-bold z-20">
                            <i class="fa-solid fa-ruler-combined mr-1"></i><span x-text="landSize(land)"></span>
                        </span>
                    </div>

                    <!-- Info Body -->
                    <div class="flex-1 min-w-0 flex flex-col justify-between py-0.5 pr-1">
                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <div class="text-base font-black leading-tight"
                                     :class="isRent(land) ? 'text-blue-600' : 'text-emerald-600'"
                                     x-text="formatPrice(land.price) + ' ' + priceUnit(land)"></div>

                                <span class="shrink-0 px-2 py-0.5 rounded-md text-[9px] font-black text-white shadow-sm"
                                      :class="isRent(land) ? 'bg-blue-600' : 'bg-slate-700'"
                                      x-text="dealLabel(land)"></span>
                            </div>

                            <h3 class="font-bold text-slate-800 text-xs mt-1 line-clamp-2 hover:text-emerald-600 transition"
                                x-text="land.title"></h3>

                            <p class="text-slate-500 text-[11px] flex items-center gap-1 mt-1">
                                <i class="fa-solid fa-location-dot text-slate-400"></i>
                                <span class="truncate" x-text="land.location"></span>
                            </p>

                            <p class="font-mono text-[10px] text-slate-400 mt-0.5"
                               x-text="land.cadastral_code ? '№ ' + land.cadastral_code : '№ —'"></p>
                        </div>

                        <div>
                            <!-- Дэд бүтцийн тэмдэглэгээ -->
                            <div class="flex items-center gap-1.5 text-[10px] font-semibold flex-wrap my-1.5">
                                <span class="px-1.5 py-0.5 rounded border"
                                      :class="land.has_electricity ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-slate-50 text-slate-300 border-slate-200 line-through'">Тог</span>
                                <span class="px-1.5 py-0.5 rounded border"
                                      :class="land.has_water ? 'bg-cyan-50 text-cyan-700 border-cyan-200' : 'bg-slate-50 text-slate-300 border-slate-200 line-through'">Ус</span>
                                <span class="px-1.5 py-0.5 rounded border"
                                      :class="land.near_road ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-slate-50 text-slate-300 border-slate-200 line-through'">Зам</span>
                                <span class="px-1.5 py-0.5 rounded border"
                                      :class="land.has_fence ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-50 text-slate-300 border-slate-200 line-through'">Хашаа</span>
                            </div>

                            <div class="flex items-center justify-between text-[10px] text-slate-400 pt-1 border-t border-slate-100">
                                <div class="flex items-center gap-1.5">
                                    <span x-text="land.created_at"></span>
                                    <span class="text-slate-300">•</span>
                                    <span class="flex items-center gap-1" title="Зарыг хэдэн хүн үзсэн">
                                        <i class="fa-regular fa-eye text-[9px]"></i>
                                        <span x-text="landViews(land)"></span>
                                    </span>
                                </div>
                                <button @click.stop="openDetailModal(land)"
                                        class="font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                                    <span>Дэлгэрэнгүй</span>
                                    <i class="fa-solid fa-arrow-right text-[9px]"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Хоосон төлөв -->
            <template x-if="filteredLands.length === 0">
                <div class="bg-white border border-dashed border-slate-300 rounded-2xl p-10 text-center">
                    <i class="fa-solid fa-magnifying-glass-location text-3xl text-slate-300 mb-3"></i>
                    <p class="text-sm font-semibold text-slate-500">Энэ бүсэд газрын зар олдсонгүй</p>
                    <button @click="clearMapAreaFilter()"
                            class="mt-3 text-xs font-bold text-emerald-600 hover:underline">
                        Бүх зарыг харах
                    </button>
                </div>
            </template>
        </div>

        <!-- Map -->
        <div class="w-full lg:flex-1 lg:w-1/2 bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm relative h-[420px] sm:h-[540px] lg:h-[650px] order-1 lg:order-2">
            <div id="splitMap" style="width: 100%; height: 100%;" class="z-10"></div>

            <!-- Floating Map Action -->
            <button @click="filterByMapArea ? clearMapAreaFilter() : searchInCurrentMapArea()"
                    class="absolute top-4 left-1/2 -translate-x-1/2 z-20 bg-white/95 backdrop-blur-md text-slate-800 hover:text-brand-700 font-bold text-xs px-4 py-2 rounded-full shadow-lg border border-slate-200 flex items-center gap-2 transition hover:scale-105">
                <i class="fa-solid" :class="filterByMapArea ? 'fa-globe text-brand-600' : 'fa-rotate-right text-brand-600'"></i>
                <span x-text="filterByMapArea ? 'Бүх зарыг харах' : 'Энэ бүсэд хайх'"></span>
            </button>

            <!-- Хотын томоохон газрын тэмдэглэгээ (ориентир) сэлгэх -->
            <button type="button" id="landmarkToggle" aria-pressed="true"
                    title="Хотын томоохон газруудыг газрын зураг дээр харуулах / нуух"
                    class="absolute bottom-4 left-4 z-20 bg-white/95 backdrop-blur-md text-slate-700 hover:text-brand-700 font-bold text-[11px] pl-3 pr-2 py-1.5 rounded-full shadow-lg border border-slate-200 flex items-center gap-2 transition">
                <i class="fa-solid fa-location-dot text-brand-600"></i>
                <span>Хотын тэмдэглэгээ</span>
                <span id="landmarkSwitch" class="relative inline-flex items-center w-8 h-[18px] rounded-full transition-colors duration-200" style="background:#10b981;">
                    <span id="landmarkKnob" class="absolute left-0.5 top-0.5 w-3.5 h-3.5 rounded-full bg-white shadow transition-transform duration-200" style="transform: translateX(14px);"></span>
                </span>
            </button>
        </div>

    </div>
@endsection

@push('scripts')
<script>
    (function () {
        var map = null;
        var markers = new Map();
        var activeId = null;
        var pending = null;
        var focusId = null;
        var retryTimer = null;

        // Хотын томоохон газрууд — газрын зураг дээр баримжаа авах тэмдэглэгээ
        var LANDMARKS = [
            { name: 'Сүхбаатарын талбай', lat: 47.91881, lng: 106.91690, major: true },
            { name: 'Гандан хийд', lat: 47.92300, lng: 106.89500, major: true },
            { name: 'Офицер цайз', lat: 47.91520, lng: 106.97150 },
            { name: 'Баруун 4 зам', lat: 47.91522, lng: 106.89295 },
            { name: 'Зүүн 4 зам', lat: 47.91881, lng: 106.94107 },
            { name: 'Тэмээтэй хөшөө', lat: 47.89900, lng: 106.90900 },
            { name: 'Зайсан', lat: 47.88520, lng: 106.91600, major: true }
        ];

        var showLandmarks = true;
        var landmarkLayer = null;

        function landmarkIcon(landmark) {
            return L.divIcon({
                className: 'landmark-marker' + (landmark.major ? ' is-major' : ''),
                html: '<div class="landmark-marker-inner"><span class="landmark-dot"></span>' +
                      landmark.name + '</div>',
                iconSize: [0, 0]
            });
        }

        function buildLandmarks() {
            if (!map || landmarkLayer) return;

            landmarkLayer = L.layerGroup();

            LANDMARKS.forEach(function (landmark) {
                L.marker([landmark.lat, landmark.lng], {
                    icon: landmarkIcon(landmark),
                    pane: 'landmarkPane',
                    interactive: false,
                    keyboard: false
                }).addTo(landmarkLayer);
            });

            if (showLandmarks) {
                landmarkLayer.addTo(map);
            }
        }

        // Тэмдэглэгээг харуулах / нуух
        function toggleLandmarks(on) {
            showLandmarks = typeof on === 'boolean' ? on : !showLandmarks;

            if (map && landmarkLayer) {
                if (showLandmarks) {
                    landmarkLayer.addTo(map);
                } else {
                    map.removeLayer(landmarkLayer);
                }
            }

            syncLandmarkToggle();
        }

        function syncLandmarkToggle() {
            var button = document.getElementById('landmarkToggle');
            var switcher = document.getElementById('landmarkSwitch');
            var knob = document.getElementById('landmarkKnob');

            if (button) {
                button.setAttribute('aria-pressed', showLandmarks ? 'true' : 'false');
                button.style.opacity = showLandmarks ? '1' : '0.65';
            }

            if (switcher) {
                switcher.style.background = showLandmarks ? '#10b981' : '#cbd5e1';
            }

            if (knob) {
                knob.style.transform = showLandmarks ? 'translateX(14px)' : 'translateX(0)';
            }
        }

        function priceLabel(value) {
            var price = Number(value || 0);

            if (price >= 1000000000) return (price / 1000000000).toLocaleString('en-US') + ' тэрбум';
            if (price >= 1000000) return (price / 1000000).toLocaleString('en-US') + ' сая';

            return price.toLocaleString('en-US');
        }

        function priceIcon(land) {
            var suffix = land.deal_type === 'rent' ? ' ₮/сар' : ' ₮';

            return L.divIcon({
                className: 'price-marker' + (land.deal_type === 'rent' ? ' price-marker-rent' : ''),
                html: '<span class="price-marker-line"></span>' +
                      '<div class="price-marker-inner">' + priceLabel(land.price) + suffix + '</div>' +
                      '<span class="price-marker-anchor"></span>',
                iconSize: [0, 0]
            });
        }

        function escapeHtml(value) {
            return String(value === null || value === undefined ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // Зар дээрх эхний зургийн бүрэн хаяг
        function landImageUrl(land) {
            var images = land ? land.images : null;

            if (typeof images === 'string') {
                try {
                    var parsed = JSON.parse(images);
                    images = Array.isArray(parsed) ? parsed : [images];
                } catch (error) {
                    images = [images];
                }
            }

            if (!Array.isArray(images)) return '';

            for (var i = 0; i < images.length; i++) {
                var image = images[i];
                if (typeof image !== 'string') continue;

                var path = image.trim();
                if (!path) continue;

                if (/^(https?:)?\/\//i.test(path) || path.indexOf('data:') === 0) return path;
                if (path.charAt(0) === '/') return path;

                return '/storage/' + path.replace(/^storage\//, '');
            }

            return '';
        }

        // Үнийн тэмдэглэгээ дээр дарахад гарах карт
        function popupHtml(land) {
            var isRent = land.deal_type === 'rent';
            var deal = isRent ? 'Түрээслэх' : 'Зарах';
            var unit = isRent ? '₮/сар' : '₮';
            var sizeText = Number(land.size_ha || 0).toLocaleString('en-US');
            var image = landImageUrl(land);
            var title = escapeHtml(land.title || 'Газар');

            var media = image
                ? '<img src="' + escapeHtml(image) + '" alt="' + title + '" class="price-popup-img" loading="lazy">'
                : '<div class="price-popup-img price-popup-img-empty">Зураггүй</div>';

            return '<div class="price-popup' + (isRent ? ' is-rent' : '') + '" data-land-open="' + escapeHtml(land.id) + '" role="button" tabindex="0" aria-label="' + title + '">' +
                       '<div class="price-popup-media">' + media + '</div>' +
                       '<div class="price-popup-body">' +
                           '<div class="price-popup-title">' + title + '</div>' +
                           '<div class="price-popup-price">' + Number(land.price || 0).toLocaleString('en-US') + ' ' + unit + '</div>' +
                           '<div class="price-popup-meta">' + deal + ' • ' + sizeText + ' га</div>' +
                       '</div>' +
                   '</div>';
        }

        function labelsIntersect(a, b) {
            return !(a.right < b.left || a.left > b.right || a.bottom < b.top || a.top > b.bottom);
        }

        // Давхцсан үнийн шошгуудыг дээш нь зөрүүлж, заагч шугамаар холбоно
        function layoutLabels() {
            if (!map) return;

            var items = [];
            var placed = [];

            markers.forEach(function (marker) {
                var element = marker.getElement();
                if (!element) return;

                var pill = element.querySelector('.price-marker-inner');
                if (!pill) return;

                var line = element.querySelector('.price-marker-line');

                pill.style.setProperty('--mk-offset', '0px');
                if (line) line.style.setProperty('--mk-line', '0px');

                items.push({ marker: marker, pill: pill, line: line });
            });

            // Дэлгэц дээр доош байрлаж буй нь эхлээд байрлана
            items.sort(function (a, b) {
                return map.latLngToContainerPoint(b.marker.getLatLng()).y -
                       map.latLngToContainerPoint(a.marker.getLatLng()).y;
            });

            items.forEach(function (item) {
                var point = map.latLngToContainerPoint(item.marker.getLatLng());
                var width = item.pill.offsetWidth || 90;
                var height = item.pill.offsetHeight || 26;
                var bottom = point.y - 12;

                var rect = {
                    left: point.x - width / 2,
                    right: point.x + width / 2,
                    top: bottom - height,
                    bottom: bottom
                };

                var step = height + 6;
                var shifts = 0;

                while (shifts < 4 && placed.some(function (other) { return labelsIntersect(rect, other); })) {
                    rect.top -= step;
                    rect.bottom -= step;
                    shifts++;
                }

                if (shifts > 0) {
                    item.pill.style.setProperty('--mk-offset', (shifts * step) + 'px');
                    if (item.line) item.line.style.setProperty('--mk-line', (shifts * step + 6) + 'px');
                }

                placed.push(rect);
            });
        }

        function setActive(id) {
            activeId = id;

            markers.forEach(function (marker, markerId) {
                var element = marker.getElement();
                if (!element) return;

                var pill = element.querySelector('.price-marker-inner');
                if (pill) pill.classList.toggle('is-active', markerId === id);

                element.style.zIndex = markerId === id ? '1000' : '';
            });
        }

        function upsertMarker(land) {
            if (land.latitude === null || land.latitude === undefined ||
                land.longitude === null || land.longitude === undefined) {
                return;
            }

            var latlng = [Number(land.latitude), Number(land.longitude)];
            var marker = markers.get(land.id);

            if (marker) {
                marker.setLatLng(latlng);
                marker.setIcon(priceIcon(land));
                marker.setPopupContent(popupHtml(land));
                return;
            }

            marker = L.marker(latlng, {
                icon: priceIcon(land),
                riseOnHover: true
            }).addTo(map);

            marker.on('click', function () {
                window.dispatchEvent(new CustomEvent('land-marker-click', { detail: { id: land.id } }));
            });

            marker.bindPopup(popupHtml(land), {
                className: 'land-price-popup',
                closeButton: true,
                offset: [0, -6],
                maxWidth: 240,
                minWidth: 200
            });

            // Popup карт дээр дарахад дэлгэрэнгүй мэдээллийг нээнэ
            marker.on('popupopen', function (event) {
                var popupElement = event.popup.getElement();
                if (!popupElement) return;

                function openFrom(target) {
                    var card = target && target.closest ? target.closest('[data-land-open]') : null;
                    if (!card) return;

                    window.dispatchEvent(new CustomEvent('land-open-detail', { detail: { id: land.id } }));
                }

                if (popupElement.dataset.landBound !== '1') {
                    popupElement.dataset.landBound = '1';

                    popupElement.addEventListener('click', function (clickEvent) {
                        openFrom(clickEvent.target);
                    });

                    popupElement.addEventListener('keydown', function (keyEvent) {
                        if (keyEvent.key !== 'Enter' && keyEvent.key !== ' ') return;
                        if (!keyEvent.target.closest('[data-land-open]')) return;

                        keyEvent.preventDefault();
                        openFrom(keyEvent.target);
                    });
                }
            });

            markers.set(land.id, marker);
        }

        function sync(lands) {
            if (!map) return;

            var seen = new Set();

            (lands || []).forEach(function (land) {
                if (!land || land.id === null || land.id === undefined) return;
                seen.add(land.id);
                upsertMarker(land);
            });

            markers.forEach(function (marker, id) {
                if (seen.has(id)) return;
                map.removeLayer(marker);
                markers.delete(id);
            });

            setActive(activeId);
            layoutLabels();
        }

        function refresh(lands, active) {
            if (!ensureMap()) {
                // Зургийн хайрцаг хараахан харагдахгүй (0×0) байгаа тул
                // хэмжээтэй болмогц дахин зурах хүсэлтийг хадгална.
                pending = { lands: lands, active: active };
                scheduleRetry();
                return;
            }

            pending = null;
            sync(lands);

            if (active !== null && active !== undefined) {
                setActive(active);
            }

            if (focusId !== null) {
                focus(focusId);
            }

            applyMapSize();
        }

        // Газрын зургийн хайрцаг харагдах хүртэл богино хугацаанд дахин оролдоно
        function scheduleRetry() {
            if (retryTimer) return;

            retryTimer = setInterval(function () {
                if (!pending) {
                    clearInterval(retryTimer);
                    retryTimer = null;
                    return;
                }

                if (ensureMap()) {
                    clearInterval(retryTimer);
                    retryTimer = null;
                    refresh(pending.lands, pending.active);
                }
            }, 250);
        }

        function focus(id) {
            var marker = markers.get(id);

            if (!marker || !map) {
                // Зураг/маркер хараахан бэлэн болоогүй бол дараа нь төвлөрүүлнэ
                focusId = id;
                return;
            }

            focusId = null;
            map.flyTo(marker.getLatLng(), Math.max(map.getZoom(), 15), { duration: 0.6 });
            setActive(id);
        }

        function closePopup() {
            if (map) map.closePopup();
        }

        function getBounds() {
            if (!map) return null;
            var bounds = map.getBounds();

            return {
                south: bounds.getSouth(),
                west: bounds.getWest(),
                north: bounds.getNorth(),
                east: bounds.getEast()
            };
        }

        // Leaflet-ийн дотоод хэмжээ болон DOM-ын хэмжээ зөрүүтэй бол
        // (зураг далд байх үед үүссэн гэх мэт) төвийг зөв байрлуулна.
        function applyMapSize() {
            if (!map) return;

            var container = map.getContainer();
            var width = container.clientWidth;
            var height = container.clientHeight;
            var size = map.getSize();

            if (!width || !height) return;

            if (size.x !== width || size.y !== height) {
                map.invalidateSize({ pan: false, animate: false });
                map.setView(map.getCenter(), map.getZoom(), { animate: false, reset: true });
            }

            layoutLabels();
        }

        // Газрын зургийг зөвхөн харагдах (хэмжээтэй) үед үүсгэнэ.
        // Ингэснээр далд байх үед 0×0 хэмжээгээр үүсч, төв нь буруу
        // байрлах асуудал гарахгүй.
        function ensureMap() {
            if (map) return map;

            var container = document.getElementById('splitMap');

            if (!container || typeof L === 'undefined') return null;
            if (container._leaflet_id) return null;
            if (!container.clientWidth || !container.clientHeight) return null;

            map = L.map(container, {
                center: [47.9188, 106.9176],
                zoom: 12,
                zoomControl: true
            });

            // Хотын тэмдэглэгээний давхарга (үнийн маркеруудын доор харагдана)
            map.createPane('landmarkPane');
            var landmarkPane = map.getPane('landmarkPane');
            landmarkPane.style.zIndex = 380;
            landmarkPane.style.pointerEvents = 'none';

            // Хиймэл дагуулын зураг
            var satelliteLayer = L.tileLayer(
                'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
                { maxZoom: 19, attribution: 'Tiles &copy; Esri' }
            );

            // Энгийн газрын зураг
            var streetLayer = L.tileLayer(
                'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                { maxZoom: 19, attribution: '&copy; OpenStreetMap' }
            );

            satelliteLayer.addTo(map);

            map.on('zoomend moveend', function () {
                requestAnimationFrame(layoutLabels);
            });

            L.control.layers({
                'Хиймэл дагуул': satelliteLayer,
                'Энгийн зураг': streetLayer
            }, null, { position: 'topright' }).addTo(map);

            // Газрын зураг хэмжээ өөрчлөгдөхөд (view солих, цонх агшаах)
            if (typeof ResizeObserver !== 'undefined') {
                new ResizeObserver(function () {
                    requestAnimationFrame(function () {
                        if (container.isConnected && map) {
                            applyMapSize();
                        }
                    });
                }).observe(container);
            }

            buildLandmarks();

            window.splitMapInstance = map;
            window.dispatchEvent(new Event('split-map-ready'));

            return map;
        }

        // API-г хуудас ачаалагдмагц бүртгэнэ (зураг харагдах үед л үүснэ)
        window.splitMapApi = {
            refresh: refresh,
            sync: function (lands) {
                refresh(lands, activeId);
            },
            focus: focus,
            highlight: setActive,
            unhighlight: function (id) {
                if (activeId === id) setActive(null);
            },
            getBounds: getBounds,
            closePopup: closePopup,
            toggleLandmarks: toggleLandmarks
        };

        // Сэлгэгч товч
        (function wireLandmarkToggle() {
            var button = document.getElementById('landmarkToggle');

            if (button) {
                button.addEventListener('click', function () {
                    toggleLandmarks(!showLandmarks);
                });
            }

            syncLandmarkToggle();
        })();

        // Хайрцаг харагдах (0-ээс ялгаатай хэмжээтэй) болмогц зургийг үүсгэж,
        // хүлээгдэж буй заруудыг зурна. Гар утсан дээр вид солиход хэрэгтэй.
        (function watchMapContainer() {
            var container = document.getElementById('splitMap');

            if (!container || typeof ResizeObserver === 'undefined') return;

            new ResizeObserver(function () {
                requestAnimationFrame(function () {
                    if (!container.isConnected) return;
                    if (!container.clientWidth || !container.clientHeight) return;

                    if (!map) {
                        ensureMap();

                        if (map && pending) {
                            refresh(pending.lands, pending.active);
                        } else if (map) {
                            applyMapSize();
                        }

                        return;
                    }

                    applyMapSize();
                });
            }).observe(container);
        })();
    })();
</script>
@endpush

