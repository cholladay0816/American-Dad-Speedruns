<x-app-layout>
    <x-slot name="title">
        Submit Run
    </x-slot>
    <form method="POST" class="w-full max-w-4xl mx-auto my-auto">
        @csrf
        @method('POST')
        <div class="rounded bg-white text-black p-3 mx-auto mt-24">
            <p class="text-green-500 font-bold text-4xl mx-auto text-center w-full pb-4">Submit Speedrun</p>
            <div class="flex flex-wrap -mx-3 mb-6">
                <div class="w-full px-3 mb-6 md:mb-0">
                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-url">
                        URL
                    </label>
                    <input name="url" required pattern="https:\/\/youtu.be\/*(.+)" class="appearance-none block w-full bg-gray-200 text-gray-700
                     @error('url')
                        border border-red-500
                     @enderror
                     rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-gray-300" id="grid-url" type="text" placeholder="https://youtu.be/your_url_here">
                    @error('url')
                        <p class="text-red-500 text-xs italic">{{$message}}</p>
                    @enderror
                </div>
            </div>
            <div class="flex flex-wrap -mx-3 mb-6">
                <div class="w-full md:w-1/2 px-3">
                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-category">
                        Category
                    </label>
                    <select class="appearance-none block w-full bg-gray-200 text-gray-700
                         @error('category')
                            border border-red-500
                        @enderror
                        rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-gray-300 focus:border-gray-500"
                            id="grid-category" name="category">
                        @foreach($categories as $category)
                            <option value="{{$category->id}}">{{$category->title}}</option>
                        @endforeach
                    </select>
                    @error('category')
                        <p class="text-red-500 text-xs italic">{{$message}}</p>
                    @enderror
                </div>
                <div class="w-full md:w-1/2 px-3">
                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-platform">
                        Platform
                    </label>
                    <select class="appearance-none block w-full bg-gray-200 text-gray-700
                        @error('platform')
                            border border-red-500
                        @enderror
                        rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-gray-300 focus:border-gray-500"
                            id="grid-platform" name="platform">
                        @foreach($platforms as $platform)
                            <option value="{{$platform->id}}">{{$platform->title}}</option>
                        @endforeach
                    </select>
                    @error('platform')
                        <p class="text-red-500 text-xs italic">{{$message}}</p>
                    @enderror
                </div>

                <div class="w-full md:w-1/2 px-3">
                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-platform-id">
                        Time
                    </label>
                    <input name="time" required type="number" min="0.0001" step="0.0001" class="appearance-none block w-full bg-gray-200 text-gray-700
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
                    <button type="submit" class="mx-auto rounded bg-green-500 text-gray-100 text-center py-2 px-3">Submit</button>
                </div>
            </div>
        </div>
    </form>
</x-app-layout>
