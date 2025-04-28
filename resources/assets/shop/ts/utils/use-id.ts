const counters = new Map<string, number>();

export function useId(prefix = 'id'): string {
  const current = counters.get(prefix) ?? 0;
  const next = current + 1;

  counters.set(prefix, next);

  return `${prefix}-${next}`;
}
