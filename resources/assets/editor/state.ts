import { Channel, Image, Template, Theme } from './types';

export const VISUAL_EDITOR_STATE = Symbol('VISUAL_EDITOR_STATE');

export interface State {
  channels: Channel[];
  channel: string;
  locale: string;
  theme: Theme | null;
  currentTemplate: string | null;
  templates: Template[];
  pageData: {
    url: string;
    template: string;
    sources: string;
  } | null;
  images: Image[];
}

let state: State | null = null;

export function createState(defaults: Partial<State> = {}): State {
  state = reactive({
    channels: defaults.channels || [],
    channel: 'default',
    locale: 'en',
    theme: defaults.theme || null,
    currentTemplate: null,
    templates: [],
    pageData: null,
    images: defaults.images || [],
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
    currentTemplate: computed(() => state!.templates.find((t) => t.template === state!.currentTemplate) || null),
  };
}
