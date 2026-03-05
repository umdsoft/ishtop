<?php

namespace App\Filament\Admin\Resources;

use App\Enums\BannerType;
use App\Filament\Admin\Resources\BannerResource\Pages;
use App\Models\Banner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 40;

    protected static ?string $modelLabel = 'Banner';

    protected static ?string $pluralModelLabel = 'Bannerlar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Asosiy')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Sarlavha')
                            ->required(),
                        Forms\Components\Select::make('type')
                            ->label('Turi')
                            ->options(BannerType::class),
                        Forms\Components\TextInput::make('image_url')
                            ->label('Rasm URL')
                            ->url(),
                        Forms\Components\TextInput::make('image_mobile_url')
                            ->label('Mobil rasm URL')
                            ->url(),
                        Forms\Components\TextInput::make('click_url')
                            ->label('Bosish URL')
                            ->url(),
                        Forms\Components\TextInput::make('click_action')
                            ->label('Bosish amali'),
                    ]),
                Forms\Components\Section::make('Reklama beruvchi')
                    ->schema([
                        Forms\Components\TextInput::make('advertiser_name')
                            ->label('Reklama beruvchi')
                            ->required(),
                        Forms\Components\TextInput::make('advertiser_contact')
                            ->label('Aloqa'),
                    ]),
                Forms\Components\Section::make('Targeting')
                    ->schema([
                        Forms\Components\TagsInput::make('placement')
                            ->label('Joylashuv'),
                        Forms\Components\TagsInput::make('categories')
                            ->label('Kategoriyalar'),
                        Forms\Components\TagsInput::make('cities')
                            ->label('Shaharlar'),
                        Forms\Components\TextInput::make('priority')
                            ->label('Ustuvorlik')
                            ->numeric()
                            ->default(0),
                    ]),
                Forms\Components\Section::make('Limitlar')
                    ->schema([
                        Forms\Components\TextInput::make('max_impressions')
                            ->label('Maks ko\'rishlar')
                            ->numeric(),
                        Forms\Components\TextInput::make('max_clicks')
                            ->label('Maks bosilishlar')
                            ->numeric(),
                        Forms\Components\Select::make('cost_type')
                            ->label('Narx turi')
                            ->options([
                                'cpm' => 'CPM',
                                'cpc' => 'CPC',
                                'flat' => 'Belgilangan',
                            ]),
                        Forms\Components\TextInput::make('cost_amount')
                            ->label('Narx')
                            ->numeric(),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Qoralama',
                                'active' => 'Faol',
                                'paused' => 'To\'xtatilgan',
                                'ended' => 'Tugagan',
                            ]),
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Boshlanish'),
                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('Tugash'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Sarlavha')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Turi')
                    ->badge(),
                Tables\Columns\TextColumn::make('advertiser_name')
                    ->label('Reklama beruvchi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'active' => 'success',
                        'paused' => 'warning',
                        'ended' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('impressions_count')
                    ->label('Ko\'rishlar')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('clicks_count')
                    ->label('Bosilishlar')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cost_amount')
                    ->label('Narx')
                    ->money('UZS'),
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Daromad')
                    ->money('UZS')
                    ->sortable(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Boshlanish')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->label('Tugash')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Qoralama',
                        'active' => 'Faol',
                        'paused' => 'To\'xtatilgan',
                        'ended' => 'Tugagan',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->options(BannerType::class),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'view' => Pages\ViewBanner::route('/{record}'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
