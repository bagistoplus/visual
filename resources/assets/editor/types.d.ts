export type ViewMode = "desktop" | "mobile" | "fullscreen";

export interface WindowThemeEditor {
  baseUrl: string;
  storefrontUrl: string;
  channels: Channel[];
  defaultChannel: string;
  routes: {
    persistTheme: string;
    themesIndex: string
  }
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
  group?: string;
  [key: string]: unknown;
}

interface Block {
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

export interface SectionData {
  id: string;
  type: string;
  name: string;
  disabled: boolean;
  settings: Record<string, unknown>;
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
