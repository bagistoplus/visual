import { PreviewClient } from '@craftile/preview-client';
import RawHtmlRenderer from '@craftile/preview-client-html';
import { Block } from '@craftile/types';

const previewClient = new PreviewClient();
RawHtmlRenderer.init(previewClient, {
  morphdom: {
    onBeforeElUpdated(fromEl, toEl) {
      console.log(fromEl, toEl);
      // @ts-ignore
      if (fromEl['_x_dataStack'] && typeof window.Alpine?.morph === 'function') {
        window.Alpine.morph(fromEl, toEl, {
          updating(oldEl: Element, newEl: Element, childrenOnly: () => void) {
            if (oldEl instanceof HTMLElement && newEl instanceof HTMLElement) {
              if (oldEl.hasAttribute('wire:id')) {
                return childrenOnly();
              }
            }
          },
        });

        return false;
      }

      return true;
    },
  },
});

function handlePropertyUpdate(data: { block: Block; key: string; value: any; oldValue: any }) {
  const { block, key, value } = data;
  const likeUpdateKey = [block.id, key].filter(Boolean).join('.');
  const attrName = `data-live-update-${likeUpdateKey}`;
  const selector = `[${CSS.escape(attrName)}]`;

  const elements = document.querySelectorAll(selector);

  if (!elements.length) {
    return;
  }

  for (const el of elements) {
    const type = el.getAttribute(attrName);
    const [updateType, updateKey] = type?.split(/:(.+)/) ?? ['text', undefined];

    switch (updateType) {
      case 'text':
        el.textContent = value;
        break;
      case 'html':
        el.innerHTML = value;
        break;
      case 'outerHTML':
        el.outerHTML = value;
        break;
      case 'attr':
        if (!value) {
          if (el.tagName.toLowerCase() === 'img' && updateKey === 'src') {
            return false;
          }

          el.removeAttribute(updateKey as string);
        } else {
          el.setAttribute(updateKey as string, value);
        }
        break;
      case 'style':
        if (!value) {
          (el as HTMLElement).style.removeProperty(updateKey as string);
        } else {
          (el as HTMLElement).style.setProperty(updateKey as string, value);
        }
        break;
      case 'toggleClass':
        el.classList.toggle(updateKey as string);
        break;
      default:
        console.warn(`Unknown live update type: ${updateType}`);
    }
  }
}

previewClient.on('block.property.updated', handlePropertyUpdate);
previewClient.on('block.insert.after', (event) => {
  const el = document.querySelector(`[data-block="${event.blockId}"]`);
  if (el) {
    el.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'nearest' });
  }
});
