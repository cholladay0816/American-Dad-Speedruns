@component('mail::message')
# Your speedrun was approved! #
{{$speedrun->user->name}}, your speedrun "{{$speedrun->title()}}"
has been approved and is currently ranking {{str_ordinal($speedrun->placement())}}
in the {{$speedrun->category()->title}} category!
<br>
Your run has been verified by a moderator.
<br>
@component('mail::button', ['url' => url('/watch/'.$speedrun->id), 'color' => 'success'])
View Speedrun
@endcomponent
Thank you for submitting your run to American Dad Speedruns.
@endcomponent
