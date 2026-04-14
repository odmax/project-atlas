<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use App\Models\ServiceTemplate;
use App\Models\LinkedAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Service Template')
                    ->description('Optionally select a service template to provision services for this user')
                    ->schema([
                        Forms\Components\Select::make('service_template_id')
                            ->label('Service Template')
                            ->options(ServiceTemplate::active()->pluck('name', 'id'))
                            ->nullable()
                            ->helperText('Select a template to provision services during onboarding'),
                    ])
                    ->collapsible(),
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\TextInput::make('uuid')
                            ->disabled(),
                        Forms\Components\TextInput::make('first_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('middle_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('display_name')
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Employment')
                    ->schema([
                        Forms\Components\TextInput::make('employee_code')
                            ->unique(ignorable: fn ($record) => $record)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('primary_email')
                            ->email()
                            ->required()
                            ->unique(ignorable: fn ($record) => $record)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('department')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('job_title')
                            ->maxLength(255),
                        Forms\Components\Select::make('employment_status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'suspended' => 'Suspended',
                                'terminated' => 'Terminated',
                            ])
                            ->required(),
                        Forms\Components\Select::make('lifecycle_status')
                            ->options([
                                'pending' => 'Pending',
                                'onboarding' => 'Onboarding',
                                'active' => 'Active',
                                'offboarding' => 'Offboarding',
                                'archived' => 'Archived',
                            ])
                            ->required(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes'),
                    ]),
                Forms\Components\Section::make('Authentication')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required()
                            ->maxLength(255),
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
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('middle_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('display_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('employee_code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('primary_email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('department')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('job_title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('employment_status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'inactive',
                        'danger' => 'suspended',
                        'gray' => 'terminated',
                    ])
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('lifecycle_status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'onboarding',
                        'success' => 'active',
                        'danger' => 'offboarding',
                        'gray' => 'archived',
                    ])
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
                Tables\Filters\SelectFilter::make('employment_status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'suspended' => 'Suspended',
                        'terminated' => 'Terminated',
                    ]),
                Tables\Filters\SelectFilter::make('lifecycle_status')
                    ->options([
                        'pending' => 'Pending',
                        'onboarding' => 'Onboarding',
                        'active' => 'Active',
                        'offboarding' => 'Offboarding',
                        'archived' => 'Archived',
                    ]),
                Tables\Filters\SelectFilter::make('department')
                    ->options([
                        'IT' => 'IT',
                        'HR' => 'HR',
                        'Sales' => 'Sales',
                        'Marketing' => 'Marketing',
                        'Finance' => 'Finance',
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
