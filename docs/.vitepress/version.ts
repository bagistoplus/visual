import { execSync } from 'child_process';

export function getVersion(): string {
  try {
    // Get the most recent git tag
    const tag = execSync('git describe --tags --abbrev=0').toString().trim();
    return tag.replace(/^v/, ''); // Remove 'v' prefix if exists
  } catch {
    return 'v2'; // Fallback if no tags exist
  }
}
