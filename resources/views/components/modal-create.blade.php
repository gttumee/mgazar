<!-- resources/views/components/modal-create.blade.php -->
<div x-show="openCreateModal" 
     class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
     x-cloak>
    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl relative max-h-[90vh] overflow-y-auto" @click.away="openCreateModal = false">
        
        <!-- Гарчиг & Хаах товч -->
        <div class="flex items-center justify-between mb-4 border-b pb-3">
            <h3 class="text-lg font-black text-gray-900 flex items-center gap-2">
                <i class="fa-solid fa-plus-circle text-emerald-600"></i> Шинэ газрын зар оруулах
            </h3>
            <button @click="openCreateModal = false" class="w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-full flex items-center justify-center text-gray-500 transition">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

      <form action="{{ route('lands.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
    @csrf

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-2xl p-3.5">
            <p class="font-black text-red-800 flex items-center gap-1.5 mb-1.5">
                <i class="fa-solid fa-circle-exclamation"></i> Зарыг оруулахад алдаа гарлаа
            </p>
            <ul class="list-disc list-inside space-y-0.5 text-red-700 font-semibold">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Нэгж талбарын дугаар (Кадастр) -->
    <div class="bg-emerald-50/50 p-3.5 rounded-2xl border border-emerald-200">
        <label class="block font-bold text-emerald-900 mb-1">
            Нэгж талбарын дугаар (Кадастр)
            <span class="ml-1 text-[10px] font-bold text-gray-500 bg-white border border-gray-200 rounded-full px-2 py-0.5">заавал биш</span>
        </label>
        <div class="flex gap-2">
            <input type="text" 
                   name="cadastral_code"
                   x-model="newParcelNo" 
                   placeholder="Жишээ: 1680710776" 
                   class="flex-1 bg-white border border-gray-300 rounded-xl p-2.5 focus:outline-none focus:border-emerald-500 font-mono font-bold text-gray-800 shadow-sm">
            <button type="button" 
                    @click="verifyParcel()" 
                    :disabled="parcelChecking"
                    :class="parcelChecking ? 'opacity-70 cursor-wait' : ''"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 py-2 rounded-xl transition flex items-center gap-1.5 shadow-md">
                <i class="fa-solid" :class="parcelChecking ? 'fa-spinner fa-spin' : 'fa-magnifying-glass'"></i>
                <span x-text="parcelChecking ? 'Шалгаж байна' : 'Шалгах'"></span>
            </button>
        </div>
        <p class="mt-1.5 text-[10px] text-emerald-800/70 font-semibold">
            Кадастрын дугааргүй газар байвал хоосон орхино уу. Дугаар оруулбал egazar.gov.mn-ээс шалгаж, талбай болон байршлыг автоматаар бөглөнө.
        </p>

        <template x-if="parcelMessage">
            <p class="mt-2 flex items-start gap-1.5 font-semibold text-[11px]"
               :class="parcelOk ? 'text-emerald-700' : 'text-red-600'">
                <i class="fa-solid mt-0.5" :class="parcelOk ? 'fa-circle-check' : 'fa-circle-exclamation'"></i>
                <span x-text="parcelMessage"></span>
            </p>
        </template>

        <!-- egazar.gov.mn-ээс ирсэн нэгж талбарын мэдээлэл -->
        <template x-if="parcelDetails">
            <div class="mt-2 rounded-xl bg-white border border-emerald-200 p-2.5 text-[11px] text-gray-700 space-y-1.5 shadow-sm">
                <div class="flex items-center justify-between gap-2">
                    <span class="font-bold text-emerald-800">
                        <i class="fa-solid fa-shield-halved mr-1"></i> egazar.gov.mn мэдээлэл
                    </span>
                    <a :href="parcelDetails.egazar_url" target="_blank" rel="noopener"
                       class="font-bold text-emerald-700 hover:underline whitespace-nowrap">
                        egazar.mn харах <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                    </a>
                </div>
                <div class="grid grid-cols-2 gap-x-3 gap-y-1">
                    <p><span class="text-gray-500">Гэрчилгээ:</span>
                        <span class="font-semibold" x-text="parcelDetails.property_no || '—'"></span></p>
                    <p><span class="text-gray-500">Талбай:</span>
                        <span class="font-semibold" x-text="parcelDetails.area_label || '—'"></span></p>
                    <p class="col-span-2"><span class="text-gray-500">Хаяг:</span>
                        <span class="font-semibold" x-text="parcelDetails.address_label || '—'"></span></p>
                    <p class="col-span-2"><span class="text-gray-500">Зориулалт:</span>
                        <span class="font-semibold" x-text="parcelDetails.landuse || '—'"></span></p>
                </div>
                <p x-show="parcelDetails.latitude" class="text-emerald-700 font-semibold">
                    <i class="fa-solid fa-location-dot mr-1"></i> Байршил газрын зураг дээр автоматаар тэмдэглэгдлээ.
                </p>
            </div>
        </template>
    </div>

    <!-- Зарын шаблон (Ашиглах) -->
    <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-200 space-y-2">
        <label class="block font-bold text-gray-700">Зарын загвар ашиглах</label>
        <select x-model="newTemplate"
                @change="applyLandTemplate(newTemplate)"
                class="w-full bg-white border border-gray-200 rounded-xl p-3 focus:outline-none focus:border-emerald-500 font-semibold text-gray-700">
            <option value="">1. Хоосон</option>
            <option value="sale">2. Зарах бэлэн загвар</option>
            <option value="rent">3. Түрээслэх бэлэн загвар</option>
            <template x-for="tpl in savedTemplates" :key="'opt-' + tpl.id">
                <option :value="'custom:' + tpl.id" x-text="'★ ' + tpl.name"></option>
            </template>
        </select>
        <p class="text-[10px] text-gray-500 font-semibold">
            Загвар сонгоход зарын төрөл, эрхийн төрөл, гарчиг, тайлбар болон дэд бүтцийн нөхцөл
            автоматаар бөглөгдөнө. Сонгохгүй бол «Зарах бэлэн загвар» автоматаар хэрэглэгдэнэ.
        </p>

        <!-- Зар хадгалах: одоогийн маягтыг нэр өгч хадгална -->
        <div class="border-t border-slate-200 pt-2.5 space-y-2">
            <label class="block font-bold text-gray-700">Зар хадгалах</label>
            <div class="flex gap-2">
                <input type="text"
                       x-model="templateName"
                       @keydown.enter.prevent="saveLandTemplate()"
                       placeholder="Загварын нэр (жишээ: Хан-Уул 0,7 га)"
                       class="flex-1 bg-white border border-gray-300 rounded-xl p-2.5 focus:outline-none focus:border-emerald-500 font-semibold text-gray-800">
                <button type="button"
                        @click="saveLandTemplate()"
                        class="bg-slate-800 hover:bg-slate-900 text-white font-bold px-4 py-2 rounded-xl transition flex items-center gap-1.5 shadow-md">
                    <i class="fa-solid fa-floppy-disk"></i> Хадгалах
                </button>
            </div>
            <p class="text-[10px] text-gray-400 font-semibold">
                Одоогийн зарын мэдээллийг нэр өгч хадгалаад, дараа нь дээрх жагсаалтаас сонгож ашиглана.
            </p>
            <p x-show="templateMessage" x-cloak x-text="templateMessage"
               class="text-[11px] font-bold"
               :class="templateMessageOk ? 'text-emerald-700' : 'text-red-600'"></p>

            <div x-show="savedTemplates.length" x-cloak class="space-y-1 max-h-32 overflow-y-auto hide-scrollbar">
                <template x-for="tpl in savedTemplates" :key="'row-' + tpl.id">
                    <div class="flex items-center justify-between gap-2 bg-white border border-gray-200 rounded-lg px-2 py-1.5">
                        <button type="button"
                                @click="applySavedTemplate(tpl.id)"
                                class="flex-1 text-left font-bold text-gray-700 truncate hover:text-emerald-700"
                                x-text="'★ ' + tpl.name"></button>
                        <button type="button"
                                @click="deleteLandTemplate(tpl.id)"
                                class="text-red-500 hover:text-red-700 px-1"
                                title="Загвар устгах">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Зар эсвэл түрээс -->
    <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-200">
        <label class="block font-bold text-gray-700 mb-2">Зарын төрөл <span class="text-red-500">*</span></label>
        <div class="grid grid-cols-2 gap-2">
            <label class="flex items-center justify-center gap-2 bg-white px-3 py-2.5 rounded-xl border-2 cursor-pointer font-bold transition"
                   :class="newDealType === 'sale' ? 'border-emerald-600 text-emerald-700 bg-emerald-50' : 'border-gray-200 text-gray-600 hover:border-emerald-400'">
                <input type="radio" name="deal_type" value="sale" x-model="newDealType" class="accent-emerald-600" @checked(old('deal_type', 'sale') === 'sale') required>
                <span><i class="fa-solid fa-tag"></i> Зарах</span>
            </label>
            <label class="flex items-center justify-center gap-2 bg-white px-3 py-2.5 rounded-xl border-2 cursor-pointer font-bold transition"
                   :class="newDealType === 'rent' ? 'border-blue-600 text-blue-700 bg-blue-50' : 'border-gray-200 text-gray-600 hover:border-blue-400'">
                <input type="radio" name="deal_type" value="rent" x-model="newDealType" class="accent-blue-600" @checked(old('deal_type') === 'rent')>
                <span><i class="fa-solid fa-key"></i> Түрээслэх</span>
            </label>
        </div>
    </div>

    <!-- Зарын гарчиг -->
    <div>
        <label class="block font-bold text-gray-700 mb-1">Зарын гарчиг <span class="text-red-500">*</span></label>
        <input type="text" name="title" x-model="newTitle" value="{{ old('title') }}" placeholder="Жишээ: Хан-Уул дүүрэгт 0.07 га газар" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:outline-none focus:border-emerald-500 font-semibold text-gray-800" required>
    </div>

    <!-- Зарын дэлгэрэнгүй мэдээлэл / Тайлбар -->
    <div>
        <label class="block font-bold text-gray-700 mb-1">Дэлгэрэнгүй мэдээлэл / Тайлбар</label>
        <textarea name="description" rows="8" x-model="newDescription" placeholder="Газрын байршил, орчны мэдээлэл, зам харгуй болон бусад нэмэлт мэдээллээ дэлгэрэнгүй бичнэ үү..." class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:outline-none focus:border-emerald-500 font-semibold text-gray-800 resize-none">{{ old('description') }}</textarea>
    </div>

    <!-- Хэрэглэгч нэвтрээгүй бол утасны дугаар шаардах -->
    @guest
    <div class="bg-amber-50/60 p-3.5 rounded-2xl border border-amber-200">
        <label class="block font-bold text-amber-900 mb-1">
            Холбогдох утасны дугаар <span class="text-red-500">*</span>
        </label>
        <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="Жишээ: 99119911" class="w-full bg-white border border-amber-300 rounded-xl p-2.5 focus:outline-none focus:border-amber-500 font-semibold text-gray-800 shadow-sm" required>
    </div>
    @endguest

    <!-- Үнэ болон Хэмжээ -->
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block font-bold text-gray-700 mb-1">
                <span x-text="newDealType === 'rent' ? 'Түрээсийн үнэ (₮/сар)' : 'Үнэ (₮)'"></span> <span class="text-red-500">*</span>
            </label>
            <input type="number" name="price" x-model="newPrice" value="{{ old('price') }}" placeholder="150000000" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 focus:outline-none focus:border-emerald-500 font-semibold" required>
        </div>
        <div>
            <label class="block font-bold text-gray-700 mb-1">Хэмжээ (га) <span class="text-red-500">*</span></label>
            <input type="number" step="0.01" name="size_ha" x-model="newSizeHa" value="{{ old('size_ha') }}" placeholder="0.07" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 focus:outline-none focus:border-emerald-500 font-semibold" required>
        </div>
    </div>

    <!-- Байршил & Эрхийн төрөл -->
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block font-bold text-gray-700 mb-1">Дүүрэг / Байршил</label>
            <select name="district" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 focus:outline-none focus:border-emerald-500 font-semibold text-gray-700">
                <option value="Хан-Уул" @selected(old('district') === 'Хан-Уул')>Хан-Уул</option>
                <option value="Баянзүрх" @selected(old('district') === 'Баянзүрх')>Баянзүрх</option>
                <option value="Сүхбаатар" @selected(old('district') === 'Сүхбаатар')>Сүхбаатар</option>
                <option value="Чингэлтэй" @selected(old('district') === 'Чингэлтэй')>Чингэлтэй</option>
                <option value="Баянгол" @selected(old('district') === 'Баянгол')>Баянгол</option>
                <option value="Сонгинохайрхан" @selected(old('district') === 'Сонгинохайрхан')>Сонгинохайрхан</option>
            </select>
        </div>
        <div>
            <label class="block font-bold text-gray-700 mb-1">Эрхийн төрөл</label>
            <select name="rights_type" x-model="newRightsType" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 focus:outline-none focus:border-emerald-500 font-semibold text-gray-700">
                <option value="Өмчлөх">Өмчлөх</option>
                <option value="Эзэмших">Эзэмших</option>
            </select>
        </div>
    </div>
  <!-- Дэд бүтцийн 6 шинж чанар (Тог, Ус, Зам, Хашаа, Намаг, Налуу) -->
    <div class="bg-gray-50 p-3.5 rounded-2xl border border-gray-200">
        <label class="block font-bold text-gray-800 mb-2">Инженерчлэл & Дэд бүтцийн нөхцөл</label>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
            <label class="flex items-center gap-2 bg-white px-3 py-2 rounded-xl border border-gray-200 cursor-pointer hover:border-emerald-500 transition shadow-xs">
                <input type="checkbox" name="has_electricity" value="1" x-model="newHasElectricity" class="rounded text-emerald-600 focus:ring-emerald-500">
                <span class="font-semibold text-gray-700">⚡ Тогтой</span>
            </label>
            <label class="flex items-center gap-2 bg-white px-3 py-2 rounded-xl border border-gray-200 cursor-pointer hover:border-emerald-500 transition shadow-xs">
                <input type="checkbox" name="has_water" value="1" x-model="newHasWater" class="rounded text-emerald-600 focus:ring-emerald-500">
                <span class="font-semibold text-gray-700">💧 Ус ойрхон</span>
            </label>
            <label class="flex items-center gap-2 bg-white px-3 py-2 rounded-xl border border-gray-200 cursor-pointer hover:border-emerald-500 transition shadow-xs">
                <input type="checkbox" name="has_fence" value="1" x-model="newHasFence" class="rounded text-emerald-600 focus:ring-emerald-500">
                <span class="font-semibold text-gray-700">🏡 Хашаатай</span>
            </label>
            <label class="flex items-center gap-2 bg-white px-3 py-2 rounded-xl border border-gray-200 cursor-pointer hover:border-emerald-500 transition shadow-xs">
                <input type="checkbox" name="near_road" value="1" x-model="newNearRoad" class="rounded text-emerald-600 focus:ring-emerald-500">
                <span class="font-semibold text-gray-700">🚧 Зам дагуу</span>
            </label>
            <label class="flex items-center gap-2 bg-white px-3 py-2 rounded-xl border border-gray-200 cursor-pointer hover:border-emerald-500 transition shadow-xs">
                <input type="checkbox" name="is_swamp" value="1" x-model="newIsSwamp" class="rounded text-emerald-600 focus:ring-emerald-500">
                <span class="font-semibold text-gray-700">🌊 Намагтай</span>
            </label>
            <label class="flex items-center gap-2 bg-white px-3 py-2 rounded-xl border border-gray-200 cursor-pointer hover:border-emerald-500 transition shadow-xs">
                <input type="checkbox" name="is_sloped" value="1" x-model="newIsSloped" class="rounded text-emerald-600 focus:ring-emerald-500">
                <span class="font-semibold text-gray-700">📐 Налуу</span>
            </label>
        </div>
    </div>
   <!-- Зураг оруулах хэсэг -->
