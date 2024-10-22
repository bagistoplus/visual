import { Section, Setting, ThemeData, WindowThemeEditor } from "./types";

export {}

declare global {
  interface Window {
    ThemeEditor: WindowThemeEditor;

    availableSections: Record<string, Section>;
    themeData: ThemeData;
    settingsSchema: { name: string; settings: Setting[] }[];
    usedColors: string[];
  }
}
