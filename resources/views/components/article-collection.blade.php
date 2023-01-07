<?php
/** @var \App\View\ArticleCollectionData $articleCollection */
/** @var \App\Models\Article $article */
?>

<div class="grid sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
    @foreach($articleCollection->getArticles() as $article)
{{--        <p>{{$article->title_fa}}</p>--}}
        <x-article-thumb :article="$article"></x-article-thumb>
    @endforeach

</div>

@if($articleCollection->shouldShowPaginator())
<div class="my-5">
    {{ $articleCollection->getArticles()->links() }}
</div>
@endif
