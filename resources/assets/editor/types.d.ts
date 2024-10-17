export type ViewMode = "desktop" | "mobile" | "fullscreen";

export interface WindowThemeEditor {
  baseUrl: string;
  imagesBaseUrl: string;
  storefrontUrl: string;
  channels: Channel[];
  defaultChannel: string;
  routes: {
    persistTheme: string;
    themesIndex: string;
    uploadImage: string;
    listImages: string;
  };
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
  default?: unknown;
  info?: string;
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
}

export interface BlockData {
  id: string;
  type: string;
  name: string;
  disabled: boolean;
  settings: Record<string, any>;
}

export interface SectionData extends BlockData {
  name: string;
  blocks: Record<string, BlockData>;
  blocks_order: string[];
}

export interface ThemeData {
  url: string;
  channel: string;
  locale: string;
  template: string;
  hasStaticContent: boolean;
  sectionsOrder: string[];
  beforeContentSectionsOrder: string[];
  afterContentSectionsOrder: string[];
  sectionsData: Record<string, SectionData>
}

interface Image {
  name: string;
  path: string;
  url: string;
  uploading?: boolean
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
  },
  translations: any[]
}

interface Product {
  id: number;
  name: string;
  description: string;
  base_image?: {
    large_image_url: string;
    medium_image_url: string;
    original_image_url: string;
    small_image_url: string;
  },
  images: {
    large_image_url: string;
    medium_image_url: string;
    original_image_url: string;
    small_image_url: string
  }[];
}
