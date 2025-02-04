<x-jet-action-section>
    <x-slot name="title">
        {{ __('Billing Information') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Manage your billing information through Stripe\'s billing portal.') }}
    </x-slot>

    <x-slot name="content">
        <div class="mt-3 max-w-xl text-sm text-gray-600">
            <p>
                {{ __('You can visit Stripe using the button below to create or update payment details at any time.
                       This is used to manage payment for various fundraising plans.') }}
            </p>
        </div>


        <div class="mt-5">
            <x-jet-button wire:click="billing">
                {{ __('Visit Stripe') }}
            </x-jet-button>
        </div>
        @if(auth()->user()->subscribed())
        <div class="border-t border-gray-600 mt-3 text-black">
            <div class="py-2 ">Subscriptions:</div>
            @foreach(auth()->user()->subscriptions as $subscription)
                <form method="POST" action="{{route('council.destroy')}}">
                    @method('DELETE')
                    @csrf
                <div class="
                @if($subscription->stripe_status == 'active' && is_null($subscription->ends_at))
                bg-green-400
                @else
                bg-gray-500
                @endif
                text-white rounded-md flex justify-between">

                    <div class="my-auto px-2">
                        American Dad Speedrun Council Subscription
                    </div>
                    @if(is_null($subscription->ends_at))
                    <button type="submit" class="bg-transparent duration-100 hover:bg-red-400 px-2 py-3 rounded-r text-green-100 hover:text-white">Cancel</button>
                    @else
                        <div class="px-2 py-3">
                            Expiration: {{ \Carbon\Carbon::parse($subscription->ends_at)->diffForHumans() }}
                        </div>
                    @endif
                </div>
                </form>
            @endforeach
        </div>
        @endif
    </x-slot>
</x-jet-action-section>
