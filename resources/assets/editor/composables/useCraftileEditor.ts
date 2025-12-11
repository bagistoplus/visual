import { PluginContext } from '@craftile/editor';
import { CRAFTILE_EDITOR } from '../plugin';

export function useCraftileEditor() {
  return inject<PluginContext['editor']>(CRAFTILE_EDITOR);
}