<div>
    <label class="block font-bold text-gray-700 mb-1">
        Зураг оруулах <span class="text-red-500">*</span>
    </label>
    
    <!-- Зураг чирч оруулах Box -->
    <div class="border-2 border-dashed border-gray-300 rounded-2xl p-4 text-center bg-gray-50 hover:bg-gray-100 transition cursor-pointer relative">
        <input type="file" name="images[]" multiple accept="image/*" required class="absolute inset-0 opacity-0 cursor-pointer w-full h-full z-10" id="land-images">
        <label for="land-images" class="cursor-pointer pointer-events-none">
            <i class="fa-solid fa-cloud-arrow-up text-2xl text-emerald-600 mb-1"></i>
            <p class="text-gray-600 font-medium text-xs">Зураг чирч оруулах эсвэл дарж сонгоно уу</p>
            <span class="text-[10px] text-amber-600 font-bold mt-1 block">★ Эхний байрлал дахь зураг автоматаар "НҮҮР ЗУРАГ" байна</span>
        </label>
    </div>

    <!-- Сонгосон зургуудын жагсаалт гарч ирэх хэсэг -->
    <div id="image-preview-container" class="grid grid-cols-3 sm:grid-cols-4 gap-3 mt-3">
        <!-- JavaScript-ээр зургууд энд динамикаар нэмэгдэнэ -->
    </div>
