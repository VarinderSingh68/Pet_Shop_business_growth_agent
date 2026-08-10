<?php
\App\Core\View::extend('layouts/site');
\App\Core\View::start('content');

/** @var array $order */
/** @var array $rzp */
?>

<section class="mx-auto max-w-md px-4 sm:px-6 py-20 text-center">
  <h1 class="font-display text-2xl font-bold">Completing your payment&hellip;</h1>
  <p class="mt-2 text-ink/60">A secure payment window will open automatically.</p>
  <p class="mt-6 text-sm text-ink/50">Order <strong><?= e($order['order_number']) ?></strong> &middot; <?= money((int) $order['total_paise']) ?></p>
  <button id="retry-btn" type="button" class="mt-8 hidden btn btn-primary">Open payment window</button>
</section>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
  function openCheckout() {
    var rzp = new Razorpay({
      key: <?= json_encode($rzp['razorpay_key']) ?>,
      amount: <?= (int) $rzp['amount'] ?>,
      currency: <?= json_encode($rzp['currency']) ?>,
      order_id: <?= json_encode($rzp['razorpay_order_id']) ?>,
      name: <?= json_encode(config('app.name')) ?>,
      description: 'Order <?= e($order['order_number']) ?>',
      handler: function (response) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';

        var fields = {
          _token: <?= json_encode(csrf_token()) ?>,
          order_number: <?= json_encode($order['order_number']) ?>,
          razorpay_order_id: response.razorpay_order_id,
          razorpay_payment_id: response.razorpay_payment_id,
          razorpay_signature: response.razorpay_signature
        };

        fetch('/api/v1/payments/verify', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': fields._token },
          body: JSON.stringify(fields)
        }).then(function (r) { return r.json(); }).then(function (data) {
          if (data.ok) {
            window.location.href = data.redirect;
          } else {
            document.getElementById('retry-btn').classList.remove('hidden');
            alert('Payment could not be verified. Please try again or choose cash on delivery.');
          }
        });
      },
      modal: {
        ondismiss: function () {
          document.getElementById('retry-btn').classList.remove('hidden');
        }
      },
      theme: { color: '#e8492a' }
    });
    rzp.open();
  }

  document.getElementById('retry-btn').addEventListener('click', openCheckout);
  openCheckout();
</script>

<?php \App\Core\View::stop(); ?>
