@if(!is_null($banner))
    <div class=" {{!is_null($banner)?'':'hidden'}} bg-{{$banner->bcolor}} text-{{$banner->tcolor}} flex flex-row p-4 mt-4 max-w-5xl rounded-lg mx-auto text-center text-2xl font-semibold">
        <a @if(isset($banner->url)) href="{{$banner->url}}" @endif
        class="flex text-center my-auto mx-auto break-words">
            {{$banner->title}}
        </a>
        @auth
            <button wire:click="detach('{{$banner->id}}')" class="flex justify-end rounded my-auto px-4">
                X
            </button>
        @endauth
    </div>
@else
    <div>

    </div>
@endif
