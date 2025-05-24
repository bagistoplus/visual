import ThemeEditor from './ThemeEditor';
import type { PreloadedModels, Setting, Template, ThemeData, ThemeEditorConfig } from './types';

declare global {
  interface Window {
    Alpine: any;
    Livewire: any;
    editorConfig: ThemeEditorConfig;
    ThemeEditor: ThemeEditor;

    themeData: ThemeData;
    templates: Template[];
    settingsSchema: { name: string; settings: Setting[] }[];
    preloadedModels: PreloadedModels;
    usedColors: string[];
  }
}
