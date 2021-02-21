<div>
    <div>
        <h1>Chat</h1>
        <div>
            <div>
                @foreach($messages as $message)
                <div>
                    {{ $message->body }}
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
