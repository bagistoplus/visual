import { describe, it, expect, beforeEach, vi } from 'vitest';
import { updateUrlParam, getUrlParam, removeUrlParam } from '../../utils/urlState';

describe('urlState utilities', () => {
  beforeEach(() => {
    // Reset window.location before each test
    delete (window as any).location;
    (window as any).location = new URL('http://localhost:3000/editor');

    // Mock history.replaceState
    window.history.replaceState = vi.fn();
  });

  describe('updateUrlParam', () => {
    it('should add a new URL parameter', () => {
      updateUrlParam('template', 'home');

      expect(window.history.replaceState).toHaveBeenCalledWith(
        {},
        '',
        'http://localhost:3000/editor?template=home'
      );
    });

    it('should update an existing URL parameter', () => {
      (window as any).location = new URL('http://localhost:3000/editor?template=home');

      updateUrlParam('template', 'about');

      expect(window.history.replaceState).toHaveBeenCalledWith(
        {},
        '',
        'http://localhost:3000/editor?template=about'
      );
    });

    it('should preserve other URL parameters', () => {
      (window as any).location = new URL('http://localhost:3000/editor?foo=bar');

      updateUrlParam('template', 'home');

      expect(window.history.replaceState).toHaveBeenCalledWith(
        {},
        '',
        'http://localhost:3000/editor?foo=bar&template=home'
      );
    });

    it('should handle special characters in values', () => {
      updateUrlParam('block', 'block-id-123');

      expect(window.history.replaceState).toHaveBeenCalledWith(
        {},
        '',
        'http://localhost:3000/editor?block=block-id-123'
      );
    });
  });

  describe('getUrlParam', () => {
    it('should return the value of an existing parameter', () => {
      (window as any).location = new URL('http://localhost:3000/editor?template=home');

      const value = getUrlParam('template');

      expect(value).toBe('home');
    });

    it('should return null for a non-existent parameter', () => {
      const value = getUrlParam('template');

      expect(value).toBeNull();
    });

    it('should return the correct value when multiple parameters exist', () => {
      (window as any).location = new URL('http://localhost:3000/editor?foo=bar&template=home&baz=qux');

      const value = getUrlParam('template');

      expect(value).toBe('home');
    });

    it('should handle URL-encoded values', () => {
      (window as any).location = new URL('http://localhost:3000/editor?block=block%2Did%2D123');

      const value = getUrlParam('block');

      expect(value).toBe('block-id-123');
    });
  });

  describe('removeUrlParam', () => {
    it('should remove an existing parameter', () => {
      (window as any).location = new URL('http://localhost:3000/editor?template=home');

      removeUrlParam('template');

      expect(window.history.replaceState).toHaveBeenCalledWith(
        {},
        '',
        'http://localhost:3000/editor'
      );
    });

    it('should preserve other parameters when removing one', () => {
      (window as any).location = new URL('http://localhost:3000/editor?template=home&block=123');

      removeUrlParam('block');

      expect(window.history.replaceState).toHaveBeenCalledWith(
        {},
        '',
        'http://localhost:3000/editor?template=home'
      );
    });

    it('should do nothing if parameter does not exist', () => {
      (window as any).location = new URL('http://localhost:3000/editor?foo=bar');

      removeUrlParam('template');

      expect(window.history.replaceState).toHaveBeenCalledWith(
        {},
        '',
        'http://localhost:3000/editor?foo=bar'
      );
    });

    it('should handle removing the last parameter', () => {
      (window as any).location = new URL('http://localhost:3000/editor?template=home');

      removeUrlParam('template');

      expect(window.history.replaceState).toHaveBeenCalledWith(
        {},
        '',
        'http://localhost:3000/editor'
      );
    });
  });
});
