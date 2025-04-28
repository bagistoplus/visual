<?php

namespace BagistoPlus\Visual\Data;

use Illuminate\Contracts\Support\Arrayable;

class AddressData implements Arrayable
{
    public function __construct(
        public ?int $id = null,
        public string $company_name = '',
        public string $email = '',
        public string $first_name = '',
        public string $last_name = '',
        public array $address = [],
        public string $country = '',
        public string $state = '',
        public string $city = '',
        public string $postcode = '',
        public string $phone = '',
        public bool $default_address = false,
        public bool $use_for_shipping = true,
        public bool $save_address = false,
        public string $address_type = '',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            company_name: $data['company_name'] ?? '',
            email: $data['email'] ?? '',
            first_name: $data['first_name'] ?? '',
            last_name: $data['last_name'] ?? '',
            address: is_array($data['address'] ?? null)
                ? $data['address']
                : explode(PHP_EOL, $data['address'] ?? ''),
            country: $data['country'] ?? '',
            state: $data['state'] ?? '',
            city: $data['city'] ?? '',
            postcode: $data['postcode'] ?? '',
            phone: $data['phone'] ?? '',
            address_type: $data['address_type'] ?? '',
            default_address: (bool) ($data['default_address'] ?? false),
            use_for_shipping: (bool) ($data['use_for_shipping'] ?? true),
            save_address: (bool) ($data['save_address'] ?? false),
        );
    }

    public static function empty(): self
    {
        return new self;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'company_name' => $this->company_name,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'address' => $this->address,
            'country' => $this->country,
            'state' => $this->state,
            'city' => $this->city,
            'postcode' => $this->postcode,
            'phone' => $this->phone,
            'address_type' => $this->address_type,
            'default_address' => $this->default_address,
            'use_for_shipping' => $this->use_for_shipping,
            'save_address' => $this->save_address,
        ];
    }
}