</div>

<!-- JavaScript Logic -->
<script>
    let selectedFiles = []; // Бүх сонгогдсон файлуудыг хадгалах массив

    const fileInput = document.getElementById('land-images');
    const previewContainer = document.getElementById('image-preview-container');

    fileInput.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        
        // Шинэ сонгогдсон зургуудыг үндсэн массив руу нэмэх
        files.forEach(file => {
            selectedFiles.push(file);
        });

        renderPreviews();
        updateInputFiles();
    });

    // Зургуудыг дэлгэцэнд харуулах болон дарааллыг удирдах функц
    function renderPreviews() {
        previewContainer.innerHTML = '';

        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const card = document.createElement('div');
                card.className = `relative group rounded-xl overflow-hidden border-2 bg-white shadow-sm flex flex-col justify-between ${index === 0 ? 'border-emerald-500 ring-2 ring-emerald-500/20' : 'border-gray-200'}`;
                card.setAttribute('draggable', 'true');
                card.dataset.index = index;

                card.innerHTML = `
                    <div class="h-24 w-full bg-gray-100 overflow-hidden relative">
                        <img src="${e.target.result}" class="w-full h-full object-cover">
                        ${index === 0 ? '<span class="absolute top-1 left-1 bg-emerald-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded shadow">НҮҮР ЗУРАГ</span>' : `<span class="absolute top-1 left-1 bg-gray-800/70 text-white text-[9px] font-bold px-1.5 py-0.5 rounded">${index + 1}</span>`}
                    </div>
                    <div class="p-1.5 flex items-center justify-between bg-gray-50 border-t border-gray-100 text-[10px]">
                        <div class="flex gap-1">
                            ${index > 0 ? `<button type="button" onclick="moveImage(${index}, -1)" class="p-1 bg-white border border-gray-200 rounded hover:bg-gray-100 text-gray-600" title="Өмнө нь болгох"><i class="fa-solid fa-arrow-left"></i></button>` : ''}
                            ${index < selectedFiles.length - 1 ? `<button type="button" onclick="moveImage(${index}, 1)" class="p-1 bg-white border border-gray-200 rounded hover:bg-gray-100 text-gray-600" title="Хойш нь болгох"><i class="fa-solid fa-arrow-right"></i></button>` : ''}
                        </div>
                        <button type="button" onclick="removeImage(${index})" class="p-1 bg-red-50 text-red-600 border border-red-100 rounded hover:bg-red-100" title="Устгах"><i class="fa-solid fa-trash"></i></button>
                    </div>
                `;

                // Drag and Drop (Чирч байрлал солих) eventiin listener-үүд
                card.addEventListener('dragstart', handleDragStart);
                card.addEventListener('dragover', handleDragOver);
                card.addEventListener('drop', handleDrop);

                previewContainer.appendChild(card);
            }
            reader.readAsDataURL(file);
        });
    }

    // Зургийн байрлалыг солих (Зүүн / Баруун сум эсвэл чирэх үед)
    function moveImage(index, direction) {
        const newIndex = index + direction;
        if (newIndex < 0 || newIndex >= selectedFiles.length) return;

        // Массив доторх байрлалыг сольж байрлуулах
        const temp = selectedFiles[index];
        selectedFiles[index] = selectedFiles[newIndex];
        selectedFiles[newIndex] = temp;

        renderPreviews();
        updateInputFiles();
    }

    // Зургийг устгах
    function removeImage(index) {
        selectedFiles.splice(index, 1);
        renderPreviews();
        updateInputFiles();
    }

    // DataTransfer ашиглан input type="file"-ийн файлуудыг шинэчлэх (Server рүү зөв илгээгдэх тулд)
    function updateInputFiles() {
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => {
            dataTransfer.items.add(file);
        });
        fileInput.files = dataTransfer.files;
    }

    // --- Drag and Drop Logic ---
    let draggedIndex = null;

    function handleDragStart(e) {
        draggedIndex = parseInt(this.dataset.index);
        e.dataTransfer.effectAllowed = 'move';
    }

    function handleDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    }

    function handleDrop(e) {
        e.preventDefault();
        const targetIndex = parseInt(this.dataset.index);

        if (draggedIndex !== targetIndex) {
            const movedFile = selectedFiles.splice(draggedIndex, 1)[0];
            selectedFiles.splice(targetIndex, 0, movedFile);

            renderPreviews();
            updateInputFiles();
        }
    }
