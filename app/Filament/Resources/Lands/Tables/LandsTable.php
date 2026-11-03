<?php

namespace App\Filament\Resources\Lands\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LandsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->limit(50),

                TextColumn::make('deal_type')
                    ->label('Төрөл')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'rent' ? 'Түрээслэх' : 'Зарах')
                    ->color(fn ($state) => $state === 'rent' ? 'info' : 'success')
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Үнэ (₮)')
                    ->numeric()
                    ->prefix('₮')
                    ->formatStateUsing(fn ($state, $record) => number_format((float) $state) . ($record && $record->deal_type === 'rent' ? ' /сар' : '')),

                TextColumn::make('district')
                    ->label('Байршил'),

                TextColumn::make('size_ha')
                    ->label('Хэмжээ (га)')
                    ->numeric(2),

                TextColumn::make('views_count')
                    ->label('Үзсэн')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('offers_count')
                    ->label('Санал')
                    ->counts('offers')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('warning'),

                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'active' ? 'Идгээр байна' : 'Идгээр байхгүй')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Үүсгэсэн огрун')
                    ->formatStateUsing(fn ($state) => $state ? date('Y.m.d H:i', strtotime((string) $state)) : '-'),
            ])
            ->filters([
                SelectFilter::make('deal_type')
                    ->label('Зарын төрөл')
                    ->options([
                        'sale' => 'Зарах',
                        'rent' => 'Түрээслэх',
                    ])
                    ->attribute('deal_type'),

                SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'active' => 'Идгээр байна',
                        'inactive' => 'Идгээр байхгүй',
                    ])
                    ->attribute('status'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
