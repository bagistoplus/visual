@if (request()->routeIs('shop.checkout.onepage.index') && (bool) core()->getConfigData('sales.payment_methods.paypal_smart_button.active'))
  <!-- PayPal Smart Button script -->
  @php
    $clientId = core()->getConfigData('sales.payment_methods.paypal_smart_button.client_id');

    $acceptedCurrency = core()->getConfigData('sales.payment_methods.paypal_smart_button.accepted_currencies');
  @endphp

  @pushOnce('scripts')
    <script src="https://www.paypal.com/sdk/js?client-id={{ $clientId }}&currency={{ $acceptedCurrency }}" data-partner-attribution-id="Bagisto_Cart"></script>
    <script>
      const messages = {
        invalidConfig: "@lang('paypal::app.errors.invalid-configs')",
        authorizationError: "@lang('paypal::app.errors.something-went-wrong')"
      };

      window.addEventListener('checkout:payment_method_set', event => {
        if (event.detail.paymentMethod !== 'paypal_smart_button') {
          return;
        }

        requestAnimationFrame(() => {
          if (!document.querySelector('.paypal-button-container')) {
            return;
          }

          const paypalOptions = {
            style: {
              layout: 'vertical',
              shape: 'rect',
            },

            authorizationFailed: false,

            enableStandardCardFields: false,

            alertBox(message, type = 'info') {
              window.dispatchEvent(new CustomEvent('show-toast', {
                type,
                message
              }));
            },

            createOrder: async function(data, actions) {
              try {
                const response = await fetch("{{ route('paypal.smart-button.create-order') }}", {
                  credentials: 'include'
                });

                const jsonResponse = await response.json();
                return jsonResponse.result.id;
              } catch (error) {
                if (error.response?.data?.error === 'invalid_client') {
                  paypalOptions.authorizationFailed = true;
                  paypalOptions.alertBox(messages.invalidConfig, 'error');
                }
                throw error;
              }
            },

            onApprove: async function(data, actions) {
              // Optionally, show a loader here
              try {
                const response = await fetch("{{ route('paypal.smart-button.capture-order') }}", {
                  method: 'POST',
                  credentials: 'include',
                  headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                  },
                  body: JSON.stringify({
                    _token: "{{ csrf_token() }}",
                    orderData: data
                  })
                });
                const jsonResponse = await response.json();
                if (jsonResponse.success) {
                  window.location.href = jsonResponse.redirect_url ||
                    "{{ route('shop.checkout.onepage.success') }}";
                }

                // Optionally, hide the PayPal loader here
              } catch (error) {
                window.location.href = "{{ route('shop.checkout.cart.index') }}";
              }
            },

            onError: function(error) {
              if (!paypalOptions.authorizationFailed) {
                paypalOptions.alertBox(messages.authorizationError, 'error');
              }
            }
          };

          if (typeof paypal == 'undefined') {
            paypalOptions.alertBox(messages.invalidConfig);
            return;
          }

          const element = window.matchMedia('(max-width: 1023px)').matches ?
            '.mobile>.paypal-button-container' :
            '.desktop>.paypal-button-container';

          paypal.Buttons(paypalOptions).render(element);
        })
      });
    </script>
  @endpushOnce
@endif
