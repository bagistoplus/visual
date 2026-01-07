export function updateUrlParam(key: string, value: string): void {
  const url = new URL(window.location.href);
  url.searchParams.set(key, value);
  window.history.replaceState({}, '', url.toString());
}

export function getUrlParam(key: string): string | null {
  const url = new URL(window.location.href);
  return url.searchParams.get(key);
}

export function removeUrlParam(key: string): void {
  const url = new URL(window.location.href);
  url.searchParams.delete(key);
  window.history.replaceState({}, '', url.toString());
}
