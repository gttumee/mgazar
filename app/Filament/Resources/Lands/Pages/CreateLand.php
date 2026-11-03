<?php

namespace App\Filament\Resources\Lands\Pages;

use App\Filament\Resources\Lands\LandResource;
use App\Filament\Resources\Lands\Schemas\LandForm;
use Filament\Resources\Pages\CreateRecord;

class CreateLand extends CreateRecord
{
    protected static string $resource = LandResource::class;

    public function mount(): void
    {
        parent::mount();

        // Шинэ зар нээх үед "Зарах бэлэн загвар"-ыг автоматаар бөглөнө.
        // Хэрэглэгч загварыг сольж эсвэл хоосон болгож болно.
        if (blank($this->data['title'] ?? null) && blank($this->data['description'] ?? null)) {
            $this->data['land_template'] = 'sale';

            foreach (LandForm::templateData('sale') as $field => $value) {
                $this->data[$field] = $value;
            }
        }

        $this->form->fill($this->data);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = (string) (auth()->id() ?? 'guest');

        // Зарыг оруулахад координат сонгоогүй бол, дүүрэг үндэслэн
        // газрын зураг дээр хуваж харагддах болгоно.
        if (empty($data['latitude']) || empty($data['longitude'])) {
            $districtDefaults = [
                'Хан-Уул' => [47.9188, 106.9176],
                'Баянзүрх' => [47.9160, 106.9500],
                'Сүхбаатар' => [47.9700, 106.9500],
                'Чингэлтэй' => [47.8850, 106.9650],
                'Баянгол' => [47.9300, 106.8900],
                'Сонгинохайрхан' => [47.9500, 106.8600],
            ];

            $district = $data['district'] ?? '';
            $default = $districtDefaults[$district] ?? [47.9188, 106.9176];
            $data['latitude'] = $default[0];
            $data['longitude'] = $default[1];
        }

        return $data;
    }
}
