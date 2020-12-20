<a href="{{url('runner/'.$judge->name)}}"
   class="border-l-4 border-green-300 grid md:grid-cols-2 p-5 text-green-400 font-bold text-4xl rounded-3xl my-4
            duration-500 hover:border-green-400 hover:text-green-500 hover:bg-green-100">
<div class="my-auto text-left">
    {{$judge->name}}
</div>
    <div class="font-thin text-sm md:text-right my-auto">
    Judge since: {{\Illuminate\Support\Carbon::createFromTimestamp($judge->subscription('default')->asStripeSubscription()->start_date)->diffForHumans()}}
    </div>
</a>
