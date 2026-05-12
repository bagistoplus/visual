import { afterEach, describe, expect, it, vi } from 'vitest';

import { useHttpClient } from '../../composables/http';

describe('useHttpClient', () => {
  afterEach(() => {
    vi.restoreAllMocks();
  });

  it('throws the JSON response message for failed requests', async () => {
    vi.spyOn(globalThis, 'fetch').mockResolvedValue(new Response(
      JSON.stringify({ message: 'Localized failure' }),
      {
        status: 422,
        headers: { 'content-type': 'application/json' },
      },
    ));

    const request = useHttpClient().post('/templates', {});

    await expect(request.execute(undefined, true)).rejects.toThrow('Localized failure');
  });

  it('falls back to the HTTP status when an error response has no JSON message', async () => {
    vi.spyOn(globalThis, 'fetch').mockResolvedValue(new Response('Nope', { status: 500 }));

    const request = useHttpClient().post('/templates', {});

    await expect(request.execute(undefined, true)).rejects.toThrow('HTTP error! status: 500');
  });
});
