<?php

namespace App\Http\Controllers;

use App\Models\Land;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class LandController extends Controller
{
    /**
     * egazar.gov.mn-ийн нэгж талбарын хайлтын хаяг.
     */
    private const EGAZAR_MAP_URL = 'https://www.egazar.gov.mn/map';

    private const EGAZAR_PARCEL_API = 'https://www.egazar.gov.mn/api/parcel/detail/by/id';

    public function index()
    {
        $lands = Land::query()
            ->where('status', 'active')
            ->latest()
            ->get()
            ->map(function (Land $land) {
                $images = $land->images;

                // Хуучин бичлэгүүдэд images нь JSON string хэлбэрээр хадгалагдсан байж болно
                if (is_string($images)) {
                    $decoded = json_decode($images, true);
                    $images = is_array($decoded) ? $decoded : array_filter([$images]);
                }

                if (!is_array($images)) {
                    $images = [];
                }

                $sizeHa = (float) ($land->size_ha ?? 0);
                $createdAt = $land->created_at;

                return [
                    'id' => $land->id,
                    'title' => $land->title,
                    'description' => $land->description,
                    'district' => $land->district,
                    'location' => 'Улаанбаатар, ' . $land->district,
                    'price' => (float) $land->price,
                    'size_ha' => $sizeHa,
                    'size_m2' => (int) round($sizeHa * 10000),
                    'cadastral_code' => $land->cadastral_code,
                    'rights_type' => $land->rights_type,
                    'deal_type' => $land->deal_type === 'rent' ? 'rent' : 'sale',
                    'views_count' => (int) ($land->views_count ?? 0),
                    'is_egazar_verified' => filled($land->cadastral_code),
                    'created_at' => $createdAt?->isToday()
                        ? 'Өнөөдөр'
                        : ($createdAt?->isYesterday()
                            ? 'Өчигдөр'
                            : $createdAt?->format('Y.m.d')),
                    'phone' => $land->phone,
                    'has_electricity' => (bool) $land->has_electricity,
                    'has_water' => (bool) $land->has_water,
                    'has_fence' => (bool) $land->has_fence,
                    'near_road' => (bool) $land->near_road,
                    'is_swamp' => (bool) $land->is_swamp,
                    'is_sloped' => (bool) $land->is_sloped,
                    'latitude' => $land->latitude !== null ? (float) $land->latitude : null,
                    'longitude' => $land->longitude !== null ? (float) $land->longitude : null,
                    'images' => array_values($images),
                ];
            })
            ->values();

        return view('lands.index', compact('lands'));
    }

    /**
     * Зарыг хэдэн хүн үзсэнийг тоолно (нэг сессэд нэг удаа).
     */
    public function view(Request $request, Land $land)
    {
        $sessionKey = 'land_viewed_' . $land->id;

        if (! $request->session()->has($sessionKey)) {
            $land->increment('views_count');
            $request->session()->put($sessionKey, true);
        }

        return response()->json([
            'views_count' => (int) $land->views_count,
        ]);
    }

    /**
     * Нэгж талбарын дугаарыг egazar.gov.mn-ээс шалгана.
     */
    public function checkParcel(Request $request)
    {
        $validated = $request->validate([
            'parcel_no' => ['required', 'string', 'max:50'],
        ], [
            'parcel_no.required' => 'Нэгж талбарын дугаараа оруулна уу.',
        ]);

        $parcelNo = preg_replace('/\D+/', '', $validated['parcel_no']) ?? '';

        if (strlen($parcelNo) < 6) {
            return response()->json([
                'found' => false,
                'message' => 'Дугаар хэт богино байна. Кадастрын дугаараа бүтэн оруулна уу.',
            ]);
        }

        $cacheKey = 'egazar_parcel_' . $parcelNo;

        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return response()->json($cached);
        }

        try {
            $result = $this->lookupEgazarParcel($parcelNo);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'found' => false,
                'error' => 'unreachable',
                'message' => 'egazar.gov.mn-тэй холбогдож чадсангүй. Түр хүлээгээд дахин оролдоно уу.',
            ]);
        }

        // Олдсон талбарын мэдээллийг 24 цаг, олдоогүйг 10 минут кэшлэнэ.
        Cache::put($cacheKey, $result, $result['found'] ? now()->addDay() : now()->addMinutes(10));

        return response()->json($result);
    }

    /**
     * egazar.gov.mn-ийн map хуудсаас CSRF авч, нэгж талбарын мэдээллийг татна.
     *
     * @return array<string, mixed>
     */
    private function lookupEgazarParcel(string $parcelNo): array
    {
        $jar = new CookieJar();

        $client = Http::withOptions(['cookies' => $jar])
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ])
            ->timeout(15);

        $page = $client->get(self::EGAZAR_MAP_URL);

        if (! $page->ok()) {
            throw new RuntimeException('egazar map хуудас ачаалагдсангүй: HTTP ' . $page->status());
        }

        preg_match('/var\s+csrf\s*=\s*"([^"]+)"/', $page->body(), $matches);
        $csrf = $matches[1] ?? null;

        if (! $csrf) {
            throw new RuntimeException('egazar CSRF токен олдсонгүй.');
        }

        $response = Http::withOptions(['cookies' => $jar])
            ->asForm()
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0 Safari/537.36',
                'Accept' => 'application/json, text/javascript, */*; q=0.01',
                'X-Requested-With' => 'XMLHttpRequest',
                'Referer' => self::EGAZAR_MAP_URL . '?parcel_no=' . $parcelNo,
            ])
            ->timeout(20)
            ->post(self::EGAZAR_PARCEL_API, [
                'id' => $parcelNo,
                '_token' => $csrf,
            ]);

        if (! $response->ok()) {
            // egazar буруу/байхгүй дугаарт 500 + {"message":"Server Error"} буцаадаг
            if ($response->status() >= 500 && $response->json('message') === 'Server Error') {
                return $this->parcelNotFound($parcelNo);
            }

            throw new RuntimeException('egazar API хариу өгсөнгүй: HTTP ' . $response->status());
        }

        $encoded = $response->json('returnJson');

        if (! is_string($encoded) || $encoded === '') {
            return $this->parcelNotFound($parcelNo);
        }

        $decoded = base64_decode($encoded, true);

        if ($decoded === false) {
            return $this->parcelNotFound($parcelNo);
        }

        $parcels = json_decode($decoded, true);
        $parcel = is_array($parcels) ? ($parcels[0] ?? null) : null;

        if (! is_array($parcel) || empty($parcel['parcel_id'])) {
            return $this->parcelNotFound($parcelNo);
        }

        $areaM2 = (int) round((float) ($parcel['area_m2'] ?? 0));

        $addressParts = array_filter([
            $parcel['au1_name'] ?? null,
            $parcel['au2_name'] ?? null,
            $parcel['au3_name'] ?? null,
            $parcel['address_streetname'] ?? null,
            ! empty($parcel['address_khashaa']) ? $parcel['address_khashaa'] . '-р байр' : null,
        ], fn ($part) => is_string($part) && trim($part) !== '');

        $district = trim((string) ($parcel['au2_name'] ?? ''));
        $khoroo = trim((string) ($parcel['au3_name'] ?? ''));

        $sizeHa = $areaM2 > 0 ? round($areaM2 / 10000, 4) : null;

        return [
            'found' => true,
            'parcel_id' => (string) $parcel['parcel_id'],
            'property_no' => trim((string) ($parcel['property_no'] ?? '')),
            'area_m2' => $areaM2,
            'area_label' => $areaM2 > 0
                ? number_format($areaM2, 0, '.', ' ') . ' м²' . ($sizeHa ? ' (' . rtrim(rtrim(number_format($sizeHa, 4, '.', ''), '0'), '.') . ' га)' : '')
                : '—',
            'size_ha' => $sizeHa,
            'district' => $district,
            'khoroo' => $khoroo,
            'address_label' => $addressParts ? implode(', ', $addressParts) : '—',
            'landuse' => trim((string) ($parcel['description'] ?? '')),
            'valid_from' => $parcel['valid_from'] ?? null,
            'latitude' => isset($parcel['parcel_y']) ? (float) $parcel['parcel_y'] : null,
            'longitude' => isset($parcel['parcel_x']) ? (float) $parcel['parcel_x'] : null,
            'egazar_url' => self::EGAZAR_MAP_URL . '?parcel_no=' . $parcelNo,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function parcelNotFound(string $parcelNo): array
    {
        return [
            'found' => false,
            'message' => 'Энэ дугаартай нэгж талбар egazar.gov.mn-ээс олдсонгүй. Дугаараа дахин шалгана уу.',
            'egazar_url' => self::EGAZAR_MAP_URL . '?parcel_no=' . $parcelNo,
        ];
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cadastral_code' => ['required', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:30'],
            'price' => ['required', 'numeric', 'min:0'],
            'size_ha' => ['required', 'numeric', 'gt:0'],
            'district' => ['required', 'string', 'max:100'],
            'rights_type' => ['required', 'string', 'max:50'],
            'deal_type' => ['required', 'in:sale,rent'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ], [
            'title.required' => 'Зарын гарчиг оруулна уу.',
            'cadastral_code.required' => 'Нэгж талбарын дугаар (кадастр) оруулна уу.',
            'price.required' => 'Үнэ оруулна уу.',
            'size_ha.required' => 'Газрын хэмжээг оруулна уу.',
            'size_ha.gt' => 'Газрын хэмжээ 0-ээс их байх ёстой.',
            'images.required' => 'Заавал дор хаяж 1 зураг оруулна уу.',
            'images.min' => 'Заавал дор хаяж 1 зураг оруулна уу.',
            'images.*.image' => 'Зөвхөн зургийн файл оруулна уу.',
            'images.*.mimes' => 'Зургийн формат jpg, jpeg, png, webp эсвэл gif байх ёстой.',
            'images.*.max' => 'Нэг зураг 5MB-аас хэтрэхгүй байх ёстой.',
        ]);

        // Зургуудыг storage/app/public/lands руу хадгална
        $imagePaths = [];

        foreach ($request->file('images', []) as $image) {
            $imagePaths[] = $image->store('lands', 'public');
        }

        Land::create([
            'user_id' => (string) (auth()->id() ?? 'guest'),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'cadastral_code' => $validated['cadastral_code'],
            'phone' => $validated['phone'] ?? null,
            'price' => $validated['price'],
            'size_ha' => $validated['size_ha'],
            'district' => $validated['district'],
            'rights_type' => $validated['rights_type'],
            'deal_type' => $validated['deal_type'],
            'has_electricity' => $request->boolean('has_electricity'),
            'has_water' => $request->boolean('has_water'),
            'has_fence' => $request->boolean('has_fence'),
            'near_road' => $request->boolean('near_road'),
            'is_swamp' => $request->boolean('is_swamp'),
            'is_sloped' => $request->boolean('is_sloped'),
            'images' => $imagePaths,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'status' => 'active',
        ]);

        return redirect()
            ->route('home')
            ->with('success', 'Газрын зар амжилттай нэмэгдлээ!');
    }
}
