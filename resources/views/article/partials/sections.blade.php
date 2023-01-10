@if(count($article->sections) > 1)
<div class="font-bold mb-2">بخش ها</div>
<ul class="mr-4 space-y-1 grid md:grid-cols-2">
@foreach($article->sections as $sectionIndex => $section)
    <li class="list-decimal list-inside marker:text-primary-600 marker:font-bold">
        <a href="#section-{{$sectionIndex+1}}">{{ $section->title_fa }}</a>
    </li>
@endforeach
</ul>
@endif
