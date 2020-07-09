<script
    src="https://code.jquery.com/jquery-3.5.1.min.js"
    integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
    crossorigin="anonymous"></script>
<script src="https://js.stripe.com/v3/"></script>
<script>
    var stripe = Stripe('{{ config('ezstripe.stripe_key') }}');

    $(function() {

        $("#ezstripe").submit(function (e) {
            e.preventDefault();

            $.post("{{route('ezstripe.checkout')}}", $("#ezstripe").serialize()).done(function (data) {
                stripe.redirectToCheckout({
                    sessionId: data
                }).then(function (result) {
                    alert(result.error.message);
                });
            }).fail(function () {
                alert("Checkout failed.");
            });
        });
    });

</script>
