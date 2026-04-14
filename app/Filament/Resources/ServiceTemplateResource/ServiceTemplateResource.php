<?php

namespace App\Filament\Resources\ServiceTemplateResource;

use App\Models\ServiceTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceTemplateResource extends Resource
{
    protected static ?string $model = ServiceTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-template';

    protected static ?string $navigationLabel = 'Service Templates';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Standard Employee Package')
                    ->helperText('Template name for identification'),
                Forms\Components\Textarea::make('description')
                    ->rows(3)
                    ->placeholder('Default services for new employees'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->helperText('Enable this template for user onboarding'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->query(fn ($query) => $query->where('is_active', true))
                    ->label('Active Only'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('manage_items')
                    ->label('Manage Items')
                    ->icon('heroicon-o-list-bullet')
                    ->url(fn (ServiceTemplate $record) => route('filament.admin.resources.service-template-items.index', ['record' => $record])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceTemplates::route('/'),
            'create' => Pages\CreateServiceTemplate::route('/create'),
            'edit' => Pages\EditServiceTemplate::route('/{record}/edit'),
        ];
    }
}
