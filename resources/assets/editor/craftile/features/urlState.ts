import type { CraftileEditor } from '@craftile/editor';

import type { State } from '../../state';
import type { Channel, ThemeEditorConfig } from '../../types';
import { updateUrlParam, removeUrlParam, getUrlParam } from '../../utils/urlState';

export function setupBlockPersistence(editor: CraftileEditor) {
  editor.events.on('ui:block:select', ({ blockId }: { blockId: string }) => {
    updateUrlParam('block', blockId);
  });

  editor.events.on('ui:block:clear-selection', () => {
    removeUrlParam('block');
  });
}

export function loadTemplateFromUrl(editor: CraftileEditor, state: State, editorConfig: ThemeEditorConfig) {
  const urlTemplate = getUrlParam('template');
  const template = urlTemplate && editorConfig.templates?.find((t) => t.template === urlTemplate);

  if (template) {
    const url = new URL(resolvePreviewUrl(template.previewUrl));
    url.searchParams.set('_designMode', state.theme?.code as string);
    url.searchParams.set('channel', state.channel);
    url.searchParams.set('locale', state.locale);

    if (template.type && template.template !== template.type) {
      url.searchParams.set('_template', template.template);
    }

    editor.preview.loadUrl(url.href);
  } else {
    editor.preview.loadUrl(editorConfig.storefrontUrl);
  }
}

export function resolvePreviewUrl(defaultUrl: string): string {
  const previewUrl = getUrlParam('previewUrl');

  if (!previewUrl) {
    return defaultUrl;
  }

  try {
    const url = new URL(previewUrl, window.location.origin);

    if (url.origin === window.location.origin) {
      return url.href;
    }
  } catch {
    return defaultUrl;
  }

  return defaultUrl;
}

export function initialChannelFromUrl(channels: Channel[], fallback: string): string {
  const requestedChannel = getUrlParam('channel');

  return requestedChannel && channels.some((channel) => channel.code === requestedChannel)
    ? requestedChannel
    : fallback;
}

export function initialLocaleFromUrl(channels: Channel[], channelCode: string, fallback: string): string {
  const requestedLocale = getUrlParam('locale');
  const channel = channels.find((channel) => channel.code === channelCode);

  if (requestedLocale && channel?.locales.some((locale) => locale.code === requestedLocale)) {
    return requestedLocale;
  }

  return channel?.default_locale || fallback;
}

export function setupUrlState(editor: CraftileEditor, state: State, editorConfig: ThemeEditorConfig) {
  setupBlockPersistence(editor);
  loadTemplateFromUrl(editor, state, editorConfig);
}
