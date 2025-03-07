<?php

namespace BagistoPlus\Visual\Sections;

use BagistoPlus\Visual\Actions\StoreCartAddresses;
use BagistoPlus\Visual\Support\InteractsWithCart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Webkul\Shipping\Facades\Shipping;
use Webkul\Shop\Http\Controllers\API\OnepageController;
use Webkul\Shop\Http\Requests\CartAddressRequest;

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
    ];

    /**
     * Initialize the component state.
     */
    public function mount(): void
    {
        $this->initializeAddresses();
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
    public function handleAddressForm(StoreCartAddresses $storeCartAddresses)
    {
        $data = ['billing' => $this->billingAddress];

        if (! $this->billingAddress['use_for_shipping']) {
            $data['shipping'] = $this->shippingAddress;
        }

        $this->getAddressValidator($data)->validate();

        if (! auth()->guard('customer')->check() && ! $this->getCart()?->hasGuestCheckoutItems()) {
            return $this->redirectRoute('shop.customer.session.index');
        }

        if ($this->cartHasErrors()) {
            return $this->redirectRoute('shop.checkout.cart.index');
        }

        $storeCartAddresses->execute($data);

        return $this->getCart()->haveStockableItems()
            ? $this->moveToShippingStep()
            : $this->moveToPaymentStep();
    }

    /**
     * Handle shipping method selection.
     */
    public function handleShippingMethod(string $method)
    {
        request()->merge(['shipping_method' => $method]);

        $response = app(OnepageController::class)->storeShippingMethod();
        $data = $response->getData(true);

        if (isset($data['redirect_url'])) {
            return $this->redirect($data['redirect_url']);
        }

        $this->paymentMethods = $data['payment_methods'];
        $this->moveToPaymentStep();
    }

    /**
     * Handle payment method selection.
     */
    public function handlePaymentMethod(string $method)
    {
        request()->merge(['payment' => ['method' => $method]]);

        $response = app(OnepageController::class)->storePaymentMethod();
        $data = $response instanceof JsonResponse ? $response->getData(true) : $response;

        if (isset($data['redirect_url'])) {
            return $this->redirect($data['redirect_url']);
        }

        $this->currentStep = 'review';
    }

    /**
     * Place the order.
     */
    public function placeOrder()
    {
        $response = app(OnepageController::class)->storeOrder();

        $responseData = $response instanceof JsonResource
            ? $response->toArray(request())
            : $response->getData(true);

        if (isset($responseData['message']) && $responseData['message']) {
            session()->flash('info', $responseData['message']);
        }

        if (isset($responseData['redirect_url'])) {
            return $this->redirect($responseData['redirect_url']);
        }
    }

    /**
     * Move to the shipping method step.
     */
    protected function moveToShippingStep()
    {
        $this->currentStep = 'shipping';

        $this->shippingMethods = collect(Shipping::collectRates()['shippingMethods'])
            ->map(function ($method) {
                $method['rates'] = collect($method['rates'])->map->toArray()->toArray();

                return $method;
            })
            ->toArray();
    }

    /**
     * Move to the payment method step.
     */
    protected function moveToPaymentStep()
    {
        $this->currentStep = 'payment';
    }

    /**
     * Get the address validator.
     */
    protected function getAddressValidator(array $data)
    {
        $request = new CartAddressRequest;
        $request->merge($data);

        $validator = Validator::make($data, $request->rules());

        $validator->setAttributeNames(
            collect($request->rules())
                ->keys()
                ->mapWithKeys(function ($key) {
                    $parts = explode('.', $key);
                    $field = str_replace('_', ' ', end($parts));

                    return [$key => $field];
                })
                ->toArray()
        );

        return $validator;
    }

    /**
     * Get view data for the component.
     */
    public function getViewData(): array
    {
        $data = [
            'cartResource' => $this->getCartResource(),
            'countries' => app('core')->countries(),
            'states' => app('core')->groupedStatesByCountries(),
            'savedAddresses' => [],
        ];

        if (Auth::guard('customer')->check()) {
            $data['savedAddresses'] = Auth::guard('customer')
                ->user()
                ->addresses
                ->map(function ($address) {
                    $address->address = explode(PHP_EOL, $address->address);

                    return $address->toArray();
                })
                ->toArray();
        }

        return $data;
    }
}