</script>
<!-- Газрын зураг дээрх байршил / Координат -->
  <!-- Leaflet.js CSS -->
<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
/>

<!-- HTML Хэсэг -->
<div class="bg-gray-50 p-3.5 rounded-2xl border border-gray-200 space-y-2">
    <div class="flex items-center justify-between">
        <label class="block font-bold text-gray-800">
            <i class="fa-solid fa-map-location-dot text-emerald-600 mr-1"></i> Газрын зураг дээрх байршил
        </label>
        <span class="text-[10px] text-gray-400 font-medium">Зураг дээр дарж эсвэл чирч байршлаа сонгоно уу</span>
    </div>

    <!-- Газрын зургийг байрлуулах хэсэг (style="height: 250px;" болгож өндрийг нь шууд заалаа) -->
<div
    id="map"
    style="width: 100%; height: 310px; min-height: 310px;"
    class="rounded-xl border border-gray-300 overflow-hidden"
></div>
    <!-- Координатын утгууд (Database руу хадгалагдах Input-үүд) -->
    <div class="grid grid-cols-2 gap-2">
        <div>
            <label class="block text-[10px] font-bold text-gray-500 mb-0.5">Өргөрөг (Latitude)</label>
            <input type="text" id="latitude" name="latitude" placeholder="47.9184" readonly
                   class="w-full bg-gray-100 border border-gray-200 rounded-xl p-2 text-xs font-mono font-semibold text-gray-700 focus:outline-none">
        </div>
        <div>
            <label class="block text-[10px] font-bold text-gray-500 mb-0.5">Уртраг (Longitude)</label>
            <input type="text" id="longitude" name="longitude" placeholder="106.9177" readonly
                   class="w-full bg-gray-100 border border-gray-200 rounded-xl p-2 text-xs font-mono font-semibold text-gray-700 focus:outline-none">
        </div>
    </div>
