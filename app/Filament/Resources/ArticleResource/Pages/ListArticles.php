<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Filament\Resources\ArticleResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
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
}
