declare module '*.vue' {
  import type { DefineComponent } from 'vue';

  const component: DefineComponent<object, object, any>;
  export default component;
}

declare module 'idiomorph/dist/idiomorph.esm.js';
declare module 'debounce-async';
