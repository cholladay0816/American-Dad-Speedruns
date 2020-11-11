<x-app-layout>

    <form method="POST" class="w-full max-w-lg mx-auto my-auto">
        @csrf
        @method('POST')
        <div class="rounded bg-white text-black p-3 mx-auto mt-24">
            <p class=" text-green-500 font-semibold text-2xl mx-auto text-center w-full pb-4">Submit Run</p>
        <div class="flex flex-wrap -mx-3 mb-6">
            <div class="w-full px-3 mb-6 md:mb-0">
                <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-url">
                    URL
                </label>
                <input class="appearance-none block w-full bg-gray-200 text-gray-700
                 @error('url')
                    border border-red-500
                 @enderror
                 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-gray-300" id="grid-url" type="text" placeholder="Jane">
                @error('url')
                <p class="text-red-500 text-xs italic">{{$message}}</p>
                @enderror
            </div>
        </div>
        <div class="flex flex-wrap -mx-3 mb-6">
            <div class="w-full md:w-1/2 px-3">
                <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-category-id">
                    Category
                </label>
                <select class="appearance-none block w-full bg-gray-200 text-gray-700
                     @error('category_id')
                        border border-red-500
                    @enderror
                    rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-gray-300 focus:border-gray-500"
                        id="grid-category-id">
                    @foreach($categories as $category)
                        <option value="{{$category->id}}">{{$category->title}}</option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="text-red-500 text-xs italic">{{$message}}</p>
                @enderror
            </div>
            <div class="w-full md:w-1/2 px-3">
                <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-platform-id">
                    Platform
                </label>
                <select class="appearance-none block w-full bg-gray-200 text-gray-700
                    @error('platform_id')
                    border border-red-500
@enderror
                    rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-gray-300 focus:border-gray-500"
                        id="grid-platform-id">
                    @foreach($platforms as $platform)
                        <option value="{{$platform->id}}">{{$platform->title}}</option>
                    @endforeach
                </select>
                @error('platform_id')
                <p class="text-red-500 text-xs italic">{{$message}}</p>
                @enderror
            </div>

            <div class="w-full md:w-1/2 px-3">
                <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-platform-id">
                    Time
                </label>
                <input type="number" min="0.0001" step="0.0001" class="appearance-none block w-full bg-gray-200 text-gray-700
                    @error('time')
                        border border-red-500
                    @enderror
                    rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-gray-300 focus:border-gray-500"
                       id="grid-platform-id" value="{{old('time')??30}}">
                @error('time')
                <p class="text-red-500 text-xs italic">{{$message}}</p>
                @enderror
            </div>
            <div class="w-full md:w-1/2 px-3">
                <label class="invisible block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-platform-id">
                    Submit
                </label>
                <button type="submit" class="mx-auto rounded bg-green-500 text-gray-100 text-center py-2 px-3">Submit Run</button>
            </div>
        </div>

        </div>

    </form>









    <form method="POST">
        @csrf
        @method('POST')
        <input name="url" maxlength="28" placeholder="https://www.youtu.be/your_run_here"/>
        <input type="number" step="0.0001" min="0.0001" value="{{old('number')??30}}" name="time"/>
        <label for="platform_id">Platform:</label>
        <select id="platform_id" name="platform_id">
            @foreach($platforms as $platform)
                <option value="{{$platform->id}}">{{$platform->title}}</option>
            @endforeach
        </select>
        @error('platform_id')
            <a class="text-red-500">Error: {{$message}}</a>
        @enderror
        <label for="category_id">Category:</label>
        <select id="category_id" name="category_id">
            @foreach($categories as $category)
                <option value="{{$category->id}}">{{$category->title}}</option>
            @endforeach
        </select>
        @error('category_id')
            <a class="text-red-500">Error: {{$message}}</a>
        @enderror
        <input type="submit"/>
    </form>
</x-app-layout>
