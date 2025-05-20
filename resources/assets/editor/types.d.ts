export type ViewMode = 'desktop' | 'mobile' | 'fullscreen' | 'reordering';
export type SettingValue = string | number | boolean | object | null | undefined;

export interface ThemeEditorConfig {
  baseUrl: string;
  imagesBaseUrl: string;
  storefrontUrl: string;
  channels: Channel[];
  defaultChannel: string;
  sections: Record<string, Section>;
  templates: Template[];
  routes: {
    persistTheme: string;
    publishTheme: string;
    themesIndex: string;
    uploadImage: string;
    listImages: string;
    getCmsPages: string;
    getIcons: string;
  };
  messages: Record<string, any>;
  editorLocale: string;
}

interface Template {
  template: string;
  label: string;
  icon: string;
  previewUrl: string;
}

interface Locale {
  code: string;
  name: string;
  logo_url: string;
}

interface Channel {
  code: string;
  name: string;
  locales: Locale[];
  default_locale: string;
}

export interface Setting {
  type: string;
  id: string;
  label: string;
  default?: SettingValue;
  info?: string;
  component: string;
  [key: string]: any;
}

export interface Block {
  type: string;
  name: string;
  limit: number;
  description: string;
  settings: Setting[];
}

export interface Section {
  slug: string;
  name: string;
  description: string;
  previewImageUrl: string;
  previewDescription: string;
  settings: Setting[];
  blocks: Block[];
  maxBlocks: number;
  enabledOn: string[];
  disabledOn: string[];
  default: {
    settings?: Record<string, SettingValue>;
    blocks?: {
      type: string;
      settings?: Record<string, SettingValue>;
    }[];
  };
}

export interface BlockData {
  id: string;
  type: string;
  name: string;
  disabled: boolean;
  settings: Record<string, SettingValue>;
}

export interface SectionData extends BlockData {
  name: string;
  blocks: Record<string, BlockData>;
  blocks_order: string[];
}

export interface ThemeData {
  url: string;
  theme: string;
  channel: string;
  locale: string;
  template: string;
  source: string;
  hasStaticContent: boolean;
  sectionsOrder: string[];
  beforeContentSectionsOrder: string[];
  afterContentSectionsOrder: string[];
  sectionsData: Record<string, SectionData>;
  settings: Record<string, SettingValue>;
  haveEdits: boolean;
}

export type SettingsSchema = {
  name: string;
  settings: Setting[];
}[];

interface Image {
  name: string;
  path: string;
  url: string;
  uploading?: boolean;
}

interface Category {
  id: number;
  name: string;
  slug: string;
  logo?: {
    large_image_url: string;
    medium_image_url: string;
    original_image_url: string;
    small_image_url: string;
  };
  translations: any[];
}

interface Product {
  id: number;
  name: string;
  url_key: string;
  description: string;
  base_image?: {
    large_image_url: string;
    medium_image_url: string;
    original_image_url: string;
    small_image_url: string;
  };
  images: {
    large_image_url: string;
    medium_image_url: string;
    original_image_url: string;
    small_image_url: string;
  }[];
}

interface CmsPage {
  id: number;
  url_key: string;
  page_title: string;
  translations: {
    id: number;
    url_key: string;
    page_title: string;
    locale: string;
  }[];
}

type ColorSchemeDefintion = {
  [K in
    | 'background'
    | 'on-background'
    | 'primary'
    | 'on-primary'
    | 'secondary'
    | 'on-secondary'
    | 'accent'
    | 'on-accent'
    | 'neutral'
    | 'on-neutral'
    | 'surface'
    | 'on-surface'
    | 'surface-alt'
    | 'on-surface-alt'
    | 'success'
    | 'on-success'
    | 'warning'
    | 'on-warning'
    | 'danger'
    | 'on-danger'
    | 'info'
    | 'on-info']: string;
};
