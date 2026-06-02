import {
  parse,
  type ColorStop,
  type GradientNode,
  type LengthNode,
  type LinearGradientNode,
  type RadialGradientNode,
} from 'gradient-parser';
import { parseColor } from '@ark-ui/vue/color-picker';

export interface GradientStop {
  color: string;
  position: number;
}

export interface GradientValue {
  type: 'linear' | 'radial';
  angle?: number;
  stops: GradientStop[];
}

export function gradientToCss(gradient: GradientValue): string {
  const stopsStr = gradient.stops.map((stop) => `${stop.color} ${stop.position}%`).join(', ');

  if (gradient.type === 'linear') {
    return `linear-gradient(${gradient.angle ?? 90}deg, ${stopsStr})`;
  }

  return `radial-gradient(circle, ${stopsStr})`;
}

export function parseGradientCss(value: string): GradientValue | null {
  let nodes: GradientNode[];

  try {
    nodes = parse(value.trim());
  } catch {
    return null;
  }

  if (nodes.length !== 1) {
    return null;
  }

  const node = nodes[0];

  if (node.type === 'linear-gradient') {
    const angle = parseLinearAngle(node.orientation);
    const stops = parseColorStops(node.colorStops);

    if (angle === null || stops === null) {
      return null;
    }

    return {
      type: 'linear',
      angle,
      stops,
    };
  }

  if (node.type === 'radial-gradient') {
    if (!isSupportedRadialOrientation(node.orientation)) {
      return null;
    }

    const stops = parseColorStops(node.colorStops);

    if (stops === null) {
      return null;
    }

    return {
      type: 'radial',
      stops,
    };
  }

  return null;
}

function parseLinearAngle(orientation: LinearGradientNode['orientation']): number | null {
  if (!orientation) {
    return 180;
  }

  if (orientation.type === 'angular') {
    if (orientation.unit !== 'deg') {
      return null;
    }

    return normalizeAngle(Number(orientation.value));
  }

  const directionToAngle: Record<string, number> = {
    top: 0,
    'top right': 45,
    'right top': 45,
    right: 90,
    'right bottom': 135,
    'bottom right': 135,
    bottom: 180,
    'bottom left': 225,
    'left bottom': 225,
    left: 270,
    'left top': 315,
    'top left': 315,
  };

  return directionToAngle[orientation.value] ?? null;
}

function isSupportedRadialOrientation(orientation: RadialGradientNode['orientation']): boolean {
  if (!orientation || orientation.length !== 1) {
    return false;
  }

  const radial = orientation[0];

  return radial.type === 'shape' && radial.value === 'circle' && radial.style === undefined && radial.at === undefined;
}

function parseColorStops(colorStops: ColorStop[]): GradientStop[] | null {
  if (colorStops.length < 2 || colorStops.some((stop) => stop.length2 !== undefined)) {
    return null;
  }

  const stops = colorStops.map((stop) => ({
    color: normalizeColorStop(stop),
    position: parseStopPosition(stop.length),
  }));

  if (stops.some((stop) => stop.color === null || (stop.position === null && stop.position !== undefined))) {
    return null;
  }

  const positions = normalizeStopPositions(stops.map((stop) => stop.position));

  if (!positions) {
    return null;
  }

  return stops.map((stop, index) => ({
    color: stop.color!,
    position: positions[index],
  }));
}

function parseStopPosition(length: LengthNode | undefined): number | undefined | null {
  if (!length) {
    return undefined;
  }

  if (length.type !== '%') {
    return null;
  }

  const position = Number(length.value);

  return Number.isFinite(position) && position >= 0 && position <= 100 ? position : null;
}

function normalizeStopPositions(positions: Array<number | undefined | null>): number[] | null {
  if (positions.some((position) => position === null)) {
    return null;
  }

  const normalized = positions.map((position) => position as number | undefined);

  if (normalized[0] === undefined) {
    normalized[0] = 0;
  }

  if (normalized[normalized.length - 1] === undefined) {
    normalized[normalized.length - 1] = 100;
  }

  let index = 0;

  while (index < normalized.length) {
    if (normalized[index] !== undefined) {
      index++;
      continue;
    }

    const startIndex = index - 1;
    let endIndex = index;

    while (endIndex < normalized.length && normalized[endIndex] === undefined) {
      endIndex++;
    }

    if (normalized[startIndex] === undefined || normalized[endIndex] === undefined) {
      return null;
    }

    const step = (normalized[endIndex]! - normalized[startIndex]!) / (endIndex - startIndex);

    for (let missingIndex = index; missingIndex < endIndex; missingIndex++) {
      normalized[missingIndex] = normalized[startIndex]! + step * (missingIndex - startIndex);
    }

    index = endIndex;
  }

  return normalized.every((position) => position !== undefined && position >= 0 && position <= 100)
    ? (normalized as number[])
    : null;
}

function normalizeColorStop(stop: ColorStop): string | null {
  const cssColor = colorStopToCss(stop);

  return cssColor ? normalizeCssColor(cssColor) : null;
}

function colorStopToCss(stop: ColorStop): string | null {
  switch (stop.type) {
    case 'hex':
      return `#${stop.value}`;
    case 'rgb':
      return `rgb(${stop.value.join(', ')})`;
    case 'rgba':
      return `rgba(${stop.value.join(', ')})`;
    case 'hsl':
      return `hsl(${stop.value[0]}, ${stop.value[1]}%, ${stop.value[2]}%)`;
    case 'hsla': {
      const alpha = normalizeCssAlpha(stop.value[3]);

      return alpha === null ? null : `hsla(${stop.value[0]}, ${stop.value[1]}%, ${stop.value[2]}%, ${alpha})`;
    }
    case 'literal':
      return stop.value;
    default:
      return null;
  }
}

function normalizeCssColor(value: string): string | null {
  try {
    return parseColor(value).toString('hexa').toLowerCase();
  } catch {
    return null;
  }
}

function normalizeCssAlpha(value: string | undefined): string | null {
  if (value === undefined) {
    return '1';
  }

  const alpha = Number(value);

  if (!Number.isFinite(alpha) || alpha < 0 || alpha > 1) {
    return null;
  }

  return value.trim().startsWith('.') ? `0${value.trim()}` : value.trim();
}

function normalizeAngle(angle: number): number | null {
  if (!Number.isFinite(angle)) {
    return null;
  }

  return ((angle % 360) + 360) % 360;
}
