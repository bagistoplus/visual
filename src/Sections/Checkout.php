<?php

namespace BagistoPlus\Visual\Sections;

use BagistoPlus\Visual\Actions\Checkout\PlaceOrder;
use BagistoPlus\Visual\Actions\Checkout\StoreAddresses;
use BagistoPlus\Visual\Actions\Checkout\StorePaymentMethod;
use BagistoPlus\Visual\Actions\Checkout\StoreShippingMethod;
use BagistoPlus\Visual\Enums\Events;
use BagistoPlus\Visual\Support\InteractsWithCart;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

#[On(Events::COUPON_APPLIED)]
#[On(Events::COUPON_REMOVED)]
class Checkout extends LivewireSection
{
    use InteractsWithCart;

    public static string $view = 'shop::sections.checkout';

    /**
     * Current checkout step.
     */
    public string $currentStep = 'address';

    /**
     * The billing address data.
     */
    public array $billingAddress = [];

    /**
     * The shipping address data.
     */
    public array $shippingAddress = [];

    /**
     * Available shipping methods.
     */
    public array $shippingMethods = [];

    /**
     * Available payment methods.
     */
    public array $paymentMethods = [];

    protected $savedAddresses = [];

    /**
     * Fillable address fields.
     */
    protected array $addressFillable = [
        'id',
        'company_name',
        'email',
        'first_name',
        'last_name',
        'address',
        'country',
        'state',
        'city',
        'postcode',
        'phone',
        'default_address',
    ];

    /**
     * Initialize the component state.
     */
    public function mount()
    {
        $this->initializeAddresses();
        $this->loadSavedAddresses();
    }

    /**
     * Initialize the billing and shipping addresses.
     */
    protected function initializeAddresses(): void
    {
        $this->billingAddress = $this->addressDefaults();
        $this->shippingAddress = $this->addressDefaults();

        $cart = $this->getCart();

        if ($cart->billing_address) {
            $this->billingAddress = array_merge(
                $this->billingAddress,
                $cart->billing_address->only($this->addressFillable)
            );

            $this->normalizeAddressFormat($this->billingAddress);
        }

        if ($cart->shipping_address) {
            $this->shippingAddress = array_merge(
                $this->shippingAddress,
                $cart->shipping_address->only($this->addressFillable)
            );

            $this->normalizeAddressFormat($this->shippingAddress);
        }
    }

    protected function loadSavedAddresses()
    {
        if (! Auth::guard('customer')->check()) {
            return;
        }

        $savedAddresses = Auth::guard('customer')
            ->user()
            ->addresses
            ->map(function ($address) {
                $address->address = explode(PHP_EOL, $address->address);

                return $address->toArray();
            });

        $defaultAddress = $savedAddresses->where('default_address', true)->first();

        if ($defaultAddress) {
            $defaultAddress['save_address'] = true;
            $this->billingAddress = $defaultAddress;
            $this->shippingAddress = $defaultAddress;
        }

        $this->savedAddresses = $savedAddresses->toArray();
    }

    /**
     * Normalize the address format.
     */
    protected function normalizeAddressFormat(array &$address): void
    {
        if (! is_array($address['address'])) {
            $address['address'] = explode(PHP_EOL, $address['address']);
        }
    }

    /**
     * Get default address structure.
     */
    protected function addressDefaults(): array
    {
        return [
            'id' => null,
            'company_name' => '',
            'email' => '',
            'first_name' => '',
            'last_name' => '',
            'address' => [],
            'country' => '',
            'state' => '',
            'city' => '',
            'postcode' => '',
            'phone' => '',
            'use_for_shipping' => true,
            'save_address' => false,
        ];
    }

    /**
     * Handle the address form submission.
     */
    public function handleAddressForm(StoreAddresses $storeAddresses)
    {
        $data = ['billing' => $this->billingAddress];

        if (! $this->billingAddress['use_for_shipping']) {
            $data['shipping'] = $this->shippingAddress;
        }

        $response = $storeAddresses->execute($data);

        if (isset($response['redirect_url'])) {
            return $this->redirect($response['redirect_url']);
        }

        return $this->cartHaveStockableItems()
            ? $this->moveToShippingStep($response['data']['shippingMethods'])
            : $this->moveToPaymentStep($response['data']['payment_methods']);
    }

    /**
     * Handle shipping method selection.
     */
    public function handleShippingMethod(StoreShippingMethod $storeShippingMethod, string $method)
    {
        $response = $storeShippingMethod->execute($method);

        if (isset($response['redirect_url'])) {
            return $this->redirect($response['redirect_url']);
        }

        $this->moveToPaymentStep($response['payment_methods']);
    }

    /**
     * Handle payment method selection.
     */
    public function handlePaymentMethod(StorePaymentMethod $storePaymentMethod, string $method)
    {
        $response = $storePaymentMethod->execute($method);

        if (isset($response['redirect_url'])) {
            return $this->redirect($response['redirect_url']);
        }

        $this->currentStep = 'review';

        $this->dispatch(Events::PAYMENT_METHOD_SET, paymentMethod: $method);
    }

    /**
     * Place the order.
     */
    public function placeOrder(PlaceOrder $placeOrder)
    {
        $response = $placeOrder->execute();

        if (isset($response['message'])) {
            session()->flash('info', $response['message']);
        }

        if (isset($response['redirect_url'])) {
            $this->redirect($response['redirect_url']);
        }
    }

    /**
     * Move to the shipping method step.
     */
    protected function moveToShippingStep(array $shippingMethods)
    {
        $this->currentStep = 'shipping';
        $this->shippingMethods = $shippingMethods;
    }

    /**
     * Move to the payment method step.
     */
    protected function moveToPaymentStep(array $paymentMethods)
    {
        $this->currentStep = 'payment';
        $this->paymentMethods = $paymentMethods;
    }

    /**
     * Get view data for the component.
     */
    public function getViewData(): array
    {
        $data = [
            'cart' => $this->getCartResource(),
            'countries' => app('core')->countries(),
            'states' => app('core')->groupedStatesByCountries(),
            'savedAddresses' => $this->savedAddresses,
        ];

        return $data;
    }
}
