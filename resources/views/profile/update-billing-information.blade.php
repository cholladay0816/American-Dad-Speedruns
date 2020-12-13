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
                       This is used to manage payment for premium upgrades like the Council subscription plan.') }}
            </p>
        </div>


        <div class="mt-5">
            <x-jet-button wire:click="billing">
                {{ __('Visit Stripe') }}
            </x-jet-button>
        </div>
    </x-slot>
</x-jet-action-section>
