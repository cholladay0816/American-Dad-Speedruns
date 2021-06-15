<x-app-layout>

    <link rel="stylesheet" href="{{asset('css/stripe.css')}}">
    <script src="https://js.stripe.com/v3/"></script>
    <div class="container mx-auto px-4 md:px-0 xl:py-24 py-0">
    <form
        data-secret="{{ $intent->client_secret }}"
        class="max-w-3xl mx-auto bg-white rounded-xl mt-10 px-4 text-black py-4"
          action="{{url('council/join')}}"
          method="post" id="payment-form">
        @csrf
        @method('POST')
        <h1 class="sm:text-left text-center text-2xl font-bold">Join the American Dad Speedrunning Council!</h1>
        <p class="pb-4">Become a member of the official American Dad Speedrunning Council for <span class="font-bold text-green-500">$4.99</span>/mo and decide the fate of every speedrun submitted.
            <br>This is a limited time offer, only <span class="font-bold text-blue-500">{{config('adsr.councilsize')}}</span> seats are given out and there {{$seats!=1?'are':'is'}} <span class="font-bold text-red-500">{{$seats}}</span> left!
            All proceeds go towards this site and future sites like it.
        </p>
        <div class="flex flex-col w-1/2 py-2">
            <label for="cardholder-name">Cardholder's name</label>
            <input required class="rounded-lg px-4 py-2 bg-gray-200 focus:outline-none focus:bg-gray-300" name="cardholder-name" id="cardholder-name" placeholder="Full Name">
        </div>
        <div class="form-row pb-4 w-1/2">
            <label for="card-element" class="sr-only">
                Credit or debit card
            </label>
            <div id="card-element">
                <!-- A Stripe Element will be inserted here. -->
            </div>

            <!-- Used to display form errors. -->
            <div id="card-errors" role="alert"></div>
        </div>

        <x-jet-button >Submit Payment</x-jet-button>
    </form>
    </div>
    <script>
        const stripe = Stripe('{{config('stripe.key')}}');

        const elements = stripe.elements();
        const cardElement = elements.create('card');

        cardElement.mount('#card-element');

        // Custom styling can be passed to options when creating an Element.
        // (Note that this demo uses a wider set of styles than the guide below.)
        var style = {
            base: {
                color: '#32325d',
                fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                fontSmoothing: 'antialiased',
                fontSize: '16px',
                '::placeholder': {
                    color: '#aab7c4'
                }
            },
            invalid: {
                color: '#fa755a',
                iconColor: '#fa755a'
            }
        };

        // Handle real-time validation errors from the card Element.
        cardElement.on('change', function(event) {
            var displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });

        // Handle form submission.
        const form = document.getElementById('payment-form');
        const cardHolderName = document.getElementById('cardholder-name');
        const cardButton = document.getElementById('card-button');
        const clientSecret = form.dataset.secret;

        form.addEventListener('submit', async function(event) {
            event.preventDefault();
            const { setupIntent, error } = await stripe.confirmCardSetup(
                clientSecret, {
                    payment_method: {
                        card: cardElement,
                        billing_details: { name: cardHolderName.value }
                    }
                }
            );

            if (error) {
                // Display "error.message" to the user...
                var errorElement = document.getElementById('card-errors');
                errorElement.textContent = error.message;
            } else {
                // The card has been verified successfully...
                stripeTokenHandler(setupIntent);
            }
        });

        // Submit the form with the token ID.
        function stripeTokenHandler(setupIntent) {
            // Insert the token ID into the form so it gets submitted to the server
            var form = document.getElementById('payment-form');
            var hiddenInput = document.createElement('input');
            hiddenInput.setAttribute('type', 'hidden');
            hiddenInput.setAttribute('name', 'paymentMethod');
            hiddenInput.setAttribute('value', setupIntent.payment_method);
            form.appendChild(hiddenInput);

            // Submit the form
            form.submit();
        }

    </script>

</x-app-layout>
