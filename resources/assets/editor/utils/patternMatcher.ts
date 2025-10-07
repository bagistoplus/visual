// Cache for compiled regex patterns
const patternCache = new Map<string, RegExp>();

/**
 * Match a string against a glob-style pattern.
 * Supports '*' wildcard for partial matches.
 *
 * @example
 * matchPattern('accounts/dashboard', 'accounts/*') // true
 * matchPattern('products/shoes', 'products/*') // true
 * matchPattern('index', '*') // true
 * matchPattern('accounts/dashboard', 'products/*') // false
 */
export function matchPattern(value: string, pattern: string): boolean {
  if (pattern === value) {
    return true;
  }

  if (pattern === '*') {
    return true;
  }

  // Fast path: prefix match (accounts/*)
  if (pattern.endsWith('/*')) {
    const prefix = pattern.slice(0, -2);
    return value.startsWith(prefix + '/') || value === prefix;
  }

  // Fast path: suffix match (*/login)
  if (pattern.startsWith('*/')) {
    const suffix = pattern.slice(2);
    return value.endsWith('/' + suffix) || value === suffix;
  }

  let regex = patternCache.get(pattern);

  if (!regex) {
    try {
      const escapedPattern = pattern.replace(/[.+?^${}()|[\]\\]/g, '\\$&').replace(/\*/g, '.*');
      regex = new RegExp(`^${escapedPattern}$`);
      patternCache.set(pattern, regex);
    } catch (error) {
      if (import.meta.env.DEV) {
        console.warn(`[PatternMatcher] Invalid pattern: "${pattern}"`, error);
      }
      return false;
    }
  }

  return regex.test(value);
}

/**
 * Check if a value matches any pattern in a list.
 */
export function matchAnyPattern(value: string, patterns: string[]): boolean {
  return patterns.some((pattern) => matchPattern(value, pattern));
}

/**
 * Check if conditions match the current context.
 *
 * @param condition - Object with optional 'regions' and 'templates' arrays
 * @param context - Object with current 'region' and 'template'
 * @returns true if conditions match, false otherwise
 */
export function matchesCondition(
  condition: { regions?: string[]; templates?: string[] },
  context: { region?: string; template?: string }
): boolean {
  const hasRegionCondition = condition.regions && condition.regions.length > 0;
  const hasTemplateCondition = condition.templates && condition.templates.length > 0;

  // No conditions specified = no match (block visible everywhere by default)
  if (!hasRegionCondition && !hasTemplateCondition) {
    return false;
  }

  // Check region match if condition specified
  let regionMatch = true;
  if (hasRegionCondition) {
    if (!context.region) {
      regionMatch = false;
    } else {
      regionMatch = matchAnyPattern(context.region, condition.regions!);
    }
  }

  // Check template match if condition specified
  let templateMatch = true;
  if (hasTemplateCondition) {
    if (!context.template) {
      templateMatch = false;
    } else {
      templateMatch = matchAnyPattern(context.template, condition.templates!);
    }
  }

  // Both must match (AND logic)
  return regionMatch && templateMatch;
}
