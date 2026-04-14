<?php

namespace App\Filament\Resources\SyncJobResource;

use App\Models\SyncJob;
use App\Services\SyncJobService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SyncJobResource extends Resource
{
    protected static ?string $model = SyncJob::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationLabel = 'Job Monitor';

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'job_type';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('job_type')
                    ->disabled(),
                Forms\Components\TextInput::make('status')
                    ->disabled(),
                Forms\Components\Textarea::make('last_error')
                    ->disabled(),
                Forms\Components\KeyValue::make('metadata_json')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('job_type')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'pending',
                        'info' => 'running',
                        'success' => 'completed',
                        'danger' => 'failed',
                        'warning' => 'queued_for_retry',
                    ]),
                Tables\Columns\TextColumn::make('connector.name')
                    ->label('Connector')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('user.primary_email')
                    ->label('User')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('direction')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('attempts_count')
                    ->label('Attempts')
                    ->counts('attempts'),
                Tables\Columns\TextColumn::make('last_error')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->last_error),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'running' => 'Running',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'queued_for_retry' => 'Queued for Retry',
                    ]),
                Tables\Filters\SelectFilter::make('job_type')
                    ->options([
                        'provision' => 'Provision',
                        'deprovision' => 'Deprovision',
                        'sync' => 'Sync',
                        'suspend' => 'Suspend',
                        'unsuspend' => 'Unsuspend',
                    ]),
                Tables\Filters\SelectFilter::make('connector_id')
                    ->relationship('connector', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('retry')
                    ->label('Queue Retry')
                    ->visible(fn (SyncJob $record) => $record->canRetry())
                    ->action(function (SyncJob $record) {
                        app(SyncJobService::class)->queueForRetry($record);
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSyncJobs::route('/'),
            'view' => Pages\ViewSyncJob::route('/{record}'),
        ];
    }
}
