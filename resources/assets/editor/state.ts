import { Channel, Image, Template, Theme } from './types';

export const VISUAL_EDITOR_STATE = Symbol('VISUAL_EDITOR_STATE');

export interface State {
  channels: Channel[];
  channel: string;
  locale: string;
  theme: Theme | null;
  templates: Template[];
  pageData: {
    url: string;
    template: string;
    sources: string;
    settings?: Record<string, any>;
  } | null;
  images: Image[];
  haveEdits: boolean;
}

let state: State | null = null;

export function createState(defaults: Partial<State> = {}): State {
  state = reactive({
    channels: defaults.channels || [],
    channel: 'default',
    locale: 'en',
    theme: defaults.theme || null,
    templates: defaults.templates || [],
    pageData: null,
    images: defaults.images || [],
    haveEdits: defaults.haveEdits || false,
  });

  return state;
}

export function useState() {
  if (!state) {
    throw new Error('State not initialized. Make sure to call createState first.');
  }

  return {
    state,
    ...toRefs(state),
    currentTemplate: computed(() => {
      const templateName = state!.pageData?.template;
      return state!.templates.find((t) => t.template === templateName) || null;
    }),
  };
}
