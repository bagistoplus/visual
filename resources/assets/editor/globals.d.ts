import { Section, Setting, ThemeData, WindowThemeEditor } from "./types";
export {}

declare global {
  interface Window {
    Alpine: any;
    Livewire: any;
    ThemeEditor: WindowThemeEditor;

    themeData: ThemeData;
    settingsSchema: { name: string; settings: Setting[] }[];
    usedColors: string[];
  }
}
