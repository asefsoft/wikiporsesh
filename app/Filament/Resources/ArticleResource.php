<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArticleResource\Pages;
use App\Forms\Components\AdvanceTextArea;
use App\Forms\Components\AdvanceTextInput;
use App\Forms\Components\DisplayImage;
use App\Forms\Components\DisplayVideo;
use App\Models\Article;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\HtmlString;
use phpDocumentor\Reflection\Types\This;
use Str;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function getEloquentQuery(): Builder {
        if(str_contains(Route::getCurrentRoute()->getName(), ".edit"))
            return parent::getEloquentQuery();

        return static::getModel()::query()->where('is_skipped', 0);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                DisplayImage::make('image_url')->setDisplayWidth(500)->label("")
                    ->setDisplayAlign('center')->columnSpan(2),
                AdvanceTextInput::make('title_fa')->label("عنوان مقاله")
                    ->maxLength(300)->columnSpan(2)
                    ->hint(fn($record) => getLeftOrderHtmlString($record?->title_en)),
                AdvanceTextarea::make('description_fa')->label("توضیحات مقاله")
                    ->required()->columnSpan(['default' => 2,'lg' => 1])
                    ->maxLength(500)
                    ->hint(fn($record) => getLeftOrderHtmlString($record?->description_en)),
                AdvanceTextarea::make('tips_fa')->columnSpan(['default' => 2,'lg' => 1])
                    ->maxLength(500)->label("نکات")
                    ->hint(fn($record) => getLeftOrderHtmlString($record?->tips_en)),
                AdvanceTextarea::make('warnings_fa')->columnSpan(['default' => 2,'lg' => 1])
                    ->maxLength(500)->label("هشدار ها")
                    ->hint(fn($record) => getLeftOrderHtmlString($record?->warnings_en)),
//                Forms\Components\TextInput::make('image_url')
//                    ->maxLength(300),
                Forms\Components\TextInput::make('steps_type')->columnSpan(['default' => 2,'lg' => 1])
                    ->disabled()
                    ->maxLength(30),

                // Sections
                Repeater::make('sections')
                    ->label(fn (array $state): ?string => sprintf("بخش های مقاله (%s)", count($state)))
                    ->columnSpan(2)
                    ->relationship()
                    ->itemLabel(fn (array $state): ?string => 'بخش ' . ($state['order'] ?? null))
                    ->disableItemDeletion()->disableItemCreation()->disableItemMovement()
                    ->schema([
                        AdvanceTextInput::make('title_fa')->required()->label("عنوان بخش")
                            ->hint(fn($record) => $record?->title_en),
                        // Steps
                        Repeater::make('steps')
                            ->label("مراحل")
                            ->columnSpan(2)
                            ->relationship()
                            ->orderable('order')
                            ->mutateRelationshipDataBeforeCreateUsing(function ($data, $record){
                                // to save a step we also need article id foreign key, so we will add it here
                                $articleId = $record->article_id ?? $record?->article->id;
                                $data['article_id'] = $articleId;
                                return $data;
                            })
                            ->collapsed()
                            ->itemLabel(fn (array $state): ?string => 'مرحله ' . ($state['order'] ?? null))
                            //->disableItemDeletion()->disableItemCreation()->disableItemMovement()
                            ->schema([
//                                Forms\Components\TextInput::make('order')->label("مرحله")
//                                    ->required()->disabled()->columnSpan(1),

                                // show video if exists, otherwise show image
                                DisplayImage::make('image_url')->setDisplayAlign('center')
                                    ->hidden(fn($record) => !empty($record->video_url))
                                                               ->setDisplayWidth(400)->label(""),
                                // show video if exists, otherwise show image
                                DisplayVideo::make('video_url')->setDisplayAlign('center')
                                    ->hidden(fn($record) => empty($record->video_url))
                                    ->setDisplayWidth(400)->label(""),

                                AdvanceTextarea::make('content_fa')->label("توضیحات")->required()
                                    ->rows(fn($record) => static::getRowsCountOfTextArea($record->content_fa))
                                    ->hint(fn($record) => getLeftOrderHtmlString($record?->content_en)),
                            ])
                    ])

//                Forms\Components\TextInput::make('source_views'),
//                Forms\Components\Toggle::make('rate')
//                    ->required(),
//                Forms\Components\Toggle::make('visible')
//                    ->required(),
//                Forms\Components\Toggle::make('is_featured')
//                    ->required(),
//                Forms\Components\DateTimePicker::make('edited_at'),
//                Forms\Components\DateTimePicker::make('published_at'),
//                Forms\Components\DateTimePicker::make('last_crawled_at'),
            ]);
    }

    private static function getRowsCountOfTextArea($content) : int {
        $contentLen = Str::length($content);
        $rows = round($contentLen / 120);
        $rows = max(2, $rows);
        $rows = min(10, $rows);
        return $rows;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')->width('140px')->height('80px')->label("Poster"),
                Tables\Columns\TextColumn::make('title_fa')->limit(50, "")->searchable()->sortable()
                    ->url(fn ($record): string => $record->getSourceSiteUrl()),
                Tables\Columns\IconColumn::make('is_skipped')->boolean()->sortable()
                                         ->label("نادیده گرفتن")
                                         ->action(function($record) {
                                             $record->is_skipped = $record->is_skipped > 0 ? 0 : 1;
                                             $record->save();
                                             Filament::notify('success', 'تغییر انجام شد.');
                                         }),
                Tables\Columns\TextColumn::make('categories')->wrap()
                    ->formatStateUsing(function ($state) {
                        return $state?->pluck('name_fa')->join(', ');
                    })
                    ,
                Tables\Columns\TextColumn::make('total_sections')->sortable()->label('Sections'),
                Tables\Columns\TextColumn::make('total_steps')->sortable()->label('Steps'),
                Tables\Columns\IconColumn::make('info')
                    ->options(['heroicon-o-light-bulb'])
                    ->label("اطلاعات")->colors(['primary'])
                    ->url(fn($record) => $record?->getArticleDisplayUrl(), true)
                    ->tooltip(fn($record) => $record?->getBriefInfoOfArticle()),
                Tables\Columns\TextColumn::make('description_fa')->limit(50)->wrap()->searchable()->visible(false),
                Tables\Columns\TextColumn::make('tips_fa')->limit(50)->wrap()->searchable()->visible(false),
                Tables\Columns\TextColumn::make('warnings_fa')->limit(50)->wrap()->searchable()->visible(false),
                //Tables\Columns\TextColumn::make('steps_type')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('source_views')
                    ->formatStateUsing(fn ($state) => number_format($state))->sortable(),
                Tables\Columns\IconColumn::make('is_featured')->boolean()->sortable(),
                Tables\Columns\IconColumn::make('is_translate_designated')->boolean()->sortable()
                    ->label("منتخب ترجمه")
                    ->action(function($record) {
                        $record->is_translate_designated = $record->is_translate_designated > 0 ? 0 : 1;
                        $record->save();
                        Filament::notify('success', 'تغییر انجام شد.');
                    }),

                Tables\Columns\IconColumn::make('published_at')->sortable()
                    ->label("انتشار")
                    ->action(function($record) {
                        $record->published_at = empty($record->published_at) ? now() : null;
                        $record->save();
                        Filament::notify('success', 'تغییر انجام شد.');
                    })
                    ->options(fn($record) => empty($record->published_at) ? ['heroicon-o-x-circle'] : ['heroicon-o-check-circle'])
                    ->colors(fn($record) => empty($record->published_at) ? ['danger'] : ['success']),
//                Tables\Columns\TextColumn::make('published_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('last_crawled_at')->wrap()->sortable()
                    ->tooltip(fn($record) => getDateString($record?->last_crawled_at, "jalali"))
                    ->formatStateUsing(fn($state) => getDateString($state)),
                Tables\Columns\TextColumn::make('created_at')->wrap()->sortable()
                    ->tooltip(fn($record) => getDateString($record?->created_at, "jalali"))
                    ->formatStateUsing(fn($state) => getDateString($state)),
//                Tables\Columns\TextColumn::make('updated_at')->wrap()->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
