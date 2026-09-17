import { describe, it, expect } from 'vitest';
import { livePreviewUrl } from '../../utils/previewUrl';

function frameAt(href: string) {
  return { contentWindow: { location: { href } } } as unknown as HTMLIFrameElement;
}

describe('livePreviewUrl', () => {
  it('reads the address the frame is showing', () => {
    const url = 'http://localhost:8000/men?_designMode=debut&limit=6&sort=name-asc';

    expect(livePreviewUrl(frameAt(url), 'http://localhost:8000/men')).toBe(url);
  });

  it('falls back when there is no frame', () => {
    expect(livePreviewUrl(null, 'http://localhost:8000/men')).toBe('http://localhost:8000/men');
  });

  it('falls back when the frame is on another origin', () => {
    const frame = {
      get contentWindow(): never {
        throw new DOMException('Blocked a frame with origin', 'SecurityError');
      },
    } as unknown as HTMLIFrameElement;

    expect(livePreviewUrl(frame, 'http://localhost:8000/men')).toBe('http://localhost:8000/men');
  });
});
