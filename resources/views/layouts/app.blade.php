<!DOCTYPE html>
<html lang="mn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Газар.mn - Газрын Интерактив Зарын Сайт')</title>

    <!-- Tailwind CSS & FontAwesome -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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

    <!-- Alpine.js & Leaflet JS (Map) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Custom Style (Ribbon & CSS) -->
    <style>
        [x-cloak] { display: none !important; }
        .egazar-ribbon-wrapper {
            position: absolute;
            top: 0;
            right: 0;
            width: 110px;
            height: 110px;
            overflow: hidden;
            z-index: 20;
            pointer-events: none;
        }
        .egazar-ribbon {
            position: absolute;
            top: 22px;
            right: -32px;
            transform: rotate(45deg);
            width: 140px;
            background: linear-gradient(135deg, #059669 0%, #0d9488 100%);
            color: #ffffff;
            font-size: 9px;
            font-weight: 800;
            text-align: center;
            padding: 4px 0;
            letter-spacing: 0.5px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.25);
            border-top: 1px solid rgba(255,255,255,0.3);
            border-bottom: 1px solid rgba(0,0,0,0.2);
            text-transform: uppercase;
        }

        /* Split view: газрын зургийн үнийн маркер */
        .price-marker { background: transparent; border: 0; overflow: visible; }
        .price-marker-inner {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transform: translate(-50%, calc(-100% - 12px - var(--mk-offset, 0px)));
            white-space: nowrap;
            background: #059669;
            color: #ffffff;
            border: 2px solid #ffffff;
            border-radius: 14px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 800;
            line-height: 1;
            letter-spacing: .2px;
            font-variant-numeric: tabular-nums;
            box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.55), 0 8px 18px rgba(0, 0, 0, 0.45);
            transition: background-color .15s ease, transform .15s ease, box-shadow .15s ease;
            cursor: pointer;
        }
        .price-marker-inner::before {
            content: '';
            position: absolute;
            left: 50%;
            top: 100%;
            transform: translate(-50%, -1px);
            border: 8px solid transparent;
            border-top-color: #ffffff;
        }
        .price-marker-inner::after {
            content: '';
            position: absolute;
            left: 50%;
            top: 100%;
            transform: translate(-50%, -3px);
            border: 6px solid transparent;
            border-top-color: #059669;
        }
        .price-marker-inner:hover {
            background: #047857;
            transform: translate(-50%, calc(-100% - 12px - var(--mk-offset, 0px))) scale(1.06);
            box-shadow: 0 0 0 2px rgba(4, 120, 87, 0.6), 0 10px 22px rgba(0, 0, 0, 0.5);
        }
        .price-marker-inner:hover::after {
            border-top-color: #047857;
        }
        /* Түрээсийн зар: цэнхэр маркер */
        .price-marker-rent .price-marker-inner {
            background: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.55), 0 8px 18px rgba(0, 0, 0, 0.45);
        }
        .price-marker-rent .price-marker-inner::after {
            border-top-color: #2563eb;
        }
        .price-marker-rent .price-marker-inner:hover {
            background: #1d4ed8;
        }
        .price-marker-rent .price-marker-inner:hover::after {
            border-top-color: #1d4ed8;
        }
        .price-marker-inner.is-active {
            background: #0f172a;
            transform: translate(-50%, calc(-100% - 12px - var(--mk-offset, 0px))) scale(1.1);
            box-shadow: 0 0 0 2px rgba(15, 23, 42, 0.55), 0 12px 26px rgba(0, 0, 0, 0.55);
        }
        .price-marker-inner.is-active::before {
            border-top-color: #ffffff;
        }
        .price-marker-inner.is-active::after {
            border-top-color: #0f172a;
        }

        /* Давхцсан маркеруудыг дээш нь зөрүүлэх шугам */
        .price-marker-line {
            position: absolute;
            left: 50%;
            bottom: 0;
            width: 2px;
            height: var(--mk-line, 0px);
            transform: translateX(-50%);
            background: #ffffff;
            box-shadow: 0 0 0 1px rgba(5, 150, 105, 0.7);
            border-radius: 2px;
            pointer-events: none;
        }

        /* Marker-ийн доорх цэг (anchor) */
        .price-marker-anchor {
            position: absolute;
            left: 50%;
            top: 100%;
            width: 8px;
            height: 8px;
            transform: translate(-50%, -50%);
            border-radius: 9999px;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.6);
        }

        /* Газрын зураг дээрх томоохон газрын тэмдэглэгээ (ориентир) */
        .landmark-marker { background: transparent; border: 0; overflow: visible; pointer-events: none; }
        .landmark-marker-inner {
            position: absolute;
            left: 0;
            top: 0;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transform: translate(-50%, -100%);
            white-space: nowrap;
            background: rgba(255, 255, 255, 0.94);
            color: #1e293b;
            border: 1px solid rgba(148, 163, 184, 0.85);
            border-radius: 10px;
            padding: 3px 8px;
            font-size: 10px;
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: .1px;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.18);
            backdrop-filter: blur(4px);
        }
        .landmark-marker-inner::after {
            content: '';
            position: absolute;
            left: 50%;
            top: 100%;
            transform: translate(-50%, -1px);
            border: 5px solid transparent;
            border-top-color: rgba(148, 163, 184, 0.9);
        }
        .landmark-dot {
            width: 6px;
            height: 6px;
            border-radius: 9999px;
            background: #0284c7;
            box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.22);
            flex-shrink: 0;
        }
        .landmark-marker.is-major .landmark-marker-inner {
            background: rgba(15, 23, 42, 0.92);
            color: #ffffff;
            border-color: rgba(15, 23, 42, 0.9);
        }
        .landmark-marker.is-major .landmark-marker-inner::after {
            border-top-color: rgba(15, 23, 42, 0.92);
        }
        .landmark-marker.is-major .landmark-dot {
            background: #fbbf24;
            box-shadow: 0 0 0 2px rgba(251, 191, 36, 0.28);
        }

        /* Газрын зураг дээр харагддах үнэний popup */
        .land-price-popup .leaflet-popup-content-wrapper {
            padding: 0;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 18px 40px -18px rgba(15, 23, 42, 0.45), 0 0 0 1px rgba(15, 23, 42, 0.05);
        }
        .land-price-popup .leaflet-popup-content {
            margin: 0;
            width: 220px !important;
        }
        .land-price-popup .leaflet-popup-tip {
            box-shadow: none;
        }
        .land-price-popup .leaflet-popup-close-button {
            top: 7px;
            right: 7px;
            width: 22px;
            height: 22px;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.92);
            color: #334155;
            font-size: 16px;
            line-height: 20px;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.25);
        }
        .land-price-popup .leaflet-popup-close-button:hover {
            background: #ffffff;
            color: #0f172a;
        }
        .price-popup { font-family: inherit; width: 220px; cursor: pointer; }
        .price-popup-media {
            display: block;
            width: 100%;
            height: 112px;
            background: linear-gradient(135deg, #e2e8f0, #f1f5f9);
            overflow: hidden;
        }
        .price-popup-img {
            display: block;
            width: 100%;
            height: 112px;
            object-fit: cover;
            transition: transform 0.25s ease;
        }
        .price-popup:hover .price-popup-img {
            transform: scale(1.04);
        }
        .price-popup-img-empty {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            color: #94a3b8;
            background: linear-gradient(135deg, #e2e8f0, #f1f5f9);
        }
        .price-popup-body { padding: 10px 12px 12px; }
        .price-popup-title {
            font-size: 12.5px;
            font-weight: 700;
            line-height: 1.35;
            color: #0f172a;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .price-popup-price {
            font-size: 15px;
            font-weight: 800;
            color: #059669;
            font-variant-numeric: tabular-nums;
            margin-top: 6px;
            line-height: 1.2;
        }
        .price-popup-meta {
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
            margin-top: 3px;
        }
        .price-popup.is-rent .price-popup-price { color: #2563eb; }

        .hide-scrollbar { scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent; }
        .hide-scrollbar::-webkit-scrollbar { width: 6px; }
        .hide-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        .hide-scrollbar::-webkit-scrollbar-track { background: transparent; }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-100 font-sans text-gray-800 antialiased min-h-screen flex flex-col" x-data="landApp()">

    <!-- Header Include -->
    @include('components.header')

    <!-- Main Content -->
    <main class="flex-grow max-w-7xl w-full mx-auto px-4 py-4 sm:px-6 lg:px-8">
        @yield('content')
    </main>

    <!-- Footer Include -->
    @include('components.footer')

    <!-- Modals Include -->
    @include('components.modal-create')
    @include('components.modal-detail')

    @stack('scripts')

    <script>
        function landApp() {
            return {
                // Эхлээд карт жагсаалт; гар утсан дээр init() дотор split болгож солино
                viewMode: 'grid',
                openCreateModal: {{ $errors->any() ? 'true' : 'false' }},
                openDetail: false,
                selectedLand: null,
                detailImageIndex: 0,
                // Үнийн санал илгээх форм
                offerOpen: false,
                offerPrice: '',
                offerPhone: '',
                offerMessage: '',
                offerSubmitting: false,
                offerSent: '',
                offerError: '',
                activeLandId: null,
                searchQuery: '',
                mapBounds: null,
                filterByMapArea: false,
                newParcelNo: @json(old('cadastral_code', '')),
                newDealType: @json(old('deal_type', 'sale')),
                newRightsType: @json(old('rights_type', 'Өмчлөх')),
                newTemplate: '',
                templateTouched: false,
                // Хэрэглэгчийн өөрөө нэр өгч хадгалсан загварууд (localStorage)
                savedTemplates: [],
                templateName: '',
                templateMessage: '',
                templateMessageOk: true,
                newTitle: '',
                newDescription: '',
                newPrice: '',
                newSizeHa: '',
                // Дэд бүтцийн нөхцөл (загвараас автоматаар бөглөгдөнө)
                newHasElectricity: @json(old('has_electricity') === '1'),
                newHasWater: @json(old('has_water') === '1'),
                newHasFence: @json(old('has_fence') === '1'),
                newNearRoad: @json(old('near_road') === '1'),
                newIsSwamp: @json(old('is_swamp') === '1'),
                newIsSloped: @json(old('is_sloped') === '1'),
                parcelMessage: '',
                parcelOk: false,
                placeholderImage: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(
                    '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400">' +
                    '<rect width="400" height="400" fill="#e2e8f0"/>' +
                    '<path d="M90 300l80-110 60 70 45-55 55 95z" fill="#cbd5e1"/>' +
                    '<circle cx="135" cy="115" r="24" fill="#cbd5e1"/>' +
                    '<text x="200" y="345" text-anchor="middle" font-family="sans-serif" font-size="18" fill="#94a3b8">Зураггүй</text>' +
                    '</svg>'
                ),
                filters: {
                    dealType: 'all',
                    district: '',
                    purpose: '',
                    rightsType: '',
                    maxPrice: '',
                    minSize: '',
                    egazarVerified: false,
                    electricity: false,
                    water: false,
                    road: false,
                    fence: false
                },
                // "Зориулалт" сонголтод тохирох түлхүүр үгс (гарчиг/тайлбар дотроос хайна)
                purposeKeywords: {
                    'Амины сууц': ['сууц', 'байр', 'гэр', 'амьдрах'],
                    'Хүлэмж': ['хүлэмж', 'нөөш', 'теплиц'],
                    'Үйлдвэрлэл': ['үйлдвэр', 'агуулах', 'цех', 'завод'],
                    'Амралтын бүс': ['амралт', 'рекреац']
                },
                // Контроллер дээрээс дамжуулсан датаг энд авна
                lands: @json($lands ?? []),

                init() {
                    // Гар утас/таблет дээр газрын зургийг эхлээд харуулна
                    if (window.matchMedia && window.matchMedia('(max-width: 1023px)').matches) {
                        this.viewMode = 'split';
                    }

                    this.syncMapWhenReady();
                    this.loadLandTemplates();

                    window.addEventListener('land-marker-click', (event) => {
                        const id = event.detail ? event.detail.id : null;
                        if (id === null || id === undefined) return;

                        this.activeLandId = id;
                        this.$nextTick(() => {
                            const card = document.getElementById('card-' + id);
                            if (card) card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        });
                    });

                    // Үнийн тэмдэглэгээний popup дээрх товчоор дэлгэрэнгүй модал нээнэ
                    window.addEventListener('land-open-detail', (event) => {
                        const id = event.detail ? event.detail.id : null;
                        if (id === null || id === undefined) return;

                        const land = this.lands.find((item) => String(item.id) === String(id));
                        if (!land) return;

                        if (window.splitMapApi && typeof window.splitMapApi.closePopup === 'function') {
                            window.splitMapApi.closePopup();
                        }

                        this.openDetailModal(land);
                    });

                    this.$watch('viewMode', (mode) => {
                        if (mode === 'split' || mode === 'map') {
                            this.$nextTick(() => this.syncMap());
                        }
                    });

                    // Шүүлтүүр, хайлт өөрчлөгдөхөд газрын зургийн маркеруудыг шинэчилнэ
                    this.$watch('filters', () => {
                        this.$nextTick(() => this.syncMap());
                    }, { deep: true });

                    this.$watch('searchQuery', () => {
                        this.$nextTick(() => this.syncMap());
                    });

                    this.$watch('openCreateModal', (open) => {
                        if (open) {
                            this.$nextTick(() => window.dispatchEvent(new Event('resize')));

                            // Анх нээхэд сонгохгүй бол template-г дээд поинтом ашиглаж,
                            // Дэлгэрэнгүй мэдээлэл / Тайлбар хэсэгт товчлолт байхгүй бол.
                            if (!this.templateTouched) {
                                this.newTemplate = 'sale';
                                this.applyLandTemplate('sale');
                            }
                        }
                    });

                    this.$watch('newParcelNo', () => {
                        this.parcelMessage = '';
                        this.parcelOk = false;
                    });
                },

                get filteredLands() {
                    const f = this.filters;
                    const search = (this.searchQuery || '').trim().toLowerCase();
                    const maxPrice = f.maxPrice ? Number(f.maxPrice) : null;
                    const minSize = f.minSize ? Number(f.minSize) : null;
                    const purposeWords = f.purpose ? (this.purposeKeywords[f.purpose] || []) : [];

                    return this.lands.filter((land) => {
                        /* ---------- Газрын зураг дээрх бүсээр шүүх ---------- */
                        if (this.filterByMapArea && this.mapBounds && !this.isInsideBounds(land)) {
                            return false;
                        }

                        /* ---------- Үгээр хайх ---------- */
                        if (search) {
                            const haystack = [land.title, land.district, land.location, land.cadastral_code, land.description]
                                .join(' ')
                                .toLowerCase();

                            if (!haystack.includes(search)) {
                                return false;
                            }
                        }

                        /* ---------- Зарын төрөл (Зарах / Түрээслэх) ---------- */
                        if (f.dealType && f.dealType !== 'all') {
                            const dealType = land.deal_type === 'rent' ? 'rent' : 'sale';

                            if (dealType !== f.dealType) {
                                return false;
                            }
                        }

                        /* ---------- Байршил / Дүүрэг ---------- */                        if (f.district && String(land.district || '').trim().toLowerCase() !== f.district.trim().toLowerCase()) {
                            return false;
                        }

                        /* ---------- Зориулалт ---------- */
                        if (f.purpose) {
                            const text = [land.title, land.description].join(' ').toLowerCase();
                            const matches = purposeWords.some((word) => text.includes(word));

                            if (!matches) {
                                return false;
                            }
                        }

                        /* ---------- Эрхийн төрөл ---------- */
                        if (f.rightsType && String(land.rights_type || '') !== f.rightsType) {
                            return false;
                        }

                        /* ---------- Дээд үнэ ---------- */
                        if (maxPrice !== null && !(Number(land.price) <= maxPrice)) {
                            return false;
                        }

                        /* ---------- Доод хэмжээ (га) ---------- */
                        if (minSize !== null && !(Number(land.size_ha) >= minSize)) {
                            return false;
                        }

                        /* ---------- Дэд бүтцийн чекбоссууд ---------- */
                        if (f.egazarVerified && !land.is_egazar_verified) {
                            return false;
                        }

                        if (f.electricity && !land.has_electricity) {
                            return false;
                        }

                        if (f.water && !land.has_water) {
                            return false;
                        }

                        if (f.road && !land.near_road) {
                            return false;
                        }

                        if (f.fence && !land.has_fence) {
                            return false;
                        }

                        return true;
                    });
                },

                get activeFilterCount() {
                    const f = this.filters;
                    let count = 0;

                    if (f.dealType && f.dealType !== 'all') count++;

                    ['district', 'purpose', 'rightsType', 'maxPrice', 'minSize'].forEach((key) => {
                        if (f[key]) count++;
                    });

                    ['egazarVerified', 'electricity', 'water', 'road', 'fence'].forEach((key) => {
                        if (f[key]) count++;
                    });

                    if (this.searchQuery && this.searchQuery.trim()) count++;

                    return count;
                },

                /* ---------- Туслах функцууд ---------- */

                formatPrice(value) {
                    return Number(value || 0).toLocaleString('en-US');
                },

                // Түрээсийн зар бол сарын төлбөр гэдгийг илэрхийлнэ
                isRent(land) {
                    return !!land && land.deal_type === 'rent';
                },

                dealLabel(land) {
                    return this.isRent(land) ? 'Түрээслэх' : 'Зарах';
                },

                priceUnit(land, withSlash = true) {
                    return this.isRent(land) ? (withSlash ? '₮/сар' : '₮') : '₮';
                },

                landSize(land) {
                    const ha = Number(land.size_ha || 0);
                    const m2 = Number(land.size_m2 || Math.round(ha * 10000));

                    return ha.toLocaleString('en-US') + ' га (' + m2.toLocaleString('en-US') + ' м²)';
                },

                landImage(land) {
                    const images = this.landImages(land);
                    return images.length ? images[0] : this.placeholderImage;
                },

                landImages(land) {
                    let images = land ? land.images : null;

                    if (typeof images === 'string') {
                        try {
                            const parsed = JSON.parse(images);
                            images = Array.isArray(parsed) ? parsed : [images];
                        } catch (error) {
                            images = [images];
                        }
                    }

                    if (!Array.isArray(images)) return [];

                    return images
                        .filter((image) => typeof image === 'string' && image.trim() !== '')
                        .map((image) => {
                            const path = image.trim();
                            if (/^(https?:)?\/\//i.test(path) || path.startsWith('data:')) return path;
                            if (path.startsWith('/')) return path;

                            return '/storage/' + path.replace(/^storage\//, '');
                        });
                },

                /* ---------- Газрын зурагтай холбоо ---------- */

                syncMapWhenReady() {
                    if (window.splitMapApi) {
                        this.syncMap();
                        return;
                    }

                    window.addEventListener('split-map-ready', () => this.syncMap(), { once: true });
                },

                syncMap() {
                    if (!window.splitMapApi) return;
                    window.splitMapApi.refresh(this.filteredLands, this.activeLandId);
                },

                highlightLand(id) {
                    this.activeLandId = id;
                    if (window.splitMapApi) window.splitMapApi.highlight(id);
                },

                unhighlightLand(id) {
                    if (this.activeLandId === id) {
                        this.activeLandId = null;
                    }
                    if (window.splitMapApi) window.splitMapApi.unhighlight(id);
                },

                zoomToLand(land) {
                    this.activeLandId = land.id;
                    if (window.splitMapApi) window.splitMapApi.focus(land.id);
                },

                isInsideBounds(land) {
                    if (!this.mapBounds || land.latitude == null || land.longitude == null) return false;

                    const lat = Number(land.latitude);
                    const lng = Number(land.longitude);
                    const b = this.mapBounds;

                    return lat >= b.south && lat <= b.north && lng >= b.west && lng <= b.east;
                },

                searchInCurrentMapArea() {
                    const bounds = window.splitMapApi ? window.splitMapApi.getBounds() : null;
                    if (!bounds) return;

                    this.mapBounds = bounds;
                    this.filterByMapArea = true;
                    this.$nextTick(() => this.syncMap());
                },

                clearMapAreaFilter() {
                    this.mapBounds = null;
                    this.filterByMapArea = false;
                    this.$nextTick(() => this.syncMap());
                },

                /* ---------- Зарын шаблон ашиглах ---------- */

                templateStorageKey() {
                    return 'gazar_land_templates_v1';
                },

                loadLandTemplates() {
                    try {
                        const raw = window.localStorage.getItem(this.templateStorageKey());
                        const parsed = raw ? JSON.parse(raw) : [];

                        this.savedTemplates = Array.isArray(parsed) ? parsed : [];
                    } catch (error) {
                        this.savedTemplates = [];
                    }
                },

                persistLandTemplates() {
                    try {
                        window.localStorage.setItem(this.templateStorageKey(), JSON.stringify(this.savedTemplates));
                    } catch (error) {
                        // localStorage боломжгүй үед ч маягт ажиллана
                    }
                },

                setAmenities(amenities) {
                    const a = amenities || {};

                    this.newHasElectricity = !!a.electricity;
                    this.newHasWater = !!a.water;
                    this.newHasFence = !!a.fence;
                    this.newNearRoad = !!a.road;
                    this.newIsSwamp = !!a.swamp;
                    this.newIsSloped = !!a.sloped;
                },

                applyLandTemplate(template) {
                    // Загвар сонгоход зарын төрөл, эрхийн төрөл, гарчиг,
                    // тайлбар болон дэд бүтцийн нөхцөл автоматаар бөглөгдөнө.

                    this.templateTouched = true;

                    // 1. Хоосон — талбаруудыг цэвэрлэнэ
                    if (template === '' || template === null || template === undefined) {
                        this.newDealType = 'sale';
                        this.newRightsType = 'Өмчлөх';
                        this.newTitle = '';
                        this.newDescription = '';
                        this.setAmenities({});

                        return;
                    }

                    // Хэрэглэгчийн хадгалсан загвар
                    if (typeof template === 'string' && template.indexOf('custom:') === 0) {
                        this.applySavedTemplate(template.substring('custom:'.length));

                        return;
                    }

                    if (template === 'rent') {
                        this.newDealType = 'rent';
                        this.newRightsType = 'Эзэмших';
                        this.setAmenities({
                            electricity: true,
                            water: true,
                            fence: true,
                            road: true
                        });
                        this.newTitle = '0,7 той газар түрээслэнэ';
                        this.newDescription = [
                            'Өмчлөлийн гэрчилгээтэй',
                            '360 тогтой',
                            'Түрээс сар сардаа',
                            'Орц гарц сайтай, өргөн гудамжтай',
                            'Тэгш газартай',
                            '0,7 хэмжээтэй',
                            'Засмалаас 500м',
                            'Улсын тогтой',
                            'Төв замтай ойрхон',
                            'Агуулахаар ашиглах болон үйлдвэрлэлийн барилга барих боломжтой.',
                            'Удахгүй нарны замын өргөтгөл зам хажуугаар нь тавигдана.'
                        ].join('\n');

                        return;
                    }

                    this.newDealType = 'sale';
                    this.newRightsType = 'Өмчлөх';
                    this.setAmenities({
                        electricity: true,
                        water: true,
                        fence: true,
                        road: true
                    });
                    this.newTitle = '0,7 той газар зарна';
                    this.newDescription = [
                        'Өмчлөлийн гэрчилгээтэй',
                        'Төвийн бохир, цэвэрийн шугам яг урд талдаа явдаг',
                        'Орц гарц сайтай, өргөн гудамжтай',
                        'Тэгш газартай',
                        '0,7 хэмжээтэй',
                        'Төв замтай ойрхон',
                        'Агуулахаар ашиглах болон үйлдвэрлэлийн барилга барих боломжтой.',
                        'Удахгүй нарны замын өргөтгөл зам хажуугаар нь тавигдана.',
                        'Бэлэн төлөл болон бартер хийнэ зарна.'
                    ].join('\n');
                },

                applySavedTemplate(id) {
                    const tpl = this.savedTemplates.find((item) => String(item.id) === String(id));
                    if (!tpl) return;

                    this.templateTouched = true;
                    this.newTemplate = 'custom:' + tpl.id;
                    this.newDealType = tpl.dealType === 'rent' ? 'rent' : 'sale';
                    this.newRightsType = tpl.rightsType || 'Өмчлөх';
                    this.newTitle = tpl.title || '';
                    this.newDescription = tpl.description || '';
                    this.setAmenities(tpl.amenities);
                },

                saveLandTemplate() {
                    const name = (this.templateName || '').trim();

                    if (!name) {
                        this.templateMessageOk = false;
                        this.templateMessage = 'Загварын нэр оруулна уу.';
                        return;
                    }

                    const template = {
                        id: 't' + Date.now(),
                        name: name,
                        dealType: this.newDealType === 'rent' ? 'rent' : 'sale',
                        rightsType: this.newRightsType || 'Өмчлөх',
                        title: this.newTitle || '',
                        description: this.newDescription || '',
                        amenities: {
                            electricity: !!this.newHasElectricity,
                            water: !!this.newHasWater,
                            fence: !!this.newHasFence,
                            road: !!this.newNearRoad,
                            swamp: !!this.newIsSwamp,
                            sloped: !!this.newIsSloped
                        },
                        createdAt: new Date().toISOString()
                    };

                    this.savedTemplates.push(template);
                    this.persistLandTemplates();

                    this.newTemplate = 'custom:' + template.id;
                    this.templateName = '';
                    this.templateMessageOk = true;
                    this.templateMessage = '«' + name + '» нэрээр загвар хадгалагдлаа.';
                },

                deleteLandTemplate(id) {
                    this.savedTemplates = this.savedTemplates.filter((item) => String(item.id) !== String(id));
                    this.persistLandTemplates();

                    if (this.newTemplate === 'custom:' + id) {
                        this.newTemplate = '';
                    }

                    this.templateMessageOk = false;
                    this.templateMessage = 'Загвар устгагдлаа.';
                },

                /* ---------- Модал / шүүлтүүр ---------- */

                openModal(land) {
                    this.openDetailModal(land);
                },

                verifyParcel() {
                    const value = (this.newParcelNo || '').trim();
                    const digits = value.replace(/\D/g, '');

                    if (!value) {
                        this.parcelOk = false;
                        this.parcelMessage = 'Нэгж талбарын дугаараа оруулна уу.';
                        return;
                    }

                    if (digits.length < 6) {
                        this.parcelOk = false;
                        this.parcelMessage = 'Дугаар хэт богино байна. Кадастрын дугаараа бүтэн оруулна уу.';
                        return;
                    }

                    this.parcelOk = true;
                    this.parcelMessage = 'Дугаарын формат зөв байна. Зар нийтлэгдсэний дараа egazar.mn холбоосоор баталгаажна.';
                },

                openDetailModal(land) {
                    this.selectedLand = land;
                    this.detailImageIndex = 0;
                    this.openDetail = true;
                    this.offerOpen = false;
                    this.offerPrice = '';
                    this.offerPhone = '';
                    this.offerMessage = '';
                    this.offerSubmitting = false;
                    this.offerSent = '';
                    this.offerError = '';

                    this.trackLandView(land);
                },

                /* ---------- Үзсэн тоо ---------- */

                landViews(land) {
                    return Number(land && land.views_count ? land.views_count : 0).toLocaleString('en-US');
                },

                async trackLandView(land) {
                    if (!land || !land.id) return;

                    try {
                        const response = await fetch('/lands/' + land.id + '/view', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken()
                            }
                        });

                        if (!response.ok) return;

                        const data = await response.json();
                        if (data && typeof data.views_count === 'number') {
                            land.views_count = data.views_count;
                        }
                    } catch (error) {
                        // Тоолох үед алдаа гарвал хэрэглэгчид саад болохгүй
                    }
                },

                /* ---------- Үнийн санал илгээх ---------- */

                csrfToken() {
                    const meta = document.querySelector('meta[name="csrf-token"]');
                    return meta ? meta.getAttribute('content') : '';
                },

                toggleOfferForm() {
                    this.offerOpen = !this.offerOpen;
                    this.offerError = '';
                    this.offerSent = '';
                },

                async submitOffer() {
                    if (!this.selectedLand || this.offerSubmitting) return;

                    this.offerSubmitting = true;
                    this.offerError = '';
                    this.offerSent = '';

                    try {
                        const response = await fetch('/lands/' + this.selectedLand.id + '/offers', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken()
                            },
                            body: JSON.stringify({
                                price: this.offerPrice,
                                phone: this.offerPhone,
                                message: this.offerMessage
                            })
                        });

                        const data = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            this.offerError = (data && data.message)
                                ? data.message
                                : 'Үнийн санал илгээхэд алдаа гарлаа. Дахин оролдоно уу.';
                            return;
                        }

                        this.offerSent = (data && data.message)
                            ? data.message
                            : 'Таны үнийн санал илгээгдлээ.';
                        this.offerOpen = false;
                        this.offerPrice = '';
                        this.offerMessage = '';
                    } catch (error) {
                        this.offerError = 'Сүлжээний алдаа гарлаа. Дахин оролдоно уу.';
                    } finally {
                        this.offerSubmitting = false;
                    }
                },

                get detailImages() {
                    const images = this.landImages(this.selectedLand);
                    return images.length ? images : [this.placeholderImage];
                },

                get detailImage() {
                    return this.detailImages[this.detailImageIndex] || this.detailImages[0];
                },

                /* ---------- Дэлгэрэнгүй модал ---------- */

                showDetailImage(index) {
                    if (index >= 0 && index < this.detailImages.length) {
                        this.detailImageIndex = index;
                    }
                },

                stepDetailImage(step) {
                    const count = this.detailImages.length;
                    if (!count) return;
                    this.detailImageIndex = (this.detailImageIndex + step + count) % count;
                },

                pricePerM2(land) {
                    const price = Number(land && land.price ? land.price : 0);
                    const m2 = Number(land && land.size_m2 ? land.size_m2 : 0);
                    if (!price || !m2) return null;

                    return Math.round(price / m2);
                },

                showLandOnMap(land) {
                    this.openDetail = false;
                    this.viewMode = 'split';
                    this.activeLandId = land.id;

                    this.$nextTick(() => {
                        this.syncMap();
                        if (window.splitMapApi) window.splitMapApi.focus(land.id);
                    });
                },

                resetFilters() {
                    this.filters = {
                        dealType: 'all',
                        district: '',
                        purpose: '',
                        rightsType: '',
                        maxPrice: '',
                        minSize: '',
                        egazarVerified: false,
                        electricity: false,
                        water: false,
                        road: false,
                        fence: false
                    };
                    this.searchQuery = '';
                    this.clearMapAreaFilter();
                }
            }
        }
    </script>
</body>
</html>
