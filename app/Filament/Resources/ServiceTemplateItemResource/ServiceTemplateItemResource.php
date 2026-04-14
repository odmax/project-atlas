<?php

namespace App\Filament\Resources\ServiceTemplateItemResource;

use App\Models\ServiceTemplateItem;
use App\Models\Connector;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceTemplateItemResource extends Resource
{
    protected static ?string $model = ServiceTemplateItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-plus-circle';

    protected static ?string $navigationLabel = 'Template Items';

    protected static ?int $navigationSort = 7;

    protected static ?string $parent = ServiceTemplateResource::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('connector_id')
                    ->label('Connector')
                    ->options(Connector::all()->pluck('name', 'id'))
                    ->required()
                    ->helperText('External system to provision this service in'),
                Forms\Components\TextInput::make('account_type')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('email, wordpress_admin, cpanel_account')
                    ->helperText('Type of account to create'),
                Forms\Components\TextInput::make('default_role')
                    ->maxLength(255)
                    ->placeholder('user, editor, administrator')
                    ->helperText('Default role to assign (optional)'),
                Forms\Components\KeyValue::make('metadata_json')
                    ->keyLabel('Key')
                    ->valueLabel('Value')
                    ->helperText('Additional metadata for provisioning'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('connector.name')
                    ->label('Connector')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('account_type')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('default_role')
                    ->label('Default Role')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceTemplateItems::route('/'),
            'create' => Pages\CreateServiceTemplateItem::route('/create'),
            'edit' => Pages\EditServiceTemplateItem::route('/{record}/edit'),
        ];
    }
}
