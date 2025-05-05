<?php

namespace BagistoPlus\Visual\Sections;

use BagistoPlus\Visual\Actions\Checkout\PlaceOrder;
use BagistoPlus\Visual\Actions\Checkout\StoreAddresses;
use BagistoPlus\Visual\Actions\Checkout\StorePaymentMethod;
use BagistoPlus\Visual\Actions\Checkout\StoreShippingMethod;
use BagistoPlus\Visual\Data\AddressData;
use BagistoPlus\Visual\Enums\Events;
use BagistoPlus\Visual\Support\InteractsWithCart;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

#[On(Events::COUPON_APPLIED)]
#[On(Events::COUPON_REMOVED)]
class Checkout extends LivewireSection
{
    use InteractsWithCart;

    protected static array $enabledOn = ['checkout'];

    protected static string $view = 'shop::sections.checkout';

    public string $currentStep = 'address';

    public AddressData $billingAddress;

    public AddressData $shippingAddress;

    public array $shippingMethods = [];

    public array $paymentMethods = [];

    public string $selectedShippingMethod = '';

    public string $selectedPaymentMethod = '';

    /** @var Collection<int, AddressData> */
    protected Collection $savedAddresses;

    /**
     * Initialize the component state.
     */
    public function boot()
    {
        $this->loadSavedAddresses();
        $this->initializeAddresses();
    }

    /**
     * Handle the address form submission.
     */
    public function handleAddressForm(StoreAddresses $storeAddresses)
    {
        $data = ['billing' => $this->billingAddress->toArray()];

        if (! $this->billingAddress->use_for_shipping) {
            $data['shipping'] = $this->shippingAddress->toArray();
        }

        $response = $storeAddresses->execute($data);

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
        $this->selectedShippingMethod = '';
    }

    /**
     * Move to the payment method step.
     */
    protected function moveToPaymentStep(array $paymentMethods)
    {
        $this->currentStep = 'payment';
        $this->paymentMethods = $paymentMethods;
        $this->selectedPaymentMethod = '';
    }

    /**
     * Get view data for the component.
     */
    public function getViewData(): array
    {
        $data = [
            'cart' => $this->getCartResource(),
            'savedAddresses' => $this->savedAddresses,
        ];

        return $data;
    }

    /**
     * Initialize the billing and shipping addresses.
     */
    protected function initializeAddresses(): void
    {
        $this->billingAddress = AddressData::empty();
        $this->shippingAddress = AddressData::empty();
        $cart = $this->getCart();

        if ($cart->billing_address) {
            $this->billingAddress = AddressData::fromArray($cart->billing_address->toArray());
            $this->savedAddresses->prepend($this->billingAddress);
        }

        if ($cart->shipping_address) {
            $this->shippingAddress = AddressData::fromArray($cart->shipping_address->toArray());
            $this->savedAddresses->prepend($this->shippingAddress);
        }

        $this->applyDefaultAddress();
    }

    protected function loadSavedAddresses(): void
    {
        if (! Auth::guard('customer')->check()) {
            $this->savedAddresses = collect();

            return;
        }

        $this->savedAddresses = Auth::guard('customer')
            ->user()
            ->addresses
            ->map(fn ($address): AddressData => AddressData::fromArray([...$address->toArray(), 'use_for_shipping' => true]));
    }

    protected function applyDefaultAddress(): void
    {
        $default = $this->savedAddresses->first(fn (AddressData $addr): bool => $addr->default_address);

        if (! ($default instanceof AddressData)) {
            return;
        }

        if (! $this->billingAddress->id) {
            $this->billingAddress = AddressData::fromArray([
                ...$default->toArray(),
                'save_address' => true,
                'use_for_shipping' => $this->billingAddress->use_for_shipping,
            ]);
        }

        if (! $this->shippingAddress->id) {
            $this->shippingAddress = AddressData::fromArray([
                ...$default->toArray(),
                'save_address' => true,
            ]);
        }
    }
}
