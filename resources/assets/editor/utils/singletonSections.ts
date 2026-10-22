/**
 * Whether a page already holds a block of a type.
 *
 * Bagisto allows only one section of a type that declares itself a singleton,
 * and guards it as a section is created. Visual writes sections through the
 * same repository but not through that guard, so the editor stops offering the
 * type once the page holds one.
 */
export function holdsBlockOfType(blocks: Record<string, { type?: string }> | undefined, type: string): boolean {
  return Object.values(blocks ?? {}).some((block) => block?.type === type);
}
