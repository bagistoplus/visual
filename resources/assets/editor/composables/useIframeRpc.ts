import { v4 as uuidv4 } from 'uuid';

type IframeMessage =
  | { type: 'refresh'; data: string }
  | { type: 'setting:update'; data: any }
  | { type: 'section:updating'; data: any }
  | { type: 'section:updated'; data: any }
  | { type: 'section:highlight'; data: string }
  | { type: 'clearActiveSection'; data: string }
  | { type: 'section:select'; data: string }
  | { type: 'sectionsOrder'; data: string[] }
  | { type: 'section:removed'; data: any }
  | { type: 'section:added'; data: any };

export function useIframeRpc() {
  let queue: IframeMessage[] = [];
  const resolvers = new Map<string, { resolve: (value: any) => void; timer: number }>();
  const ready = ref(false);
  const iframeRef = ref<HTMLIFrameElement | null>(null);

  function onMessage(event: MessageEvent) {
    const { messageId, ...data } = event.data;

    if (messageId && resolvers.has(messageId)) {
      const { resolve, timer } = resolvers.get(messageId)!;

      clearTimeout(timer);
      resolvers.delete(messageId);
      resolve(data);
    }
  }

  window.addEventListener('message', onMessage);

  function setIframe(el: HTMLIFrameElement) {
    iframeRef.value = el;
  }

  function markReady() {
    ready.value = true;
    queue.forEach(post);
    queue = [];
  }

  function post(msg: any) {
    console.log(msg);
    iframeRef.value?.contentWindow?.postMessage(msg, window.origin);
  }

  function call<T extends IframeMessage['type']>(
    type: T,
    data?: Extract<IframeMessage, { type: T }>['data'],
    timeout = 3000
  ): Promise<any> {
    const msg = { type, data } as IframeMessage;

    if (!ready.value) {
      return Promise.resolve(null);
    }

    return new Promise((resolve) => {
      const messageId = uuidv4();
      const timer = window.setTimeout(() => {
        if (resolvers.has(messageId)) {
          resolvers.delete(messageId);
        }

        resolve(null);
      }, timeout);

      resolvers.set(messageId, { resolve, timer });

      post({ ...msg, messageId });
    });
  }

  return {
    setIframe,
    markReady,
    call,
  };
}
