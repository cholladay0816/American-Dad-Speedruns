@component('mail::message')
#Election started!#
{{$user->name}},<br>
an election has started to decide if run #{{$election->speedrun_id}}, also known as "{{$election->speedrun->title()}}"
is to be accepted or disqualified.
Use the link below to cast your vote!
<br>
@component('mail::button', ['url'=>url('/elections/'.$election->id), 'color' => 'success'])
    Click to Vote
@endcomponent
<br>
Thanks for your continued support,
<br>
AmericanDadSpeedruns.com
@endcomponent
