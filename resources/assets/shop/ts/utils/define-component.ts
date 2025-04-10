import type { AlpineComponent, Magics as AlpineMagics, Alpine as AlpineType } from 'alpinejs';
import { useId } from './use-id';
export interface PartContext {
  value: any;
  modifiers: string[];
  Alpine: AlpineType;
  cleanup: (callback: () => void) => void;
  generateId: (prefix: string) => string;
}

type WithAlpineMagics<T> = AlpineMagics<T> & T;

export type PartHandler<Api = unknown> = (
  this: WithAlpineMagics<Api>,
  api: WithAlpineMagics<Api>,
  el: HTMLElement,
  context: PartContext
) => Record<string, any> | void;

type ExcludeScopes<T> = {
  [K in keyof T as K extends `$${string}` ? never : K]: T[K];
};

type NonFunctionKeys<T> = {
  [K in keyof T]: T[K] extends Function ? never : K;
}[keyof T];

type State<T> = ExcludeScopes<Partial<Pick<T, NonFunctionKeys<T>>>>;

type SetupWithAlpine<T> = (props: State<T>, ctx: { generateId: (s: string) => string }) => AlpineComponent<T> & T;

export interface ComponentConfig<RootApi> {
  name: string;
  setup: SetupWithAlpine<ExcludeScopes<RootApi>>;
  parts?: Record<string, PartHandler<RootApi>>;
}

function toCamelCase(input: string): string {
  return input
    .trim()
    .replace(/[-_\s]+(.)?/g, (_, char) => (char ? char.toUpperCase() : ''))
    .replace(/^[A-Z]/, (char) => char.toLowerCase());
}

export function defineComponent<RootApi>({ name, setup, parts }: ComponentConfig<RootApi>) {
  return (Alpine: AlpineType) => {
    Alpine.magic(toCamelCase(name), (el) => Alpine.$data(el));

    Alpine.directive(name, (el, { value: partName, expression, modifiers }, { evaluate, cleanup }) => {
      if (!partName) {
        const instanceId = useId(name);
        const userConfig = expression ? evaluate(expression) : ({} as any);
        const generateId = (part: string) => `${instanceId}:${part}`;

        const api = Alpine.reactive(setup(userConfig, { generateId }));
        (api as any)._generateId = generateId;

        Alpine.bind(el, {
          'x-id': () => [name],
          'x-data': () => api,
        });

        if (typeof parts?.root === 'function') {
          const bindings = parts.root.call(api as any, api as any, el, {
            value: undefined,
            modifiers,
            Alpine,
            cleanup,
            generateId,
          });

          if (bindings) {
            Alpine.bind(el, bindings);
          }
        }

        return;
      }

      const camelPartName = toCamelCase(partName);
      const handler = parts ? parts[camelPartName] : null;

      if (!handler) return;

      const api = Alpine.$data(el);
      const value = expression ? evaluate(expression) : undefined;

      const generateId = (api as any)._generateId;

      const context: PartContext = {
        value,
        modifiers,
        Alpine,
        cleanup,
        generateId,
      };

      const bindings = handler.call(api as any, api as any, el, context) ?? {};

      Alpine.bind(el, {
        'data-part': partName,
        ...bindings,
      });
    }).before('bind');
  };
}

type WithScope<Api, ScopeName extends string, Scope> = Api & {
  [K in `$${ScopeName}`]: Scope;
};

export function defineScope<Api, ScopeName extends string, Scope>(options: {
  name: ScopeName;
  setup: (api: Api, el: HTMLElement, ctx: PartContext) => Scope;
  bindings?: (api: WithAlpineMagics<Api>, scope: Scope) => Record<string, any>;
}): (api: WithScope<Api, ScopeName, Scope>, el: HTMLElement, ctx: PartContext) => Record<string, any> {
  return (api, el, ctx) => {
    const prefix = useId(options.name);
    const generateId = (part: string) => {
      return ctx.generateId(`${prefix}:${part}`);
    };

    const scope = ctx.Alpine.reactive(options.setup(api, el, { ...ctx, generateId }));

    const key = `$${options.name}` as keyof typeof api;

    return {
      'x-data': () => ({ [key]: scope }),
      ...(options.bindings?.(api as WithAlpineMagics<Api>, scope) ?? {}),
    };
  };
}
