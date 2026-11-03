<?php

use Illuminate\Support\Facades\Http;

function egazarFakeMapPage(): string
{
    return '<script>var csrf = "token123";</script>';
}

it('returns parcel details from egazar for a valid parcel', function () {
    $parcel = [[
        'parcel_id' => '1800704601',
        'property_no' => 'г2201010962',
        'area_m2' => '311',
        'address_streetname' => 'Мандал овоо гудамж-9',
        'address_khashaa' => '53',
        'au1_name' => 'Улаанбаатар',
        'au2_name' => 'Сонгинохайрхан',
        'au3_name' => '26-р хороо',
        'description' => 'Гэр, орон сууцны хашааны газар',
        'parcel_x' => '106.80382900454902',
        'parcel_y' => '47.93324894928276',
    ]];

    Http::fake([
        'https://www.egazar.gov.mn/map*' => Http::response(egazarFakeMapPage()),
        'https://www.egazar.gov.mn/api/parcel/detail/by/id' => Http::response([
            'success' => true,
            'returnJson' => base64_encode(json_encode($parcel, JSON_UNESCAPED_UNICODE)),
        ]),
    ]);

    $this->postJson('/check-parcel', ['parcel_no' => '1800704601'])
        ->assertOk()
        ->assertJson([
            'found' => true,
            'parcel_id' => '1800704601',
            'area_m2' => 311,
            'size_ha' => 0.0311,
            'district' => 'Сонгинохайрхан',
            'khoroo' => '26-р хороо',
            'landuse' => 'Гэр, орон сууцны хашааны газар',
            'egazar_url' => 'https://www.egazar.gov.mn/map?parcel_no=1800704601',
        ]);
});

it('reports a parcel as not found when egazar returns a server error', function () {
    Http::fake([
        'https://www.egazar.gov.mn/map*' => Http::response(egazarFakeMapPage()),
        'https://www.egazar.gov.mn/api/parcel/detail/by/id' => Http::response(['message' => 'Server Error'], 500),
    ]);

    $this->postJson('/check-parcel', ['parcel_no' => '9999999999'])
        ->assertOk()
        ->assertJson(['found' => false])
        ->assertJsonPath('message', 'Энэ дугаартай нэгж талбар egazar.gov.mn-ээс олдсонгүй. Дугаараа дахин шалгана уу.');
});

it('requires a parcel number', function () {
    $this->postJson('/check-parcel', ['parcel_no' => ''])
        ->assertStatus(422)
        ->assertJsonValidationErrors('parcel_no');
});
