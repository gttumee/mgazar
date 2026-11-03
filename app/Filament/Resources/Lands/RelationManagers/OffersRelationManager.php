<?php

namespace App\Filament\Resources\Lands\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OffersRelationManager extends RelationManager
{
    protected static string $relationship = 'offers';

    protected static ?string $title = 'Үнийн саналууд';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('phone')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('price')
                    ->label('Санал болгосон үнэ')
                    ->numeric()
                    ->prefix('₮')
                    ->formatStateUsing(fn ($state) => number_format((float) $state)),

                TextColumn::make('phone')
                    ->label('Утас')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('message')
                    ->label('Тайлбар')
                    ->wrap()
                    ->limit(120)
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->label('Төлөв')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'new' ? 'Шинэ' : (string) $state)
                    ->color(fn ($state) => $state === 'new' ? 'warning' : 'gray'),

                TextColumn::make('created_at')
                    ->label('Илгээсэн')
                    ->dateTime('Y.m.d H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
