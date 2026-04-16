<?php

namespace App\Filament\Resources;

use App\Models\AutomationRule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\AutomationRuleResource\Pages;

class AutomationRuleResource extends Resource
{
    protected static ?string $model = AutomationRule::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static ?string $navigationLabel = 'Automation Rules';

    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('trigger_type')
                    ->options([
                        'user.created' => 'User Created',
                        'reconciliation.drift_detected' => 'Reconciliation Drift',
                        'job.failed' => 'Job Failed',
                    ])
                    ->required(),
                Forms\Components\KeyValue::make('condition_json')
                    ->keyLabel('Field')
                    ->valueLabel('Value'),
                Forms\Components\KeyValue::make('action_json')
                    ->keyLabel('Action Type')
                    ->valueLabel('Parameters'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('trigger_type')
                    ->colors([
                        'success' => 'user.created',
                        'warning' => 'reconciliation.drift_detected',
                        'danger' => 'job.failed',
                    ]),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('last_run_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('runs_count')
                    ->label('Runs')
                    ->counts('runs'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('trigger_type')
                    ->options([
                        'user.created' => 'User Created',
                        'reconciliation.drift_detected' => 'Reconciliation Drift',
                        'job.failed' => 'Job Failed',
                    ]),
                Tables\Filters\Filter::make('active')
                    ->query(fn ($query) => $query->where('is_active', true))
                    ->label('Active Only'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListAutomationRules::route('/'),
            'create' => Pages\CreateAutomationRule::route('/create'),
            'edit' => Pages\EditAutomationRule::route('/{record}/edit'),
        ];
    }
}