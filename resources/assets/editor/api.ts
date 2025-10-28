import { UpdatesEvent } from '@craftile/types';
import { useHttpClient } from './composables/http';
import { useState } from './state';

export function persistUpdates(updates: UpdatesEvent) {
  const { state } = useState();
  const { post } = useHttpClient();

  const request = post(
    window.editorConfig.routes.persistUpdates,
    {
      theme: state.theme?.code,
      channel: window.editorConfig.defaultChannel,
      locale: window.editorConfig.editorLocale,
      template: {
        url: state.pageData?.url || '',
        name: state.pageData?.template || 'index',
        sources: state.pageData?.sources,
      },
      updates,
    }
  ).text();

  request.onError((error) => {
    console.error('Failed to persist updates:', error);
  });

  return request;
}

export function persistThemeSettings(updates: Record<string, any>) {
  const { state } = useState();
  const { post } = useHttpClient();

  const request = post(
    window.editorConfig.routes.persistThemeSettings,
    {
      theme: state.theme?.code || 'sections-pro',
      channel: state.channel || window.editorConfig.defaultChannel,
      locale: state.locale || window.editorConfig.editorLocale,
      template: {
        url: state.pageData?.url || '',
        name: state.pageData?.template || 'index',
        sources: state.pageData?.sources,
      },
      updates,
    }
  );

  request.onError((error) => {
    console.error('Failed to persist theme settings:', error);
  });

  return request;
}

export function publishTheme() {
  const { state } = useState();
  const { post } = useHttpClient();

  const request = post(
    window.editorConfig.routes.publishTheme,
    {
      theme: state.theme?.code || 'sections-pro',
    }
  );

  request.onError((error) => {
    console.error('Failed to publish theme:', error);
  });

  return request;
}
