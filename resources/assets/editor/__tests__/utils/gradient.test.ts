import { describe, expect, it } from 'vitest';
import { gradientToCss, parseGradientCss } from '../../utils/gradient';

describe('gradient utilities', () => {
  it('parses linear gradients with explicit stop positions', () => {
    expect(parseGradientCss('linear-gradient(90deg, #000 0%, rgba(255, 255, 255, 0.5) 100%)')).toEqual({
      type: 'linear',
      angle: 90,
      stops: [
        { color: '#000000ff', position: 0 },
        { color: '#ffffff80', position: 100 },
      ],
    });
  });

  it('parses radial circle gradients with explicit stop positions', () => {
    expect(parseGradientCss('radial-gradient(circle, red 0%, blue 100%)')).toEqual({
      type: 'radial',
      stops: [
        { color: '#ff0000ff', position: 0 },
        { color: '#0000ffff', position: 100 },
      ],
    });
  });

  it('normalizes omitted stop positions using CSS interpolation behavior', () => {
    expect(parseGradientCss('linear-gradient(red, yellow 25%, green, blue)')).toEqual({
      type: 'linear',
      angle: 180,
      stops: [
        { color: '#ff0000ff', position: 0 },
        { color: '#ffff00ff', position: 25 },
        { color: '#008000ff', position: 62.5 },
        { color: '#0000ffff', position: 100 },
      ],
    });
  });

  it('parses simple directional linear gradients', () => {
    expect(parseGradientCss('linear-gradient(to right, red, blue)')).toEqual({
      type: 'linear',
      angle: 90,
      stops: [
        { color: '#ff0000ff', position: 0 },
        { color: '#0000ffff', position: 100 },
      ],
    });
  });

  it('accepts concrete color formats supported by the editor color picker', () => {
    expect(parseGradientCss('linear-gradient(90deg, hsl(0, 100%, 50%) 0%, hsla(240, 100%, 50%, 0.5) 100%)')).toEqual({
      type: 'linear',
      angle: 90,
      stops: [
        { color: '#ff0000ff', position: 0 },
        { color: '#0000ff80', position: 100 },
      ],
    });
  });

  it('renders gradients from the editor model', () => {
    expect(gradientToCss({
      type: 'linear',
      angle: 45,
      stops: [
        { color: '#000000ff', position: 0 },
        { color: '#ffffff80', position: 100 },
      ],
    })).toBe('linear-gradient(45deg, #000000ff 0%, #ffffff80 100%)');
  });

  it.each([
    'linear-gradient(90deg, red 0%, blue 100%), radial-gradient(circle, red 0%, blue 100%)',
    'conic-gradient(red 0%, blue 100%)',
    'repeating-linear-gradient(90deg, red 0%, blue 100%)',
    'radial-gradient(ellipse, red 0%, blue 100%)',
    'radial-gradient(circle at center, red 0%, blue 100%)',
    'linear-gradient(90deg, var(--primary) 0%, blue 100%)',
    'linear-gradient(90deg, currentColor 0%, blue 100%)',
    'linear-gradient(90deg, color-mix(in srgb, red, blue) 0%, blue 100%)',
    'linear-gradient(90deg, red 20% 40%, blue 100%)',
  ])('rejects unsupported gradient syntax: %s', (css) => {
    expect(parseGradientCss(css)).toBeNull();
  });
});
