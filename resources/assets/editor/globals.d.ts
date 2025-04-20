import { Section, Setting, Template, ThemeData, WindowThemeEditor } from "./types";
export {}

declare global {
  interface Window {
    Alpine: any;
    Livewire: any;
    ThemeEditor: WindowThemeEditor;

    themeData: ThemeData;
    templates: Template[];
    settingsSchema: { name: string; settings: Setting[] }[];
    usedColors: string[];
  }
}
