<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConnectorResource\Pages;
use App\Filament\Resources\ConnectorResource\RelationManagers;
use App\Models\Connector;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ConnectorResource extends Resource
{
    protected static ?string $model = Connector::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationLabel = 'Connectors';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('uuid')
                    ->disabled(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->options([
                        'cpanel' => 'cPanel',
                        'wordpress' => 'WordPress',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('base_url')
                    ->url()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('username')
                    ->maxLength(255),
                Forms\Components\TextInput::make('secret')
                    ->password()
                    ->maxLength(255),
                Forms\Components\Checkbox::make('is_active'),
                Forms\Components\Checkbox::make('ssl_verify'),
                Forms\Components\TextInput::make('timeout_seconds')
                    ->numeric()
                    ->default(30)
                    ->minValue(1)
                    ->maxValue(300),
                Forms\Components\Textarea::make('meta_json')
                    ->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('uuid')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'primary' => 'cpanel',
                        'info' => 'wordpress',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('base_url')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('username')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BooleanColumn::make('is_active')
                    ->label('Active')
                    ->sortable(),
                Tables\Columns\BooleanColumn::make('ssl_verify')
                    ->label('SSL Verify')
                    ->sortable(),
                Tables\Columns\TextColumn::make('timeout_seconds')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'cpanel' => 'cPanel',
                        'wordpress' => 'WordPress',
                    ]),
                Tables\Filters\SelectFilter::make('is_active')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConnectors::route('/'),
            'create' => Pages\CreateConnector::route('/create'),
            'edit' => Pages\EditConnector::route('/{record}/edit'),
        ];
    }
}
