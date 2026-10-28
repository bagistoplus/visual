import type { CraftileEditor } from '@craftile/editor';
import { debounce } from 'perfect-debounce';

import type { State } from '../../state';
import { persistThemeSettings } from '../../api';
import { livePreviewUrl } from '../../utils/previewUrl';

let queueActive: ((id: string, value: any) => void) | null = null;
let flushActive: (() => Promise<boolean>) | null = null;

export function queueThemeSetting(id: string, value: any) {
  queueActive?.(id, value);
}

/**
 * Save every theme setting that is waiting for its debounce or was not saved,
 * and wait for a save already on its way. Whether everything was saved.
 */
export function flushThemeSettings(): Promise<boolean> {
  return flushActive ? flushActive() : Promise.resolve(true);
}

export function setupThemeSettingsPersistence(editor: CraftileEditor, state: State) {
  let pendingSettings: Record<string, any> = {};
  let failedSettings: Record<string, any> = {};
  let inFlight: Promise<boolean> | null = null;

  function hasUnsavedSettings() {
    return Object.keys(pendingSettings).length > 0 || Object.keys(failedSettings).length > 0;
  }

  function persistNow(): Promise<boolean> {
    if (inFlight) {
      return inFlight;
    }

    if (!hasUnsavedSettings()) {
      return Promise.resolve(true);
    }

    inFlight = send().finally(() => {
      inFlight = null;
    });

    return inFlight;
  }

  async function send(): Promise<boolean> {
    // A value changed since a failed save replaces the failed one.
    const settings = { ...failedSettings, ...pendingSettings };

    failedSettings = {};
    pendingSettings = {};

    let saved = false;

    const request = persistThemeSettings(
      settings,
      livePreviewUrl(editor.preview.getFrame(), state.pageData?.url || '')
    );

    request.onSuccess((html) => {
      saved = true;

      if (html) {
        editor.preview.sendMessage('page.refresh' as any, { html });
      } else {
        editor.preview.reload();
      }
    });

    request.onError(() => {
      editor.ui.toast({
        type: 'error',
        title: 'Failed to save theme settings',
      });

      failedSettings = settings;
    });

    request.onFinish(() => {
      if (Object.keys(pendingSettings).length > 0) {
        debouncedPersist();
      }
    });

    await request.execute();

    return saved;
  }

  const debouncedPersist = debounce(persistNow, 500);

  queueActive = (id, value) => {
    pendingSettings[id] = value;

    debouncedPersist();
  };

  flushActive = async () => {
    while (inFlight || hasUnsavedSettings()) {
      if (!(await persistNow())) {
        return false;
      }
    }

    return true;
  };
}
