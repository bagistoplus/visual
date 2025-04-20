import { Livewire, Alpine } from '../../../../vendor/livewire/livewire/dist/livewire.esm';

import collapsible from './components/ui/collapsible';
import accordion from './components/ui/accordion';
import dropdown from './components/ui/dropdown';
import numberInput from './components/ui/number-input';
import slider from './components/ui/slider';
import toasts, { toaster } from './components/ui/toasts';
import modal from './components/ui/modal';
import modals from './plugins/modals';
import confirm from './plugins/confirm';
import helpers from './plugins/helpers';
import addressForm from './features/address-form';
import imageSearch from './features/image-search';
import datagrid from './features/datagrid';
import tabs from './components/ui/tabs';
import mediaGallery from './features/media-gallery';
import productsCompare from './features/products-compare';
import productBundle from './features/product-bundle';
import productForm from './features/product-form';
import variantPicker from './features/variant-picker';
import navigation from './features/navigation';
import productActions from './features/product-actions';

// ui components as plugin
Alpine.plugin(accordion);
Alpine.plugin(collapsible);
Alpine.plugin(dropdown);
Alpine.plugin(modal);
Alpine.plugin(numberInput);
Alpine.plugin(slider);
Alpine.plugin(tabs);
Alpine.plugin(toasts);

// plugins
Alpine.plugin(confirm);
Alpine.plugin(modals);
Alpine.plugin(toaster);
Alpine.plugin(helpers);

// feature components as plugin
Alpine.plugin(addressForm);
Alpine.plugin(datagrid);
Alpine.plugin(imageSearch);
Alpine.plugin(mediaGallery);
Alpine.plugin(navigation);
Alpine.plugin(productForm);
Alpine.plugin(productActions);
Alpine.plugin(productBundle);
Alpine.plugin(productsCompare);
Alpine.plugin(variantPicker);

Livewire.start();
