<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Filament\Resources\ArticleResource;
use App\Models\Category;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Layout;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class ListArticles extends ListRecords
{
    protected static string $resource = ArticleResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    // alter table search to add search on invisible fields
    protected function applySearchToTableQuery(Builder $query): Builder
    {
        $query = parent::applySearchToTableQuery($query);
        if (filled($searchQuery = $this->getTableSearchQuery())) {
            //In this example a filter scope on the model is used
            //But you can also customize the query right here!
            $query = $query
                ->orWhere('description_fa', 'like' , "%" . $searchQuery . "%")
                ->orWhere('tips_fa', 'like' , "%" . $searchQuery . "%")
                ->orWhere('warnings_fa', 'like' , "%" . $searchQuery . "%");
        }

    return $query;
    }

    protected function getTableFiltersLayout(): ?string
    {
        return Layout::AboveContent;
    }

    protected function getTableFilters(): array
    {
        return [

            //SelectFilter::make('rate')->label("میزان رضایت")
            //            ->query(function (Builder $query, $data) {
            //                $target = $data['value'] ?? null;
            //
            //                if(!empty($target)) {
            //                    if($target == 'high')
            //                        return $query->where('average_rate', '>=', 80);
            //                    elseif($target == 'middle')
            //                        return $query->whereBetween('average_rate', [50,80]);
            //                    elseif($target == 'low')
            //                        return $query->where('average_rate', '<=', 50);
            //                }
            //
            //                return $query;
            //
            //            })
            //            ->options([
            //                'high' => 'بالا',
            //                'middle' => 'متوسط',
            //                'low' =>  'پایین',
            //            ]),

            // filter all category and sub categories
            SelectFilter::make('category')
                    ->multiple()
                    ->relationship('categories', 'name_fa', function (Builder $query) {
                        return $query->whereNull('parent_category_id');
                    })

                    // get all subcategories
                    ->query(function (Builder $query, $data) {
                        $target = $data['values'] ?? [];

                        // no filter
                        if(count($target) == 0)
                            return $query;

                        $allCategories = Category::getAllCategoriesAndSubCategories($target, 'id');

                        return $query->whereRelation('categories', function (Builder $query) use ($allCategories) {
                            return $query->whereIn('category_id', $allCategories);
                        }, $allCategories);
                    }),

            Filter::make('is_featured')->label("مقالات ویژه")
                  ->query(fn (Builder $query): Builder => $query->where('is_featured', 1)),

            Filter::make('is_translate_designated')->label("منتخب ترجمه")
                  ->query(fn (Builder $query): Builder => $query->where('is_translate_designated', 1)),

            Filter::make('edited_at')->label("ویرایش شده")
                  ->query(fn (Builder $query): Builder => $query->whereNotNull('edited_at')),

            Filter::make('published_at')->label("منتشر شده")
                  ->query(fn (Builder $query): Builder => $query->whereNotNull('published_at')),

            Filter::make('most_visited_source')->label("پر بازدید")
                  ->query(fn (Builder $query): Builder => $query->where('source_views','>', 1000 * 100)),


        ];
    }
}
