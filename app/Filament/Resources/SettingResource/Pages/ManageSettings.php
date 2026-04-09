<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use App\Models\Connector;
use App\Models\Setting;
use App\Services\ApiClientFactory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;

class ManageSettings extends Page
{
    protected static string $resource = SettingResource::class;

    protected static string $view = 'filament.resources.setting-resource.pages.manage-settings';

    protected ?string $heading = 'Settings';

    public $data = [];

    public function mount(): void
    {
        $this->fillForm();
    }

    public function fillForm(): void
    {
        $settings = Setting::all()->pluck('value', 'key');
        $flattened = [];
        foreach ($settings as $key => $value) {
            $flattened[$key] = $value;
        }
        
        $nested = [];
        foreach ($flattened as $key => $value) {
            if (str_contains($key, '.')) {
                $parts = explode('.', $key);
                $current = &$nested;
                foreach ($parts as $part) {
                    if (!isset($current[$part])) {
                        $current[$part] = [];
                    }
                    $current = &$current[$part];
                }
                $current = $value;
            } else {
                $nested[$key] = $value;
            }
        }
        
        $defaults = [
            'general' => [
                'app_name' => 'Atlas',
                'app_tagline' => '',
                'support_email' => '',
                'logo_url' => '',
                'primary_color' => '#f59e0b',
                'default_locale' => 'en',
            ],
            'platform' => [
                'app_env' => 'local',
                'debug_mode' => false,
                'timezone' => 'UTC',
                'session_lifetime' => 120,
                'max_login_attempts' => 5,
                'lockout_duration' => 15,
                'two_factor_required' => false,
                'force_password_change' => false,
                'maintenance_mode' => false,
                'maintenance_message' => '',
            ],
            'sync' => [
                'enabled' => true,
                'interval' => '5',
                'batch_size' => 100,
                'max_retries' => 3,
                'retry_delay' => 60,
                'notify_on_failure' => false,
                'notify_on_success' => false,
                'notification_email' => '',
            ],
            'connector' => [
                'default_timeout' => 30,
                'default_ssl_verify' => true,
                'default_api_version' => 'v1',
            ],
            'admin' => [
                'name' => 'Admin',
                'email' => 'admin@atlas.test',
                'phone' => '',
                'force_password_change' => false,
                'default_theme' => 'system',
                'default_language' => 'en',
            ],
        ];
        
        foreach ($defaults as $category => $fields) {
            foreach ($fields as $key => $defaultValue) {
                if (!isset($nested[$category][$key])) {
                    $nested[$category][$key] = $defaultValue;
                }
            }
        }
        
        $this->data = $nested;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('test_connector'),
                Forms\Components\Tabs::make('settings_tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('general')
                            ->label('General')
                            ->icon('heroicon-o-cog')
                            ->schema([
                                Forms\Components\Section::make('Application Identity')
                                    ->description('Basic information about your application')
                                    ->schema([
                                        Forms\Components\TextInput::make('general.app_name')
                                            ->label('Application Name')
                                            ->default('Atlas')
                                            ->maxLength(255)
                                            ->required(),
                                        Forms\Components\TextInput::make('general.app_tagline')
                                            ->label('Tagline')
                                            ->maxLength(255)
                                            ->placeholder('User Identity Management Platform'),
                                        Forms\Components\TextInput::make('general.support_email')
                                            ->label('Support Email')
                                            ->email()
                                            ->placeholder('support@example.com'),
                                    ]),
                            ]),
                        Forms\Components\Tabs\Tab::make('platform')
                            ->label('Platform')
                            ->icon('heroicon-o-computer-desktop')
                            ->schema([
                                Forms\Components\Section::make('Environment')
                                    ->description('Configure platform environment settings')
                                    ->schema([
                                        Forms\Components\Select::make('platform.app_env')
                                            ->label('Environment')
                                            ->options([
                                                'local' => 'Local',
                                                'development' => 'Development',
                                                'staging' => 'Staging',
                                                'production' => 'Production',
                                            ])
                                            ->default('local'),
                                        Forms\Components\Checkbox::make('platform.debug_mode')
                                            ->label('Debug Mode')
                                            ->helperText('Enable detailed error messages'),
                                        Forms\Components\TextInput::make('platform.timezone')
                                            ->label('Default Timezone')
                                            ->default('UTC')
                                            ->helperText('e.g., America/New_York, Europe/London'),
                                    ]),
                                Forms\Components\Section::make('Session & Security')
                                    ->schema([
                                        Forms\Components\TextInput::make('platform.session_lifetime')
                                            ->label('Session Lifetime (minutes)')
                                            ->numeric()
                                            ->default(120),
                                        Forms\Components\TextInput::make('platform.max_login_attempts')
                                            ->label('Max Login Attempts')
                                            ->numeric()
                                            ->default(5)
                                            ->helperText('Before account lockout'),
                                        Forms\Components\TextInput::make('platform.lockout_duration')
                                            ->label('Lockout Duration (minutes)')
                                            ->numeric()
                                            ->default(15),
                                        Forms\Components\Checkbox::make('platform.two_factor_required')
                                            ->label('Require Two-Factor Authentication'),
                                        Forms\Components\Checkbox::make('platform.force_password_change')
                                            ->label('Force Password Change Every 90 Days'),
                                    ]),
                                Forms\Components\Section::make('Maintenance')
                                    ->schema([
                                        Forms\Components\Checkbox::make('platform.maintenance_mode')
                                            ->label('Enable Maintenance Mode')
                                            ->helperText('Restricts access to administrators only'),
                                        Forms\Components\Textarea::make('platform.maintenance_message')
                                            ->label('Maintenance Message')
                                            ->rows(3)
                                            ->placeholder('We are performing scheduled maintenance...'),
                                    ]),
                            ]),
                        Forms\Components\Tabs\Tab::make('sync')
                            ->label('Sync')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                Forms\Components\Section::make('Automatic Sync')
                                    ->description('Configure automatic data synchronization')
                                    ->schema([
                                        Forms\Components\Checkbox::make('sync.enabled')
                                            ->label('Enable Automatic Sync')
                                            ->default(true),
                                        Forms\Components\Select::make('sync.interval')
                                            ->label('Sync Interval')
                                            ->options([
                                                '1' => 'Every 1 minute',
                                                '5' => 'Every 5 minutes',
                                                '15' => 'Every 15 minutes',
                                                '30' => 'Every 30 minutes',
                                                '60' => 'Every hour',
                                            ])
                                            ->default('5'),
                                        Forms\Components\TextInput::make('sync.batch_size')
                                            ->label('Batch Size')
                                            ->numeric()
                                            ->default(100)
                                            ->helperText('Records to process per sync cycle'),
                                    ]),
                                Forms\Components\Section::make('Retry Policy')
                                    ->schema([
                                        Forms\Components\TextInput::make('sync.max_retries')
                                            ->label('Max Retries')
                                            ->numeric()
                                            ->default(3),
                                        Forms\Components\TextInput::make('sync.retry_delay')
                                            ->label('Retry Delay (seconds)')
                                            ->numeric()
                                            ->default(60),
                                    ]),
                                Forms\Components\Section::make('Notifications')
                                    ->schema([
                                        Forms\Components\Checkbox::make('sync.notify_on_failure')
                                            ->label('Notify on Sync Failure'),
                                        Forms\Components\Checkbox::make('sync.notify_on_success')
                                            ->label('Notify on Sync Success'),
                                        Forms\Components\TextInput::make('sync.notification_email')
                                            ->label('Notification Email')
                                            ->email()
                                            ->placeholder('admin@example.com'),
                                    ]),
                            ]),
                        Forms\Components\Tabs\Tab::make('connectors')
                            ->label('Connectors')
                            ->icon('heroicon-o-server')
                            ->schema([
                                Forms\Components\Section::make('Connector Defaults')
                                    ->description('Default settings for new connectors')
                                    ->schema([
                                        Forms\Components\TextInput::make('connector.default_timeout')
                                            ->label('Connection Timeout (seconds)')
                                            ->numeric()
                                            ->default(30),
                                        Forms\Components\Checkbox::make('connector.default_ssl_verify')
                                            ->label('Verify SSL by Default')
                                            ->default(true),
                                        Forms\Components\TextInput::make('connector.default_api_version')
                                            ->label('Default API Version')
                                            ->default('v1'),
                                    ]),
                                Forms\Components\Section::make('Test Connection')
                                    ->description('Test your cPanel/WordPress connection')
                                    ->schema([
                                        Forms\Components\Select::make('test_connector')
                                            ->label('Select Connector')
                                            ->options(Connector::all()->pluck('name', 'id')->toArray())
                                            ->helperText('Select a connector to test the connection'),
                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('test_connection')
                                                ->label('Test Connection')
                                                ->action(function () {
                                                    $connectorId = $this->data['test_connector'] ?? null;
                                                    
                                                    if (empty($connectorId)) {
                                                        Notification::make()
                                                            ->title('Error')
                                                            ->body('Please select a connector. Available: ' . implode(', ', Connector::pluck('name', 'id')->toArray()))
                                                            ->warning()
                                                            ->send();
                                                        return;
                                                    }
                                                    
                                                    $connector = Connector::find($connectorId);
                                                    if (!$connector) {
                                                        Notification::make()
                                                            ->title('Error')
                                                            ->body('Connector not found')
                                                            ->danger()
                                                            ->send();
                                                        return;
                                                    }
                                                    
                                                    try {
                                                        $client = ApiClientFactory::create($connector);
                                                        $result = $client->testConnection();
                                                        
                                                        if ($result) {
                                                            Notification::make()
                                                                ->title('Connection Successful')
                                                                ->body('Successfully connected to ' . $connector->name)
                                                                ->success()
                                                                ->send();
                                                        } else {
                                                            $errorMsg = 'Could not connect to ' . $connector->name;
                                                            if (method_exists($client, 'getLastError')) {
                                                                $lastError = $client->getLastError();
                                                                if (is_array($lastError)) {
                                                                    $errorMsg .= '. Method: ' . ($lastError['method'] ?? 'unknown') . ' - ' . ($lastError['message'] ?? 'Unknown error');
                                                                } else {
                                                                    $errorMsg .= '. ' . $lastError;
                                                                }
                                                            }
                                                            $errorMsg .= '. Check credentials or server API permissions.';
                                                            
                                                            Notification::make()
                                                                ->title('Connection Failed')
                                                                ->body($errorMsg)
                                                                ->danger()
                                                                ->send();
                                                        }
                                                    } catch (\Exception $e) {
                                                        Notification::make()
                                                            ->title('Connection Error')
                                                            ->body('Error: ' . $e->getMessage())
                                                            ->danger()
                                                            ->send();
                                                    }
                                                })
                                                ->color('success'),
                                        ]),
                                    ]),
                            ]),
                        Forms\Components\Tabs\Tab::make('admin')
                            ->label('Admin')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                Forms\Components\Section::make('Admin Profile')
                                    ->description('Your administrator account settings')
                                    ->schema([
                                        Forms\Components\TextInput::make('admin.name')
                                            ->label('Admin Name')
                                            ->default('Admin'),
                                        Forms\Components\TextInput::make('admin.email')
                                            ->label('Admin Email')
                                            ->email()
                                            ->default('admin@atlas.test'),
                                        Forms\Components\TextInput::make('admin.phone')
                                            ->label('Phone Number')
                                            ->tel(),
                                    ]),
                                Forms\Components\Section::make('Security & Preferences')
                                    ->schema([
                                        Forms\Components\Checkbox::make('admin.force_password_change')
                                            ->label('Force Password Change'),
                                        Forms\Components\Select::make('admin.default_theme')
                                            ->label('Default Theme')
                                            ->options([
                                                'light' => 'Light',
                                                'dark' => 'Dark',
                                                'system' => 'System',
                                            ])
                                            ->default('system'),
                                        Forms\Components\Select::make('admin.default_language')
                                            ->label('Default Language')
                                            ->options([
                                                'en' => 'English',
                                                'es' => 'Spanish',
                                                'fr' => 'French',
                                            ])
                                            ->default('en'),
                                    ]),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Save Settings')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->data;
        
        $flattened = [];
        $this->flattenData($data, '', $flattened);
        
        foreach ($flattened as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        
        Notification::make()
            ->title('Settings Saved')
            ->body('Your settings have been saved successfully.')
            ->success()
            ->send();
    }
    
    private function flattenData(array $data, string $prefix, array &$result): void
    {
        foreach ($data as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;
            if (is_array($value)) {
                $this->flattenData($value, $fullKey, $result);
            } else {
                $result[$fullKey] = $value;
            }
        }
    }
}
