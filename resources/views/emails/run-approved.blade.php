@component('mail::message')
# Your speedrun was approved! #
{{$speedrun->user->name}}, your speedrun "{{$speedrun->title()}}"
has been approved and is currently ranking {{str_ordinal($speedrun->placement())}}
in the {{$speedrun->category()->title}} category!
<br>
The Council will confirm this decision with an election this week, where the ultimate fate of your run will be decided.
<br>
@component('mail::button', ['url' => url('/watch/'.$speedrun->id), 'color' => 'success'])
View Speedrun
@endcomponent
Thank you for submitting your run to American Dad Speedruns.
@endcomponent
