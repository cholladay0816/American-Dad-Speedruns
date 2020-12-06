<x-app-layout>
    <x-slot name="header">
        {{isset($banner)?'Edit':'Create'}} Banner
    </x-slot>
    <x-slot name="title">
        {{isset($banner)?'Edit':'Create'}} Banner
    </x-slot>
<div class="">
    <div class="max-w-4xl mx-auto bg-gray-500 p-3 mt-24 rounded-t-2xl text-white text-center font-bold text-3xl">{{isset($banner)?'Edit':'Create'}} Banner</div>
    <form class="max-w-4xl mx-auto my-auto p-5 bg-white rounded-b-2xl" method="POST">
        @csrf
        @method(isset($banner)?"PUT":"POST")
        <div class="grid md:grid-cols-2 grid-cols-1 gap-5">
        <div class="flex flex-col col-span-2">
            <label class="text-center text-black font-semibold text-2xl" for="title">Title</label>
            <input required maxlength="256" class="
            @error('title')
            border-red-600 border-2
            @enderror
            rounded-lg font-semibold px-3 py-2 text-black bg-gray-300" id="title" type="text" name="title" placeholder="" value="{{old('title')??$banner->title??''}}">
            @error('title')
            <span class="text-center text-red-600 font-semibold text-sm pt-1">{{$message}}</span>
            @enderror
        </div>
        <div class="flex flex-col">
            <label class="text-center text-black font-semibold text-2xl" for="url">Link <span class="text-gray-600 text-xs my-auto font-light"> (optional)</span></label>
            <input maxlength="256" class="
            @error('url')
                border-red-600 border-2
            @enderror
            rounded-lg font-semibold px-3 py-2 text-black bg-gray-300" id="url" type="text" name="url" placeholder="" value="{{old('url')??$banner->url??''}}">
            @error('url')
            <span class="text-center text-red-600 font-semibold text-sm pt-1">{{$message}}</span>
            @enderror
        </div>
        <div class="flex flex-col">
            <label class="text-center text-black font-semibold text-2xl" for="expiration">Expiration Date</label>
            <input required class="
            @error('expiration')
                border-red-600 border-2
            @enderror
            rounded-lg font-semibold px-3 py-2 text-black bg-gray-300" id="url" type="text" name="expiration" placeholder=""
                   value="{{old('expiration') ?? $banner->expiration ?? now()->addDays(7)->toDateString()}}"
            >
            @error('expiration')
            <span class="text-center text-red-600 font-semibold text-sm pt-1">{{$message}}</span>
            @enderror
        </div>
            <div class="flex flex-col">
                <label class="text-center text-black font-semibold text-2xl" for="bodycolor">Body Color</label>
                <input required class="
                @error('bodycolor')
                    border-red-600 border-2
                @enderror
                    rounded-lg font-semibold px-3 py-2 text-black bg-gray-300" id="bodycolor" type="text" name="bodycolor" placeholder="blue-300" value="{{old('bodycolor')??$banner->bcolor??'blue-300'}}">
                @error('bodycolor')
                <span class="text-center text-red-600 font-semibold text-sm pt-1">{{$message}}</span>
                @enderror
            </div>
            <div class="flex flex-col">
                <label class="text-center text-black font-semibold text-2xl" for="textcolor">Text Color</label>
                <input required class="
                @error('textcolor')
                    border-red-600 border-2
                @enderror
                    rounded-lg font-semibold px-3 py-2 text-black bg-gray-300" id="textcolor" type="text" name="textcolor" placeholder="blue-700" value="{{old('textcolor')??$banner->tcolor??'blue-700'}}">
                @error('textcolor')
                <span class="text-center text-red-600 font-semibold text-sm pt-1">{{$message}}</span>
                @enderror
            </div>

            <input class="rounded-lg bg-blue-600 px-3 py-2 font-semibold" type="submit">
        </div>
    </form>

</div>
</x-app-layout>
