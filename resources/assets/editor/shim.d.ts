declare module '*.vue' {
  import type { DefineComponent } from 'vue';

  const component: DefineComponent<object, object, any>;
  export default component;
}

declare module 'prismjs/components/prism-core' {
  const Prism: {
    languages: Record<string, any>;
    highlight(code: string, grammar: any, language: string): string;
  };

  export default Prism;
}

declare module 'prismjs/components/prism-markup';
declare module 'prismjs/components/prism-css';
