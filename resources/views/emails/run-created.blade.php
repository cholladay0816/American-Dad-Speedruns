@component('mail::message')
A new speedrun has been submitted<br>
Runner: {{$speedrun->user->name}}<br>
Time: {{$speedrun->time}}<br>
Category: {{$speedrun->category()->title}}<br>
Platform: {{$speedrun->platform()->title}}<br>
@component('mail::button', ['url' => url('/watch/'.$speedrun->id), 'color' => 'success'])
Watch Run
@endcomponent
@component('mail::button', ['url' => url('/admin/verify'), 'color' => 'primary'])
Open Dashboard
@endcomponent

@endcomponent
