import { PluginContext } from '@craftile/editor';
import { CRAFTILE_EDITOR } from '../craftile/plugin';

export function useCraftileEditor() {
  return inject<PluginContext['editor']>(CRAFTILE_EDITOR);
}
