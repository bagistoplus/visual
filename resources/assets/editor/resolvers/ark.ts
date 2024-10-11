const matchComponents = [
  {
    pattern: /^Field/,
    module: 'field'
  },
  {
    pattern: /^NumberInput/,
    module: 'number-input'
  },
  {
    pattern: /^Checkbox/,
    module: 'checkbox'
  },
  {
    pattern: /^RadioGroup/,
    module: 'radio-group'
  },
  {
    pattern: /^Select/,
    module: 'select'
  },
  {
    pattern: /^Slider/,
    module: 'slider'
  },
  {
    pattern: /^Accordion/,
    module: 'accordion'
  }
];

export function ArkUIVueResolver() {
  return {
    type: 'component' as const,
    resolve(name: string) {
      if (name.startsWith('Ark')) {
        const componentName = name.slice(3);
        for (const pattern of matchComponents) {
          if (pattern.pattern.test(componentName)) {
            return {name: componentName, from: '@ark-ui/vue/' + pattern.module }
          }
        }
      }
    }
  }
}
