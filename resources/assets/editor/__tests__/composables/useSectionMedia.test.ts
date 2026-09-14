import { describe, expect, it, vi, beforeEach } from 'vitest';

const { listSectionMedia } = vi.hoisted(() => ({ listSectionMedia: vi.fn() }));

vi.mock('../../api', () => ({ listSectionMedia }));

const FILE = { path: 'themes/default/sections/7/a.webp', url: '/storage/a.webp', section: 7 };

function listing(files: any[]) {
  return () => ({
    onError: () => {},
    execute: async () => files,
  });
}

async function freshStore() {
  vi.resetModules();

  const { useSectionMedia } = await import('../../composables/useSectionMedia');

  return useSectionMedia();
}

describe('useSectionMedia', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    listSectionMedia.mockImplementation(listing([FILE]));
  });

  it('reads the library once, however many pickers ask for it', async () => {
    const store = await freshStore();

    await Promise.all([store.load(), store.load()]);
    await store.load();

    expect(listSectionMedia).toHaveBeenCalledTimes(1);
    expect(store.files.value).toEqual([FILE]);
  });

  it('reads it again when asked to', async () => {
    const store = await freshStore();

    await store.load();
    await store.load(true);

    expect(listSectionMedia).toHaveBeenCalledTimes(2);
  });

  it('keeps a file that was just added without reading the library again', async () => {
    const store = await freshStore();

    await store.load();

    const added = { path: 'themes/default/sections/9/b.webp', url: '/storage/b.webp', section: 9 };

    store.remember(added);

    expect(store.files.value).toEqual([added, FILE]);
    expect(listSectionMedia).toHaveBeenCalledTimes(1);
  });

  it('holds one entry per file, however often it is remembered', async () => {
    const store = await freshStore();

    await store.load();
    store.remember(FILE);

    expect(store.files.value).toEqual([FILE]);
  });

  it('finds a file by the path it is stored under', async () => {
    const store = await freshStore();

    await store.load();

    expect(store.find(FILE.path)).toEqual(FILE);
    expect(store.find('themes/default/sections/7/gone.webp')).toBeNull();
    expect(store.find(null)).toBeNull();
  });

  it('finds a file stored under the path an installer seeded it as', async () => {
    const store = await freshStore();

    await store.load();

    expect(store.find('storage/themes/default/sections/7/a.webp')).toEqual(FILE);
    expect(store.find('/storage/themes/default/sections/7/a.webp')).toEqual(FILE);
  });

  it('answers an empty library when the listing fails', async () => {
    listSectionMedia.mockImplementation(() => ({ onError: () => {}, execute: async () => null }));

    const store = await freshStore();

    await store.load();

    expect(store.files.value).toEqual([]);
  });
});
