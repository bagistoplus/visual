import { describe, it, expect } from 'vitest';
import { matchPattern, matchAnyPattern, matchesCondition } from '../../utils/patternMatcher';

describe('patternMatcher utilities', () => {
  describe('matchPattern', () => {
    describe('exact matches', () => {
      it('should match exact strings', () => {
        expect(matchPattern('accounts/dashboard', 'accounts/dashboard')).toBe(true);
        expect(matchPattern('index', 'index')).toBe(true);
        expect(matchPattern('products/shoes', 'products/shoes')).toBe(true);
      });

      it('should not match different strings', () => {
        expect(matchPattern('accounts/dashboard', 'accounts/settings')).toBe(false);
        expect(matchPattern('products', 'product')).toBe(false);
      });
    });

    describe('wildcard matches', () => {
      it('should match everything with single asterisk', () => {
        expect(matchPattern('accounts/dashboard', '*')).toBe(true);
        expect(matchPattern('products/shoes', '*')).toBe(true);
        expect(matchPattern('index', '*')).toBe(true);
        expect(matchPattern('', '*')).toBe(true);
      });

      it('should match prefix patterns (accounts/*)', () => {
        expect(matchPattern('accounts/dashboard', 'accounts/*')).toBe(true);
        expect(matchPattern('accounts/settings', 'accounts/*')).toBe(true);
        expect(matchPattern('accounts/profile/edit', 'accounts/*')).toBe(true);
        expect(matchPattern('accounts', 'accounts/*')).toBe(true);
      });

      it('should not match prefix patterns incorrectly', () => {
        expect(matchPattern('products/shoes', 'accounts/*')).toBe(false);
        expect(matchPattern('account', 'accounts/*')).toBe(false);
        expect(matchPattern('accountsettings', 'accounts/*')).toBe(false);
      });

      it('should match suffix patterns (*/login)', () => {
        expect(matchPattern('accounts/login', '*/login')).toBe(true);
        expect(matchPattern('admin/login', '*/login')).toBe(true);
        expect(matchPattern('customer/auth/login', '*/login')).toBe(true);
        expect(matchPattern('login', '*/login')).toBe(true);
      });

      it('should not match suffix patterns incorrectly', () => {
        expect(matchPattern('accounts/logout', '*/login')).toBe(false);
        expect(matchPattern('loginpage', '*/login')).toBe(false);
        expect(matchPattern('accounts/login/form', '*/login')).toBe(false);
      });

      it('should match middle wildcards (accounts/*/settings)', () => {
        expect(matchPattern('accounts/user/settings', 'accounts/*/settings')).toBe(true);
        expect(matchPattern('accounts/admin/settings', 'accounts/*/settings')).toBe(true);
      });

      it('should match multiple wildcards', () => {
        expect(matchPattern('accounts/user/profile', 'accounts/*/profile')).toBe(true);
        expect(matchPattern('admin/users/123', 'admin/users/*')).toBe(true);
        expect(matchPattern('a/b/c', 'a/*/c')).toBe(true);
      });
    });

    describe('special characters', () => {
      it('should handle dots in patterns', () => {
        expect(matchPattern('file.txt', 'file.txt')).toBe(true);
        expect(matchPattern('index.html', '*.html')).toBe(true);
      });

      it('should escape regex special characters', () => {
        expect(matchPattern('test(1)', 'test(1)')).toBe(true);
        expect(matchPattern('item[0]', 'item[0]')).toBe(true);
        expect(matchPattern('price$10', 'price$10')).toBe(true);
      });
    });

    describe('edge cases', () => {
      it('should handle empty strings', () => {
        expect(matchPattern('', '')).toBe(true);
        expect(matchPattern('', 'something')).toBe(false);
        expect(matchPattern('something', '')).toBe(false);
      });

      it('should handle patterns with only wildcards', () => {
        expect(matchPattern('anything', '***')).toBe(true);
        expect(matchPattern('test', '*test*')).toBe(true);
      });

      it('should handle forward slashes', () => {
        expect(matchPattern('a/b/c', 'a/b/c')).toBe(true);
        expect(matchPattern('a/b/c', 'a/*/c')).toBe(true);
        expect(matchPattern('a/b/c', 'a/*')).toBe(true);
      });
    });
  });

  describe('matchAnyPattern', () => {
    it('should match if any pattern matches', () => {
      const patterns = ['accounts/*', 'products/*', 'index'];

      expect(matchAnyPattern('accounts/dashboard', patterns)).toBe(true);
      expect(matchAnyPattern('products/shoes', patterns)).toBe(true);
      expect(matchAnyPattern('index', patterns)).toBe(true);
    });

    it('should not match if no patterns match', () => {
      const patterns = ['accounts/*', 'products/*', 'index'];

      expect(matchAnyPattern('cart/view', patterns)).toBe(false);
      expect(matchAnyPattern('checkout', patterns)).toBe(false);
    });

    it('should handle empty pattern array', () => {
      expect(matchAnyPattern('anything', [])).toBe(false);
    });

    it('should handle single pattern', () => {
      expect(matchAnyPattern('accounts/login', ['accounts/*'])).toBe(true);
      expect(matchAnyPattern('products/shoes', ['accounts/*'])).toBe(false);
    });

    it('should match with wildcard in array', () => {
      expect(matchAnyPattern('anything', ['*'])).toBe(true);
      expect(matchAnyPattern('something', ['specific', '*'])).toBe(true);
    });
  });

  describe('matchesCondition', () => {
    describe('no conditions', () => {
      it('should return false when no conditions specified', () => {
        expect(matchesCondition({}, { region: 'header', template: 'home' })).toBe(false);
        expect(matchesCondition({}, {})).toBe(false);
      });

      it('should return false when conditions are empty arrays', () => {
        expect(matchesCondition({ regions: [], templates: [] }, { region: 'header' })).toBe(false);
      });
    });

    describe('region conditions only', () => {
      it('should match when region matches', () => {
        const condition = { regions: ['header', 'footer'] };

        expect(matchesCondition(condition, { region: 'header' })).toBe(true);
        expect(matchesCondition(condition, { region: 'footer' })).toBe(true);
      });

      it('should not match when region does not match', () => {
        const condition = { regions: ['header', 'footer'] };

        expect(matchesCondition(condition, { region: 'sidebar' })).toBe(false);
      });

      it('should not match when region is missing from context', () => {
        const condition = { regions: ['header'] };

        expect(matchesCondition(condition, {})).toBe(false);
        expect(matchesCondition(condition, { template: 'home' })).toBe(false);
      });

      it('should match with wildcard patterns', () => {
        const condition = { regions: ['content/*'] };

        expect(matchesCondition(condition, { region: 'content/main' })).toBe(true);
        expect(matchesCondition(condition, { region: 'content/sidebar' })).toBe(true);
        expect(matchesCondition(condition, { region: 'header' })).toBe(false);
      });
    });

    describe('template conditions only', () => {
      it('should match when template matches', () => {
        const condition = { templates: ['home', 'index'] };

        expect(matchesCondition(condition, { template: 'home' })).toBe(true);
        expect(matchesCondition(condition, { template: 'index' })).toBe(true);
      });

      it('should not match when template does not match', () => {
        const condition = { templates: ['home', 'index'] };

        expect(matchesCondition(condition, { template: 'products' })).toBe(false);
      });

      it('should not match when template is missing from context', () => {
        const condition = { templates: ['home'] };

        expect(matchesCondition(condition, {})).toBe(false);
        expect(matchesCondition(condition, { region: 'header' })).toBe(false);
      });

      it('should match with wildcard patterns', () => {
        const condition = { templates: ['products/*'] };

        expect(matchesCondition(condition, { template: 'products/list' })).toBe(true);
        expect(matchesCondition(condition, { template: 'products/view' })).toBe(true);
        expect(matchesCondition(condition, { template: 'home' })).toBe(false);
      });
    });

    describe('combined conditions (AND logic)', () => {
      it('should match when both region and template match', () => {
        const condition = { regions: ['header'], templates: ['home'] };

        expect(matchesCondition(condition, { region: 'header', template: 'home' })).toBe(true);
      });

      it('should not match when only region matches', () => {
        const condition = { regions: ['header'], templates: ['home'] };

        expect(matchesCondition(condition, { region: 'header', template: 'products' })).toBe(false);
      });

      it('should not match when only template matches', () => {
        const condition = { regions: ['header'], templates: ['home'] };

        expect(matchesCondition(condition, { region: 'footer', template: 'home' })).toBe(false);
      });

      it('should not match when neither matches', () => {
        const condition = { regions: ['header'], templates: ['home'] };

        expect(matchesCondition(condition, { region: 'footer', template: 'products' })).toBe(false);
      });

      it('should work with multiple patterns in both conditions', () => {
        const condition = {
          regions: ['header', 'footer'],
          templates: ['home', 'index']
        };

        expect(matchesCondition(condition, { region: 'header', template: 'home' })).toBe(true);
        expect(matchesCondition(condition, { region: 'footer', template: 'index' })).toBe(true);
        expect(matchesCondition(condition, { region: 'header', template: 'products' })).toBe(false);
        expect(matchesCondition(condition, { region: 'sidebar', template: 'home' })).toBe(false);
      });

      it('should work with wildcard patterns in both conditions', () => {
        const condition = {
          regions: ['content/*'],
          templates: ['products/*']
        };

        expect(matchesCondition(condition, {
          region: 'content/main',
          template: 'products/view'
        })).toBe(true);

        expect(matchesCondition(condition, {
          region: 'content/sidebar',
          template: 'products/list'
        })).toBe(true);

        expect(matchesCondition(condition, {
          region: 'header',
          template: 'products/view'
        })).toBe(false);
      });
    });
  });
});
