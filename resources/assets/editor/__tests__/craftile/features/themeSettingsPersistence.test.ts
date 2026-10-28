import { afterEach, describe, expect, it, vi } from 'vitest';

const { persistThemeSettingsMock } = vi.hoisted(() => ({
  persistThemeSettingsMock: vi.fn(),
}));

vi.mock('../../../api', () => ({
  persistThemeSettings: persistThemeSettingsMock,
}));

import {
  flushThemeSettings,
  queueThemeSetting,
  setupThemeSettingsPersistence,
} from '../../../craftile/features/themeSettingsPersistence';

afterEach(() => {
  vi.useRealTimers();
  persistThemeSettingsMock.mockReset();
});

function createControlledRequest() {
  const successCallbacks: Array<(response: string) => void> = [];
  const errorCallbacks: Array<(error: Error) => void> = [];
  const finishCallbacks: Array<() => void> = [];
  let resolveExecution: (() => void) | undefined;

  return {
    onSuccess(callback: (response: string) => void) {
      successCallbacks.push(callback);
    },
    onError(callback: (error: Error) => void) {
      errorCallbacks.push(callback);
    },
    onFinish(callback: () => void) {
      finishCallbacks.push(callback);
    },
    execute: vi.fn(
      () =>
        new Promise<void>((resolve) => {
          resolveExecution = resolve;
        })
    ),
    succeed(response: string) {
      successCallbacks.forEach((callback) => callback(response));
      finishCallbacks.forEach((callback) => callback());
      resolveExecution?.();
    },
    fail(error: Error) {
      errorCallbacks.forEach((callback) => callback(error));
      finishCallbacks.forEach((callback) => callback());
      resolveExecution?.();
    },
  };
}

function setup() {
  const editor = {
    preview: {
      getFrame: () => null,
      sendMessage: vi.fn(),
      reload: vi.fn(),
    },
    ui: { toast: vi.fn() },
  };

  setupThemeSettingsPersistence(editor as any, { pageData: { url: 'https://example.test' } } as any);

  return editor;
}

describe('themeSettingsPersistence', () => {
  it('saves the settings changed within the debounce together', async () => {
    vi.useFakeTimers();
    const request = createControlledRequest();
    persistThemeSettingsMock.mockReturnValueOnce(request);
    const editor = setup();

    queueThemeSetting('primary_color', 'red');
    queueThemeSetting('font', 'serif');
    await vi.advanceTimersByTimeAsync(500);

    expect(persistThemeSettingsMock).toHaveBeenCalledTimes(1);
    expect(persistThemeSettingsMock.mock.calls[0][0]).toEqual({ primary_color: 'red', font: 'serif' });

    request.succeed('<html></html>');

    expect(editor.preview.sendMessage).toHaveBeenCalledWith('page.refresh', { html: '<html></html>' });
  });

  it('saves a setting still waiting for its debounce at once', async () => {
    vi.useFakeTimers();
    const request = createControlledRequest();
    persistThemeSettingsMock.mockReturnValueOnce(request);
    setup();

    queueThemeSetting('primary_color', 'red');
    const flushed = flushThemeSettings();

    expect(persistThemeSettingsMock).toHaveBeenCalledTimes(1);
    request.succeed('');

    await expect(flushed).resolves.toBe(true);
  });

  it('waits for a save already on its way', async () => {
    vi.useFakeTimers();
    const request = createControlledRequest();
    persistThemeSettingsMock.mockReturnValueOnce(request);
    setup();

    queueThemeSetting('primary_color', 'red');
    await vi.advanceTimersByTimeAsync(500);

    let settled = false;
    const flushed = flushThemeSettings().then((saved) => {
      settled = true;

      return saved;
    });
    await Promise.resolve();

    expect(settled).toBe(false);
    request.succeed('');

    await expect(flushed).resolves.toBe(true);
    expect(persistThemeSettingsMock).toHaveBeenCalledTimes(1);
  });

  it('saves a setting changed while a save is on its way after that save', async () => {
    vi.useFakeTimers();
    const first = createControlledRequest();
    const second = createControlledRequest();
    persistThemeSettingsMock.mockReturnValueOnce(first).mockReturnValueOnce(second);
    setup();

    queueThemeSetting('primary_color', 'red');
    await vi.advanceTimersByTimeAsync(500);
    queueThemeSetting('font', 'serif');

    const flushed = flushThemeSettings();
    first.succeed('');
    await vi.advanceTimersByTimeAsync(0);

    expect(persistThemeSettingsMock).toHaveBeenCalledTimes(2);
    expect(persistThemeSettingsMock.mock.calls[1][0]).toEqual({ font: 'serif' });
    second.succeed('');

    await expect(flushed).resolves.toBe(true);
  });

  it('reports a failed save, and retries it with newer values winning', async () => {
    vi.useFakeTimers();
    const failing = createControlledRequest();
    const retry = createControlledRequest();
    persistThemeSettingsMock.mockReturnValueOnce(failing).mockReturnValueOnce(retry);
    const editor = setup();

    queueThemeSetting('primary_color', 'red');
    queueThemeSetting('font', 'serif');
    const flushed = flushThemeSettings();
    failing.fail(new Error('Server error'));

    await expect(flushed).resolves.toBe(false);
    expect(editor.ui.toast).toHaveBeenCalledWith(expect.objectContaining({ type: 'error' }));

    queueThemeSetting('primary_color', 'blue');
    const retried = flushThemeSettings();

    expect(persistThemeSettingsMock).toHaveBeenCalledTimes(2);
    expect(persistThemeSettingsMock.mock.calls[1][0]).toEqual({ primary_color: 'blue', font: 'serif' });
    retry.succeed('');

    await expect(retried).resolves.toBe(true);
  });

  it('has nothing to do when every setting is saved', async () => {
    setup();

    await expect(flushThemeSettings()).resolves.toBe(true);
    expect(persistThemeSettingsMock).not.toHaveBeenCalled();
  });
});
