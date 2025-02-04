<x-app-layout>
    <div class="container mx-auto px-4 md:px-0 xl:py-24 py-0">
    <form
        class="max-w-3xl mx-auto bg-white rounded-xl mt-10 px-4 text-black py-4"
          action="{{url('council/join')}}"
          method="post" id="payment-form">
        @csrf
        @method('POST')
        <h1 class="sm:text-left text-center text-2xl font-bold">Join the American Dad Speedrunning Council!</h1>
        <p class="pb-4">Become a member of the official American Dad Speedrunning Council and decide the fate of every speedrun submitted.
            <br>Only <span class="font-bold text-blue-500">{{config('adsr.council.size')}}</span> seats are given out and there {{$seats!=1?'are':'is'}} <span class="font-bold text-red-500">{{$seats}}</span> left!
        </p>

        <x-jet-button type="submit">Join!</x-jet-button>
    </form>
    </div>

</x-app-layout>
