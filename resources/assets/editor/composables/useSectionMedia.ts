import { listSectionMedia, type SectionMedia } from '../api';

/**
 * The files a Bagisto theme's sections hold, read once and shared by every
 * picker.
 *
 * A picker needs the whole library to draw the file it is already pointed at,
 * since only the server knows what url a stored path is served from. Reading it
 * per picker would mean a request per image field on the page.
 */
const files = ref<SectionMedia[]>([]);
const loading = ref(false);

let read: Promise<void> | null = null;

async function fetchMedia() {
  loading.value = true;

  const request = listSectionMedia();

  request.onError((error) => console.error('Failed to list section media:', error));

  files.value = (await request.execute()) ?? [];

  loading.value = false;
}

/**
 * Read the library, once, unless asked to read it again: a file added by
 * another picker is remembered rather than re-read.
 */
function load(force = false) {
  if (force) {
    read = null;
  }

  return (read ??= fetchMedia());
}

/** Keep a file the merchant just added, newest first, as the listing reports them. */
function remember(file: SectionMedia) {
  files.value = [file, ...files.value.filter((held) => held.path !== file.path)];
}

/**
 * A stored path as the disk holds it.
 *
 * Bagisto's own editor writes a path relative to the disk, but the paths its
 * installer seeds are rooted in the public directory, and the storefront reads
 * that prefix off before resolving. Both forms name the same file.
 */
export function normalizeMediaPath(path: string): string {
  return path.replace(/^\/+/, '').replace(/^storage\//, '');
}

function find(path: string | null | undefined): SectionMedia | null {
  if (!path) {
    return null;
  }

  const wanted = normalizeMediaPath(path);

  return files.value.find((file) => normalizeMediaPath(file.path) === wanted) ?? null;
}

export function useSectionMedia() {
  return {
    files: computed(() => files.value),
    loading: computed(() => loading.value),
    load,
    remember,
    find,
  };
}
