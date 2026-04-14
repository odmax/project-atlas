<?php

namespace App\Filament\Resources\PolicyResource;

use App\Models\Policy;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PolicyResource extends Resource
{
    protected static ?string $model = Policy::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Policies';

    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('key')
                    ->required()
                    ->unique(ignorable: fn ($record) => $record)
                    ->maxLength(255)
                    ->helperText('Unique identifier (e.g., wordpress.suspend.rotate_password)')
                    ->disabled(fn ($record) => $record !== null),
                Forms\Components\Select::make('category')
                    ->options([
                        'suspension' => 'Suspension',
                        'wordpress' => 'WordPress',
                        'cpanel' => 'cPanel',
                        'jobs' => 'Jobs',
                        'reconciliation' => 'Reconciliation',
                        'linked_accounts' => 'Linked Accounts',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('value_json')
                    ->required()
                    ->rows(3)
                    ->helperText('JSON value: true, false, 123, "string"'),
                Forms\Components\Textarea::make('description')
                    ->rows(2),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->sortable()
                    ->code()
                    ->limit(40),
                Tables\Columns\BadgeColumn::make('category')
                    ->colors([
                        'primary' => 'wordpress',
                        'success' => 'cpanel',
                        'warning' => 'jobs',
                        'danger' => 'suspension',
                        'info' => 'reconciliation',
                        'gray' => 'linked_accounts',
                    ]),
                Tables\Columns\TextColumn::make('value_json')
                    ->label('Value')
                    ->limit(30)
                    ->formatStateUsing(fn ($state) => is_bool($state) ? ($state ? 'true' : 'false') : json_encode($state)),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'suspension' => 'Suspension',
                        'wordpress' => 'WordPress',
                        'cpanel' => 'cPanel',
                        'jobs' => 'Jobs',
                        'reconciliation' => 'Reconciliation',
                        'linked_accounts' => 'Linked Accounts',
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
            'index' => Pages\ListPolicies::route('/'),
            'edit' => Pages\EditPolicy::route('/{record}/edit'),
        ];
    }
}
