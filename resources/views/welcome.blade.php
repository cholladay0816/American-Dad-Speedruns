<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>American Dad Speedruns</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

        <!-- Styles -->
        <style>
            /*! normalize.css v8.0.1 | MIT License | github.com/necolas/normalize.css */html{line-height:1.15;-webkit-text-size-adjust:100%}body{margin:0}a{background-color:transparent}[hidden]{display:none}html{font-family:system-ui,-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica Neue,Arial,Noto Sans,sans-serif,Apple Color Emoji,Segoe UI Emoji,Segoe UI Symbol,Noto Color Emoji;line-height:1.5}*,:after,:before{box-sizing:border-box;border:0 solid #e2e8f0}a{color:inherit;text-decoration:inherit}svg,video{display:block;vertical-align:middle}video{max-width:100%;height:auto}.bg-white{--bg-opacity:1;background-color:#fff;background-color:rgba(255,255,255,var(--bg-opacity))}.bg-gray-100{--bg-opacity:1;background-color:#f7fafc;background-color:rgba(247,250,252,var(--bg-opacity))}.border-gray-200{--border-opacity:1;border-color:#edf2f7;border-color:rgba(237,242,247,var(--border-opacity))}.border-t{border-top-width:1px}.flex{display:flex}.grid{display:grid}.hidden{display:none}.items-center{align-items:center}.justify-center{justify-content:center}.font-semibold{font-weight:600}.h-5{height:1.25rem}.h-8{height:2rem}.h-16{height:4rem}.text-sm{font-size:.875rem}.text-lg{font-size:1.125rem}.leading-7{line-height:1.75rem}.mx-auto{margin-left:auto;margin-right:auto}.ml-1{margin-left:.25rem}.mt-2{margin-top:.5rem}.mr-2{margin-right:.5rem}.ml-2{margin-left:.5rem}.mt-4{margin-top:1rem}.ml-4{margin-left:1rem}.mt-8{margin-top:2rem}.ml-12{margin-left:3rem}.-mt-px{margin-top:-1px}.max-w-6xl{max-width:72rem}.min-h-screen{min-height:100vh}.overflow-hidden{overflow:hidden}.p-6{padding:1.5rem}.py-4{padding-top:1rem;padding-bottom:1rem}.px-6{padding-left:1.5rem;padding-right:1.5rem}.pt-8{padding-top:2rem}.fixed{position:fixed}.relative{position:relative}.top-0{top:0}.right-0{right:0}.shadow{box-shadow:0 1px 3px 0 rgba(0,0,0,.1),0 1px 2px 0 rgba(0,0,0,.06)}.text-center{text-align:center}.text-gray-200{--text-opacity:1;color:#edf2f7;color:rgba(237,242,247,var(--text-opacity))}.text-gray-300{--text-opacity:1;color:#e2e8f0;color:rgba(226,232,240,var(--text-opacity))}.text-gray-400{--text-opacity:1;color:#cbd5e0;color:rgba(203,213,224,var(--text-opacity))}.text-gray-500{--text-opacity:1;color:#a0aec0;color:rgba(160,174,192,var(--text-opacity))}.text-gray-600{--text-opacity:1;color:#718096;color:rgba(113,128,150,var(--text-opacity))}.text-gray-700{--text-opacity:1;color:#4a5568;color:rgba(74,85,104,var(--text-opacity))}.text-gray-900{--text-opacity:1;color:#1a202c;color:rgba(26,32,44,var(--text-opacity))}.underline{text-decoration:underline}.antialiased{-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}.w-5{width:1.25rem}.w-8{width:2rem}.w-auto{width:auto}.grid-cols-1{grid-template-columns:repeat(1,minmax(0,1fr))}@media (min-width:640px){.sm\:rounded-lg{border-radius:.5rem}.sm\:block{display:block}.sm\:items-center{align-items:center}.sm\:justify-start{justify-content:flex-start}.sm\:justify-between{justify-content:space-between}.sm\:h-20{height:5rem}.sm\:ml-0{margin-left:0}.sm\:px-6{padding-left:1.5rem;padding-right:1.5rem}.sm\:pt-0{padding-top:0}.sm\:text-left{text-align:left}.sm\:text-right{text-align:right}}@media (min-width:768px){.md\:border-t-0{border-top-width:0}.md\:border-l{border-left-width:1px}.md\:grid-cols-2{grid-template-columns:repeat(2,minmax(0,1fr))}}@media (min-width:1024px){.lg\:px-8{padding-left:2rem;padding-right:2rem}}@media (prefers-color-scheme:dark){.dark\:bg-gray-800{--bg-opacity:1;background-color:#2d3748;background-color:rgba(45,55,72,var(--bg-opacity))}.dark\:bg-gray-900{--bg-opacity:1;background-color:#1a202c;background-color:rgba(26,32,44,var(--bg-opacity))}.dark\:border-gray-700{--border-opacity:1;border-color:#4a5568;border-color:rgba(74,85,104,var(--border-opacity))}.dark\:text-white{--text-opacity:1;color:#fff;color:rgba(255,255,255,var(--text-opacity))}.dark\:text-gray-400{--text-opacity:1;color:#cbd5e0;color:rgba(203,213,224,var(--text-opacity))}}
        </style>

        <style>
            body {
                font-family: 'Nunito';
            }
        </style>
    </head>
    <x-app-layout>
    <body>
        <h1 class="text-center font-bold mx-auto text-5xl mt-4">American Dad Speedruns</h1>
        <h2 class="text-center mx-auto text-lg  text-gray-200">The official leaderboards for all American Dad Speedruns.</h2>
        <div class="grid grid-cols-1 lg:grid-cols-6">

            <div class="col-span-1 grid grid-cols-1 grid-flow-row-dense auto-rows-min text-black">
                <p class="col-span-0 text-gray-200 font-semibold text-center mx-auto pt-10">Advertisements</p>
                <div class="bg-gray-200 p-5 m-5 rounded text-center">
                    advertisement
                </div>
            </div>
            <div class="max-w-full lg:max-w-6xl mx-auto col-span-1 md:col-span-4">
                @if(isset($featured))
                <div class="py-12">
                    <div class="grid grid-cols-1 xl:grid-cols-3 gap-2 border rounded p-10 bg-gray-50 text-black">
                        <div class="h-56 md:h-96 xl:col-span-2 relative overflow-hidden block max-w-96">
                        <iframe class="absolute top-0 bottom-0 left-0"
                                width="100%" height="100%" frameborder="0"
                                allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen=""
                                src="{{$featured->embed_url()}}" ></iframe>
                        </div>
                        <div class="flex flex-col">
                            <div class="font-bold text-2xl">Featured Run</div>
                            <div class="font-semibold text-2xl">{{$featured->time}}s by {{$featured->user->name}}</div>
                            <div class="text-gray-700 text-lg">{{$featured->category()->title}} - {{$featured->platform()->title}}</div>
                        </div>
                    </div>
                </div>
                @endif

                @if($speedruns)
                    <div class="max-w-full lg:max-w-6xl mx-auto py-12">
                        <div class="border rounded py-10 md:p-10 bg-gray-50 text-black">
                            <div class="font-bold text-2xl mb-10">Recently Submitted Speedruns</div>
                            <div class="md:-mx-10">
                            @component('components.speedrun-table', ['speedruns'=>$speedruns])
                            @endcomponent
                            </div>
                        </div>
                    </div>
                @endif
                <div class="mx-auto border-white border-t border-dotted">
                    <div class="max-w-7xl max-h-xl mx-auto grid grid-cols-2">
                        <div class="h-100">
                            <img class="max-w-24" src="https://www.americandadspeedruns.com/public/img/stan_says.png"/>
                        </div>
                        <div class=" my-auto flex flex-col">
                            <div class="font-bold text-4xl">Stan Says:</div>
                            <div class="font-semibold text-gray-300">You should join the American Dad Speedrunning Discord!</div>
                            <div class="border-l-2 border-yellow-300 text-white bg-opacity-10 hover:bg-opacity-25 bg-yellow-500 px-3">All the cool kids are already <a class="text-indigo-500 font-semibold">@here</a>.</div>
                            <a href="" class="bg-indigo-500 border-white text-white text-xl rounded px-2 py-2 mr-auto mt-5 ">Click here to Join!</a>
                            <div class="text-gray-300 mt-8">Note: we are not affiliated with this server.</div>
                        </div>
                    </div>
                </div>
                <div>

                </div>
            </div>
        </div>
    </body>
    </x-app-layout>
</html>
