<?php

namespace App\Filament\Resources\Lands\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class LandForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Зарын загвар ашиглах')
                    ->description('Загвар сонгоход зарын төрөл, эрхийн төрөл, гарчиг, тайлбар болон дэд бүтцийн нөхцөл автоматаар бөглөгдөнө.')
                    ->schema([
                        Select::make('land_template')
                            ->label('Загвар')
                            ->options([
                                '' => '1. Хоосон',
                                'sale' => '2. Зарах бэлэн загвар',
                                'rent' => '3. Түрээслэх бэлэн загвар',
                            ])
                            ->default('sale')
                            ->live()
                            ->dehydrated(false)
                            ->afterStateUpdated(function (Set $set, ?string $state): void {
                                static::applyTemplate($set, $state);
                            }),
                    ]),

                Section::make('Зарын үндсэн мэдээлэл')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('deal_type')
                                    ->label('Зарын төрөл')
                                    ->options([
                                        'sale' => 'Зарах',
                                        'rent' => 'Түрээслэх',
                                    ])
                                    ->default('sale')
                                    ->required()
                                    ->live(),

                                Select::make('status')
                                    ->label('Газарын зар статус')
                                    ->options([
                                        'active' => 'Идэвхтэй (Нийгээнд харуулах)',
                                        'inactive' => 'Идэвхгүй (Харуулахгүй)',
                                    ])
                                    ->default('active')
                                    ->required(),
                            ]),

                        TextInput::make('title')
                            ->label('Зарын гарчиг')
                            ->placeholder('Жишээ: Хан-Уул дүүрэгт 0.07 га газар')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Дэлгэрэнгүй мэдээлэл / Тайлбар')
                            ->placeholder('Газрын байршил, орчны мэдээлэл, зам харгуй болон бусад нэмэлт мэдээллээ дэлгэрэнгүй бичнэ үү...')
                            ->rows(10)
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('cadastral_code')
                                    ->label('Нэгж талбарын дугаар (Кадастр)')
                                    ->placeholder('Жишээ: 1680710776')
                                    ->required(),

                                TextInput::make('phone')
                                    ->label('Холбогдох утасны дугаар')
                                    ->placeholder('Жишээ: 99119911')
                                    ->tel(),
                            ]),
                    ]),

                Section::make('Үнэ ба хэмжээ')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('price')
                                    ->label(fn (Get $get): string => $get('deal_type') === 'rent'
                                        ? 'Түрээсийн үнэ (₮/сар)'
                                        : 'Үнэ (₮)')
                                    ->placeholder('Жишээ: 150000000')
                                    ->helperText('Түрээсийн зар бол сарын төлбөрийг оруулна.')
                                    ->required()
                                    ->numeric(),

                                TextInput::make('size_ha')
                                    ->label('Хэмжээ (га)')
                                    ->placeholder('0.07')
                                    ->helperText('Газрын нийт талбайг гектараар оруулна.')
                                    ->required()
                                    ->numeric(),
                            ]),
                    ]),

                Section::make('Байршил ба эрхийн төрөл')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('district')
                                    ->label('Дүүрэг / Байршил')
                                    ->options([
                                        'Хан-Уул' => 'Хан-Уул',
                                        'Баянзүрх' => 'Баянзүрх',
                                        'Сүхбаатар' => 'Сүхбаатар',
                                        'Чингэлтэй' => 'Чингэлтэй',
                                        'Баянгол' => 'Баянгол',
                                        'Сонгинохайрхан' => 'Сонгинохайрхан',
                                    ])
                                    ->required(),

                                Select::make('rights_type')
                                    ->label('Эрхийн төрөл')
                                    ->options([
                                        'Өмчлөх' => 'Өмчлөх',
                                        'Эзэмших' => 'Эзэмших',
                                    ])
                                    ->required(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('latitude')
                                    ->label('Өргөрөг (Latitude)')
                                    ->placeholder('47.9184')
                                    ->numeric(),

                                TextInput::make('longitude')
                                    ->label('Уртраг (Longitude)')
                                    ->placeholder('106.9177')
                                    ->numeric(),
                            ]),

                        Text::make(
                            'Координат оруулаагүй бол шинэ зар дүүргийн төвд автоматаар байрлана. Газрын зураг дээрх байршлыг нийгэмд харуулах тул зөв оруулна уу.'
                        )
                            ->size('sm'),
                    ]),

                Section::make('Инженерчлэл & Дэд бүтцийн нөхцөл')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Checkbox::make('has_electricity')
                                    ->label('Тогтой'),

                                Checkbox::make('has_water')
                                    ->label('Ус ойрхон'),

                                Checkbox::make('has_fence')
                                    ->label('Хашаатай'),

                                Checkbox::make('near_road')
                                    ->label('Зам дагуу'),

                                Checkbox::make('is_swamp')
                                    ->label('Намагтай'),

                                Checkbox::make('is_sloped')
                                    ->label('Налуу'),
                            ]),
                    ]),

                Section::make('Зураг оруулах')
                    ->description('Эхний байрлал дахь зураг автоматаар «НҮҮР ЗУРАГ» болно. Зургуудыг чирж байрлалыг нь сольж болно.')
                    ->schema([
                        FileUpload::make('images')
                            ->label('Зураг')
                            ->multiple()
                            ->reorderable()
                            ->disk('public')
                            ->directory('lands')
                            ->image()
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * Сонгосон загварын дагуу маягтын талбаруудыг бөглөнө.
     */
    protected static function applyTemplate(Set $set, ?string $state): void
    {
        foreach (static::templateData($state) as $field => $value) {
            $set($field, $value);
        }
    }

    /**
     * Загварын утгуудыг буцаана.
     *
     * @return array<string, mixed>
     */
    public static function templateData(?string $state): array
    {
        if ($state === 'rent') {
            return [
                'deal_type' => 'rent',
                'rights_type' => 'Эзэмших',
                'title' => '0,7 той газар түрээслэнэ',
                'description' => implode("\n", [
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
                    'Удахгүй нарны замын өргөтгөл зам хажуугаар нь тавигдана.',
                ]),
                'has_electricity' => true,
                'has_water' => true,
                'has_fence' => true,
                'near_road' => true,
                'is_swamp' => false,
                'is_sloped' => false,
            ];
        }

        if ($state === 'sale') {
            return [
                'deal_type' => 'sale',
                'rights_type' => 'Өмчлөх',
                'title' => '0,7 той газар зарна',
                'description' => implode("\n", [
                    'Өмчлөлийн гэрчилгээтэй',
                    'Төвийн бохир, цэвэрийн шугам яг урд талдаа явдаг',
                    'Орц гарц сайтай, өргөн гудамжтай',
                    'Тэгш газартай',
                    '0,7 хэмжээтэй',
                    'Төв замтай ойрхон',
                    'Агуулахаар ашиглах болон үйлдвэрлэлийн барилга барих боломжтой.',
                    'Удахгүй нарны замын өргөтгөл зам хажуугаар нь тавигдана.',
                    'Бэлэн төлөл болон бартер хийнэ зарна.',
                ]),
                'has_electricity' => true,
                'has_water' => true,
                'has_fence' => true,
                'near_road' => true,
                'is_swamp' => false,
                'is_sloped' => false,
            ];
        }

        // Хоосон — талбаруудыг цэвэрлэнэ
        return [
            'deal_type' => 'sale',
            'rights_type' => 'Өмчлөх',
            'title' => '',
            'description' => '',
            'has_electricity' => false,
            'has_water' => false,
            'has_fence' => false,
            'near_road' => false,
            'is_swamp' => false,
            'is_sloped' => false,
        ];
    }
}
