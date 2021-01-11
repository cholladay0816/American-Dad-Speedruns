@component('mail::message')
# A comment was posted on your Speedrun #
{{$user->name}}, you've received a comment on your speedrun, **{{$speedrun->title()}}**.
<br>
This comment was made by **{{$commentor->name}}**:
<br>
> *{{$comment->message}}*
<br>
@component('mail::button', ['url' => url('/watch/'.$speedrun->id), 'color' => 'primary'])
    View
@endcomponent

@endcomponent
