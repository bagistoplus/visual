import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import LocaleSelector from '../../components/LocaleSelector.vue';
import { createState } from '../../state';

function mountSelector() {
  (window as any).editorConfig = {
    messages: {
      Inherits: 'Inherits',
      Locales: 'Locales',
    },
    channels: [
      {
        code: 'default',
        name: 'Default',
        default_locale: 'en',
        locales: [
          { code: 'en', name: 'English', logo_url: '' },
          { code: 'ar', name: 'Arabic', logo_url: '' },
          { code: 'fr', name: 'French', logo_url: '' },
        ],
      },
      {
        code: 'uea',
        name: 'UEA',
        default_locale: 'en',
        locales: [
          { code: 'en', name: 'English', logo_url: '' },
          { code: 'ar', name: 'Arabic', logo_url: '' },
          { code: 'fr', name: 'French', logo_url: '' },
        ],
      },
    ],
  };

  createState({
    channel: 'uea',
    locale: 'ar',
    localeInheritance: {
      en: { parentChannel: 'default', parentLocale: 'en' },
      ar: { parentChannel: 'default', parentLocale: 'ar' },
      fr: { parentChannel: 'uea', parentLocale: 'en' },
    },
  });

  return mount(LocaleSelector, {
    props: {
      channel: 'uea',
      modelValue: 'ar',
    },
    global: {
      stubs: {
        'Menu.Root': { template: '<div><slot /></div>' },
        'Menu.Trigger': { template: '<div><slot /></div>' },
        'Menu.Indicator': { template: '<span><slot /></span>' },
        'Menu.Positioner': { template: '<div><slot /></div>' },
        'Menu.Content': { template: '<div><slot /></div>' },
        'Menu.ItemGroup': { template: '<div><slot /></div>' },
        'Menu.ItemGroupLabel': { template: '<div><slot /></div>' },
        'Menu.Item': { template: '<div><slot /></div>' },
        Button: { template: '<button><slot /></button>' },
        'i-heroicons-globe-asia-australia': true,
        'i-heroicons-chevron-down': true,
        'i-heroicons-arrow-turn-down-right': true,
      },
    },
  });
}

describe('LocaleSelector', () => {
  it('shows inheritance labels for locale options that have a parent', () => {
    const wrapper = mountSelector();

    expect(wrapper.text()).toContain('English');
    expect(wrapper.text()).toContain('Inherits Default / English');
    expect(wrapper.text()).toContain('Arabic');
    expect(wrapper.text()).toContain('Inherits Default / Arabic');
    expect(wrapper.text()).toContain('French');
    expect(wrapper.text()).toContain('Inherits English');
  });
});
