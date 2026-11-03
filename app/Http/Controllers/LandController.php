<?php

namespace App\Http\Controllers;

use App\Models\Land;
use Illuminate\Http\Request;

class LandController extends Controller
{
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
