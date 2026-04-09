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
                Forms\Components\Tabs::make('connector_tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('general')
                            ->label('General')
                            ->icon('heroicon-o-cog')
                            ->schema([
                                Forms\Components\TextInput::make('uuid')
                                    ->disabled()
                                    ->label('UUID'),
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('My cPanel Server')
                                    ->helperText('A friendly name to identify this connector'),
                                Forms\Components\Select::make('type')
                                    ->options([
                                        'cpanel' => 'cPanel',
                                        'wordpress' => 'WordPress',
                                    ])
                                    ->required()
                                    ->live(),
                                Forms\Components\Checkbox::make('is_active')
                                    ->label('Active')
                                    ->helperText('Enable this connector for sync operations'),
                                Forms\Components\Checkbox::make('ssl_verify')
                                    ->label('Verify SSL Certificate')
                                    ->default(true),
                            ]),
                        Forms\Components\Tabs\Tab::make('connection')
                            ->label('Connection')
                            ->icon('heroicon-o-link')
                            ->schema([
                                Forms\Components\TextInput::make('base_url')
                                    ->url()
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('https://server.example.com:2087')
                                    ->helperText('Full URL to your cPanel/WHM or WordPress instance'),
                                Forms\Components\TextInput::make('username')
                                    ->maxLength(255)
                                    ->placeholder('root')
                                    ->helperText('cPanel username or API token'),
                                Forms\Components\TextInput::make('secret')
                                    ->password()
                                    ->maxLength(255)
                                    ->placeholder('••••••••')
                                    ->helperText('API key or password'),
                                Forms\Components\TextInput::make('timeout_seconds')
                                    ->numeric()
                                    ->default(30)
                                    ->minValue(1)
                                    ->maxValue(300)
                                    ->label('Timeout (seconds)'),
                            ]),
                        Forms\Components\Tabs\Tab::make('advanced')
                            ->label('Advanced')
                            ->icon('heroicon-o-cog')
                            ->schema([
                                Forms\Components\Textarea::make('meta_json')
                                    ->rows(3)
                                    ->label('Metadata (JSON)')
                                    ->placeholder('{"key": "value"}')
                                    ->helperText('Additional configuration options'),
                            ]),
                    ]),
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