</div>

<!-- Leaflet.js Скрипт -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
(function () {
    function initMap() {
        const container = document.getElementById('map');

        if (!container || typeof L === 'undefined') {
            return;
        }

        // Нэг газрын зургийг дахин дахин үүсгэхээс хамгаална
        if (container._leaflet_id) {
            return;
        }

        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');

        const defaultLat = parseFloat(latInput?.value) || 47.9184;
        const defaultLng = parseFloat(lngInput?.value) || 106.9177;

        const satelliteLayer = L.tileLayer(
            'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
            {
                maxZoom: 19,
                attribution: 'Tiles &copy; Esri'
            }
        );

        const streetLayer = L.tileLayer(
            'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }
        );

        const map = L.map(container, {
            center: [defaultLat, defaultLng],
            zoom: 15,
            layers: [satelliteLayer]
        });

        L.control.layers({
            'Хиймэл дагуул': satelliteLayer,
            'Гудамжны зураг': streetLayer
        }).addTo(map);

        const marker = L.marker(
            [defaultLat, defaultLng],
            { draggable: true }
        ).addTo(map);

        function updateInputs(lat, lng) {
            if (latInput) latInput.value = lat.toFixed(6);
            if (lngInput) lngInput.value = lng.toFixed(6);
        }

        // egazar шалгалтаас ирсэн байршлыг газрын зураг дээр тэмдэглэнэ
        window.gazarSetCreateLocation = function (lat, lng) {
            const latitude = Number(lat);
            const longitude = Number(lng);

            if (!isFinite(latitude) || !isFinite(longitude)) return;
            if (latitude < -90 || latitude > 90 || longitude < -180 || longitude > 180) return;

            const target = L.latLng(latitude, longitude);
            marker.setLatLng(target);
            map.setView(target, Math.max(map.getZoom() || 15, 17));
            updateInputs(latitude, longitude);
        };

        map.on('click', function (e) {
            marker.setLatLng(e.latlng);
            updateInputs(e.latlng.lat, e.latlng.lng);
        });

        marker.on('dragend', function () {
            const pos = marker.getLatLng();
            updateInputs(pos.lat, pos.lng);
        });

        // Хэмжээг газрын зураг харагдсаны дараа шинэчилнэ
        function refreshMap() {
            if (!container.isConnected) return;

            if (container.offsetWidth > 0 &&
                container.offsetHeight > 0) {
                map.invalidateSize({ pan: false });
            }
        }

        requestAnimationFrame(() => {
            requestAnimationFrame(refreshMap);
        });

        // Modal, responsive layout, form-ийн хэмжээ өөрчлөгдөхөд
        if (typeof ResizeObserver !== 'undefined') {
            const observer = new ResizeObserver(() => {
                refreshMap();
            });

            observer.observe(container);
        }

        // Tile ачаалагдахгүй байгаа эсэхийг шалгах
        satelliteLayer.on('tileerror', function (e) {
            console.error('Satellite tile ачаалагдсангүй:', e);
        });

        updateInputs(defaultLat, defaultLng);

        // Шалгалт зураг ачаалагдахаас өмнө дууссан бол хүлээгдэж буй байршлыг хэрэглэнэ
        if (Array.isArray(window.gazarPendingCreateLocation)) {
            const [pendingLat, pendingLng] = window.gazarPendingCreateLocation;
            window.gazarPendingCreateLocation = null;
            window.gazarSetCreateLocation(pendingLat, pendingLng);
        }
    }

    // DOMContentLoaded аль хэдийн болсон байсан ч ажиллана
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMap, {
            once: true
        });
    } else {
        initMap();
    }
})();
</script>
    <!-- Үйлдлийн товчнууд -->
    <div class="flex justify-end gap-2 pt-3 border-t">
        <button type="button" @click="openCreateModal = false" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold hover:bg-gray-200 transition">Цуцлах</button>
        <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold transition shadow-md">Зарыг илгээх</button>
    </div>
</form>
    </div>
</div>