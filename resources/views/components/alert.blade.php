<div id="{{$id}}" class="my-3 transition-all duration-1000 ease-in-out p-3 max-w-4xl mx-auto rounded-2xl bg-{{$color}} text-{{$text}}"
onclick="
this.classList.remove('opacity-100');
this.classList.add('opacity-0');
this.classList.remove('my-3');
this.classList.add('my-0');
this.classList.remove('p-3');
this.classList.add('p-0');
setTimeout(() => {
    this.classList.add('h-0');
    }, 1000);
">
    {{$slot}}
</div>
