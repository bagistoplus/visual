import { defineComponent, defineScope } from '../utils/define-component';

type StatesByCountry = Record<string, { code: string; default_name: string }[]>;

interface Address {
  id?: number;
  company_name?: string;
  vat_id?: string;
  first_name: string;
  last_name: string;
  email: string;
  address: string[];
  country: string;
  state: string;
  city: string;
  postcode: string;
  phone: string;
  save_address?: boolean;
  use_for_shipping?: boolean;
  default_address?: boolean;
}

interface AddressFormSetup {
  selectedCountry: string;
  statesByCountry: StatesByCountry;
  states: StatesByCountry[string];
  haveStates: boolean;

  // used by checkout form
  name: 'billingAddress' | 'shippingAddress';
  initialAddress?: Address;
  showAddressFields?: boolean;

  fillAddressFields(address: Partial<Address>): void;
  resetInitialAddress(): void;
  clearAddressFields(): void;
  editAddress(address: Address): void;
}

interface AddressScope {
  address: Address;
  select(): void;
  edit(): void;
}

type AddressFormAPI = AddressFormSetup & {
  $address: AddressScope;
};

export default defineComponent<AddressFormAPI>({
  name: 'address-form',

  setup: (props) => ({
    selectedCountry: props.selectedCountry ?? '',
    statesByCountry: props.statesByCountry ?? {},

    name: props.name ?? 'billingAddress',
    initialAddress: props.initialAddress,
    showAddressFields: props.showAddressFields ?? true,

    get states() {
      return this.statesByCountry[this.selectedCountry] ?? [];
    },

    get haveStates() {
      return this.states.length > 0;
    },

    fillAddressFields(address) {
      const newAddress: any = {};

      for (const key of Object.keys(
        this.initialAddress as any
      ) as (keyof Address)[]) {
        newAddress[key] = address[key] ?? '';
      }

      this.selectedCountry = newAddress.country;
      this.$wire.set(this.name, newAddress, false);
    },

    resetInitialAddress() {
      this.fillAddressFields({ ...this.initialAddress });
    },

    clearAddressFields() {
      this.initialAddress = { ...this.$wire[this.name] };
      this.fillAddressFields({
        address: [],
        use_for_shipping: this.initialAddress?.use_for_shipping,
        save_address: this.initialAddress?.save_address,
      });
    },

    editAddress(address) {
      this.fillAddressFields(address);
      this.showAddressFields = true;
    },
  }),

  parts: {
    country() {
      return {
        'x-model': 'selectedCountry',
      };
    },

    address: defineScope<AddressFormAPI, 'address', AddressScope>({
      name: 'address',
      setup: (api, _, { value }) => ({
        address: value,

        select() {
          api.fillAddressFields(this.address);
        },

        edit() {
          api.editAddress(this.address);
        },
      }),
    }),

    addressTrigger(api) {
      return {
        'x-on:click': () => api.$address.select(),
      };
    },

    addressEdit(api, _, { value }) {
      return {
        'x-on:click': () => {
          api.editAddress(value);
        },
      };
    },

    addTrigger(api) {
      return {
        'x-on:click': () => {
          api.showAddressFields = true;
          api.clearAddressFields();
        },
      };
    },

    cancelTrigger(api) {
      return {
        'x-show': () => api.showAddressFields,
        'x-on:click': () => {
          api.showAddressFields = false;
          api.resetInitialAddress();
        },
      };
    },
  },
});
