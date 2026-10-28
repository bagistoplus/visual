import { beforeEach, describe, expect, it, vi } from 'vitest';

const { publishTheme, flushPendingUpdates, flushThemeSettings, toast } = vi.hoisted(() => ({
  publishTheme: vi.fn(),
  flushPendingUpdates: vi.fn(),
  flushThemeSettings: vi.fn(),
  toast: vi.fn(),
}));

vi.mock('../../api', () => ({ publishTheme }));
vi.mock('../../craftile/features/updatePersistence', () => ({ flushPendingUpdates }));
vi.mock('../../craftile/features/themeSettingsPersistence', () => ({ flushThemeSettings }));
vi.mock('../../state', () => ({ useState: () => ({ state: { haveEdits: true, draftCount: null } }) }));

function request() {
  let success: (() => void) | undefined;

  return {
    onSuccess: (callback: () => void) => (success = callback),
    onError: vi.fn(),
    execute: vi.fn(async () => success?.()),
  };
}

async function publish() {
  vi.resetModules();
  vi.stubGlobal('useCraftileEditor', () => ({ ui: { toast, closeModal: vi.fn() } }));

  const { usePublish } = await import('../../composables/usePublish');

  await usePublish().publish();
}

describe('usePublish', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    publishTheme.mockImplementation(request);
    flushPendingUpdates.mockResolvedValue(true);
    flushThemeSettings.mockResolvedValue(true);
  });

  it('saves pending edits and theme settings before publishing', async () => {
    const order: string[] = [];
    flushPendingUpdates.mockImplementation(async () => {
      order.push('flush edits');

      return true;
    });
    flushThemeSettings.mockImplementation(async () => {
      order.push('flush theme settings');

      return true;
    });
    publishTheme.mockImplementation(() => {
      order.push('publish');

      return request();
    });

    await publish();

    expect(order).toEqual(['flush edits', 'flush theme settings', 'publish']);
    expect(publishTheme).toHaveBeenCalledWith();
  });

  it('does not publish when an edit could not be saved', async () => {
    flushPendingUpdates.mockResolvedValue(false);

    await publish();

    expect(publishTheme).not.toHaveBeenCalled();
  });

  it('does not publish when a theme setting could not be saved', async () => {
    flushThemeSettings.mockResolvedValue(false);

    await publish();

    expect(publishTheme).not.toHaveBeenCalled();
  });
});
