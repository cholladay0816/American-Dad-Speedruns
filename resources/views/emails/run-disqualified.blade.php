@component('mail::message')
# Your speedrun has been disqualified. #
{{$speedrun->user->name}},
your speedrun has been disqualified for the following reason: "{{$speedrun->disqualification->reason}}"<br>
Your run will stay up, but your score is no longer in the official ranking.
@endcomponent
