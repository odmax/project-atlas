<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use App\Services\ApiClientFactory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?int $navigationSort = 99;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('settings_tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('platform')
                            ->label('Platform')
                            ->icon('heroicon-o-cpu-chip')
                            ->schema([
                                Forms\Components\Section::make('General Settings')
                                    ->schema([
                                        Forms\Components\TextInput::make('settings.platform.app_name')
                                            ->label('Application Name')
                                            ->default('Atlas')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('settings.platform.app_url')
                                            ->label('Application URL')
                                            ->url()
                                            ->default('http://localhost:8000'),
                                        Forms\Components\TextInput::make('settings.platform.timezone')
                                            ->label('Timezone')
                                            ->default('UTC'),
                                    ]),
                                Forms\Components\Section::make('Sync Settings')
                                    ->schema([
                                        Forms\Components\TextInput::make('settings.sync.interval_minutes')
                                            ->label('Sync Interval (Minutes)')
                                            ->numeric()
                                            ->default(5),
                                        Forms\Components\Checkbox::make('settings.sync.auto_sync')
                                            ->label('Enable Auto Sync')
                                            ->default(true),
                                        Forms\Components\TextInput::make('settings.sync.max_retries')
                                            ->label('Max Retries')
                                            ->numeric()
                                            ->default(3),
                                    ]),
                                Forms\Components\Section::make('Notification Settings')
                                    ->schema([
                                        Forms\Components\Checkbox::make('settings.notifications.email_on_failure')
                                            ->label('Email on Sync Failure'),
                                        Forms\Components\Checkbox::make('settings.notifications.email_on_success')
                                            ->label('Email on Sync Success'),
                                        Forms\Components\TextInput::make('settings.notifications.email_recipients')
                                            ->label('Email Recipients')
                                            ->helperText('Comma separated email addresses'),
                                    ]),
                            ]),
                        Forms\Components\Tabs\Tab::make('connectors')
                            ->label('Connectors')
                            ->icon('heroicon-o-globe')
                            ->schema([
                                Forms\Components\Section::make('Connector Defaults')
                                    ->schema([
                                        Forms\Components\TextInput::make('settings.connector.default_cpanel_domain')
                                            ->label('Default cPanel Domain')
                                            ->placeholder('example.com'),
                                        Forms\Components\TextInput::make('settings.connector.default_timeout')
                                            ->label('Default Timeout (seconds)')
                                            ->numeric()
                                            ->default(30),
                                        Forms\Components\Checkbox::make('settings.connector.default_ssl_verify')
                                            ->label('SSL Verify by Default')
                                            ->default(true),
                                    ]),
                                Forms\Components\Section::make('Test Connection')
                                    ->description('Test your cPanel/WordPress connection')
                                    ->schema([
                                        Forms\Components\Select::make('test_connector_id')
                                            ->label('Select Connector')
                                            ->options(\App\Models\Connector::where('is_active', true)->pluck('name', 'id')),
                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('test_connection')
                                                ->label('Test Connection')
                                                ->action(function (array $data) {
                                                    if (empty($data['test_connector_id'])) {
                                                        Notification::make()
                                                            ->title('Error')
                                                            ->body('Please select a connector')
                                                            ->warning()
                                                            ->send();
                                                        return;
                                                    }
                                                    
                                                    $connector = \App\Models\Connector::find($data['test_connector_id']);
                                                    if ($connector) {
                                                        try {
                                                            $client = ApiClientFactory::create($connector);
                                                            $result = $client->testConnection();
                                                            
                                                            Notification::make()
                                                                ->title($result ? 'Connection Successful' : 'Connection Failed')
                                                                ->body($result ? 'Successfully connected to ' . $connector->name : 'Could not connect to ' . $connector->name)
                                                                ->color($result ? 'success' : 'danger')
                                                                ->send();
                                                        } catch (\Exception $e) {
                                                            Notification::make()
                                                                ->title('Connection Error')
                                                                ->body($e->getMessage())
                                                                ->danger()
                                                                ->send();
                                                        }
                                                    }
                                                })
                                                ->color('success'),
                                        ]),
                                    ]),
                            ]),
                        Forms\Components\Tabs\Tab::make('admin')
                            ->label('Admin Profile')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Forms\Components\Section::make('Profile Information')
                                    ->schema([
                                        Forms\Components\TextInput::make('settings.admin.name')
                                            ->label('Admin Name')
                                            ->default('Admin'),
                                        Forms\Components\TextInput::make('settings.admin.email')
                                            ->label('Admin Email')
                                            ->email()
                                            ->default('admin@atlas.test'),
                                        Forms\Components\TextInput::make('settings.admin.phone')
                                            ->label('Phone Number')
                                            ->tel(),
                                    ]),
                                Forms\Components\Section::make('Security')
                                    ->schema([
                                        Forms\Components\Checkbox::make('settings.admin.force_password_change')
                                            ->label('Force Password Change'),
                                        Forms\Components\TextInput::make('settings.admin.session_timeout')
                                            ->label('Session Timeout (minutes)')
                                            ->numeric()
                                            ->default(60),
                                    ]),
                                Forms\Components\Section::make('Preferences')
                                    ->schema([
                                        Forms\Components\Select::make('settings.admin.default_theme')
                                            ->label('Default Theme')
                                            ->options([
                                                'light' => 'Light',
                                                'dark' => 'Dark',
                                                'system' => 'System',
                                            ])
                                            ->default('system'),
                                        Forms\Components\Select::make('settings.admin.default_language')
                                            ->label('Default Language')
                                            ->options([
                                                'en' => 'English',
                                                'es' => 'Spanish',
                                                'fr' => 'French',
                                            ])
                                            ->default('en'),
                                    ]),
                            ]),
                        Forms\Components\Tabs\Tab::make('custom')
                            ->label('Custom Settings')
                            ->icon('heroicon-o-code-bracket')
                            ->schema([
                                Forms\Components\Section::make('Custom Key-Value Settings')
                                    ->description('Add custom settings using key-value pairs')
                                    ->schema([
                                        Forms\Components\TextInput::make('key')
                                            ->label('Setting Key')
                                            ->maxLength(255)
                                            ->helperText('e.g., my_custom_setting'),
                                        Forms\Components\Textarea::make('value')
                                            ->label('Value')
                                            ->rows(3),
                                    ]),
                            ]),
                    ]),
            ])
            ->action(function (array $data) {
                foreach ($data['settings'] ?? [] as $category => $fields) {
                    if (is_array($fields)) {
                        foreach ($fields as $key => $value) {
                            $fullKey = "{$category}.{$key}";
                            Setting::updateOrCreate(['key' => $fullKey], ['value' => $value]);
                        }
                    }
                }
                
                Notification::make()
                    ->title('Settings Saved')
                    ->body('Your settings have been saved successfully.')
                    ->success()
                    ->send();
            });
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
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
            'index' => Pages\ManageSettings::route('/'),
        ];
    }
}
