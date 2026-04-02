<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LinkedAccountResource\Pages;
use App\Filament\Resources\LinkedAccountResource\RelationManagers;
use App\Models\LinkedAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LinkedAccountResource extends Resource
{
    protected static ?string $model = LinkedAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationLabel = 'Linked Accounts';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'display_name')
                            ->required(),
                        Forms\Components\Select::make('connector_id')
                            ->relationship('connector', 'name')
                            ->required(),
                        Forms\Components\Select::make('account_type')
                            ->options([
                                'cpanel_email' => 'cPanel Email',
                                'cpanel_ftp' => 'cPanel FTP',
                                'wordpress_user' => 'WordPress User',
                            ])
                            ->required(),
                    ]),
                Forms\Components\Section::make('External Account Details')
                    ->schema([
                        Forms\Components\TextInput::make('external_id')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('external_username')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('external_email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('external_role')
                            ->maxLength(255),
                    ]),
                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Select::make('desired_state')
                            ->options([
                                'active' => 'Active',
                                'suspended' => 'Suspended',
                                'deleted' => 'Deleted',
                            ])
                            ->required(),
                        Forms\Components\Select::make('actual_state')
                            ->options([
                                'active' => 'Active',
                                'suspended' => 'Suspended',
                                'deleted' => 'Deleted',
                                'unknown' => 'Unknown',
                            ])
                            ->disabled(),
                        Forms\Components\Checkbox::make('is_suspended'),
                        Forms\Components\Select::make('provisioning_status')
                            ->options([
                                'pending' => 'Pending',
                                'provisioning' => 'Provisioning',
                                'active' => 'Active',
                                'failed' => 'Failed',
                                'deprovisioned' => 'Deprovisioned',
                            ])
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('last_synced_at')
                            ->disabled(),
                        Forms\Components\TextInput::make('last_sync_status')
                            ->disabled(),
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
                Tables\Columns\TextColumn::make('user.display_name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('connector.name')
                    ->label('Connector')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('account_type')
                    ->colors([
                        'primary' => 'cpanel_email',
                        'info' => 'cpanel_ftp',
                        'success' => 'wordpress_user',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('external_id')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('external_username')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('external_email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('desired_state')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'suspended',
                        'danger' => 'deleted',
                    ])
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('actual_state')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'suspended',
                        'danger' => 'deleted',
                        'gray' => 'unknown',
                    ])
                    ->sortable(),
                Tables\Columns\BooleanColumn::make('is_suspended')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('provisioning_status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'provisioning',
                        'success' => 'active',
                        'danger' => 'failed',
                        'gray' => 'deprovisioned',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('external_role')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_synced_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('last_sync_status')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Sync Status')
                    ->badge()
                    ->colors([
                        'success' => 'success',
                        'danger' => 'failed',
                        'warning' => 'pending',
                    ]),
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
                Tables\Filters\SelectFilter::make('user_id')
                    ->relationship('user', 'display_name')
                    ->label('User'),
                Tables\Filters\SelectFilter::make('connector_id')
                    ->relationship('connector', 'name')
                    ->label('Connector'),
                Tables\Filters\SelectFilter::make('account_type')
                    ->options([
                        'cpanel_email' => 'cPanel Email',
                        'cpanel_ftp' => 'cPanel FTP',
                        'wordpress_user' => 'WordPress User',
                    ]),
                Tables\Filters\SelectFilter::make('desired_state')
                    ->options([
                        'active' => 'Active',
                        'suspended' => 'Suspended',
                        'deleted' => 'Deleted',
                    ]),
                Tables\Filters\SelectFilter::make('provisioning_status')
                    ->options([
                        'pending' => 'Pending',
                        'provisioning' => 'Provisioning',
                        'active' => 'Active',
                        'failed' => 'Failed',
                        'deprovisioned' => 'Deprovisioned',
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
            'index' => Pages\ListLinkedAccounts::route('/'),
            'create' => Pages\CreateLinkedAccount::route('/create'),
            'edit' => Pages\EditLinkedAccount::route('/{record}/edit'),
        ];
    }
}
