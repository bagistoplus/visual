import { beforeEach, describe, expect, it, vi } from 'vitest';

const { publishTheme, flushPendingUpdates, toast } = vi.hoisted(() => ({
  publishTheme: vi.fn(),
  flushPendingUpdates: vi.fn(),
  toast: vi.fn(),
}));

vi.mock('../../api', () => ({ publishTheme }));
vi.mock('../../craftile/features/updatePersistence', () => ({ flushPendingUpdates }));
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
  });

  it('saves pending edits before publishing', async () => {
    const order: string[] = [];
    flushPendingUpdates.mockImplementation(async () => {
      order.push('flush');

      return true;
    });
    publishTheme.mockImplementation(() => {
      order.push('publish');

      return request();
    });

    await publish();

    expect(order).toEqual(['flush', 'publish']);
    expect(publishTheme).toHaveBeenCalledWith();
  });

  it('does not publish when an edit could not be saved', async () => {
    flushPendingUpdates.mockResolvedValue(false);

    await publish();

    expect(publishTheme).not.toHaveBeenCalled();
  });
});
