<div id="{{$id}}" class="my-3 transition-all duration-1000 ease-in-out p-3 max-w-4xl mx-auto rounded-2xl bg-{{$color}} text-{{$text}}"

onclick="

this.classList.remove('opacity-100');
this.classList.add('opacity-0');
"

>
    {{$slot}}
</div>
