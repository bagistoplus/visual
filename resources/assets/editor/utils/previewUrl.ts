/**
 * The address the preview is showing, which is not the address it was loaded
 * with: Livewire's `#[Url]` properties rewrite it through `replaceState`, and a
 * re-render has to replay them or the component mounts without them.
 */
export function livePreviewUrl(frame: HTMLIFrameElement | null, fallback: string): string {
  try {
    return frame?.contentWindow?.location.href || fallback;
  } catch {
    return fallback;
  }
}
