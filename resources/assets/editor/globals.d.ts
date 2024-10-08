import { Section, ThemeData, WindowThemeEditor } from "./types";

export {}

declare global {
  interface Window {
    ThemeEditor: WindowThemeEditor;

    availableSections: Record<string, Section>;
    themeData: ThemeData;
  }
}
