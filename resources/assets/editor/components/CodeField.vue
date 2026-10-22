<script setup lang="ts">
import type { PropertyField } from '@craftile/types';
import Prism from 'prismjs/components/prism-core';
import 'prismjs/components/prism-markup';
import 'prismjs/components/prism-css';
import { uploadSectionMedia } from '../api';
import useI18n from '../composables/i18n';
import { useCraftileEditor } from '../composables/useCraftileEditor';
import { sectionRowId } from '../utils/sectionBlockIds';

interface Props {
  field: PropertyField & { language?: string };
}

const props = defineProps<Props>();

const model = defineModel<string | null>();

const { t } = useI18n();
const editor = useCraftileEditor();

const area = ref<HTMLTextAreaElement>();
const painted = ref<HTMLPreElement>();

const language = computed(() => props.field.language || 'html');

const uploading = ref(false);

/**
 * Bagisto files a section's uploads under the row it is stored as, so media can
 * only be added to a section that is stored, and only to a field with somewhere
 * to put a tag.
 */
const section = computed(() => sectionRowId(editor?.ui.state.selectedBlockId));
const allowsMedia = computed(() => language.value !== 'css' && section.value !== null);

/**
 * What is being typed, which is not always what is stored.
 *
 * A section is saved as it is edited, and Bagisto rewrites the markup and styles
 * it stores: it closes tags, drops what it will not serve, and hands back
 * something other than what was typed. Writing that into the box under the
 * merchant would move their cursor and rewrite half finished markup, so the box
 * holds its own text for as long as they are in it, and takes what is stored
 * once they leave.
 */
const buffer = ref(model.value ?? '');
const typing = ref(false);

watch(
  () => model.value,
  (stored) => {
    if (!typing.value) {
      buffer.value = stored ?? '';
    }
  }
);

/**
 * The same text, coloured, drawn behind a box whose own text is transparent.
 * The two carry identical type and wrapping, so the colours sit under the
 * characters they belong to and the cursor stays the browser's own.
 */
const highlighted = computed(() => {
  const grammar = Prism.languages[language.value] ?? Prism.languages.markup;

  // A trailing newline collapses in a `pre`, which would shift the last line.
  const code = buffer.value.endsWith('\n') ? `${buffer.value} ` : buffer.value;

  return Prism.highlight(code, grammar, language.value);
});

function input(event: Event) {
  buffer.value = (event.target as HTMLTextAreaElement).value;
  model.value = buffer.value;
}

function leave() {
  typing.value = false;
  buffer.value = model.value ?? '';
}

/**
 * The tag that draws an uploaded file on the storefront, written where the
 * cursor is so it lands in what is being typed.
 */
function markupFor(media: { url: string; type: string }): string {
  return media.type === 'video'
    ? `<video src="${media.url}" controls playsinline class="w-full"></video>`
    : `<img src="${media.url}" alt="" class="w-full" />`;
}

function write(markup: string) {
  const box = area.value;
  const at = box ? box.selectionStart : buffer.value.length;
  const to = box ? box.selectionEnd : buffer.value.length;

  buffer.value = `${buffer.value.slice(0, at)}${markup}${buffer.value.slice(to)}`;
  model.value = buffer.value;

  nextTick(() => {
    box?.focus();
    box?.setSelectionRange(at + markup.length, at + markup.length);
  });
}

async function addMedia(event: Event) {
  const input = event.target as HTMLInputElement;
  const file = input.files?.[0];

  if (!file || section.value === null) {
    return;
  }

  uploading.value = true;

  const request = uploadSectionMedia(section.value, file);

  request.onSuccess((media) => write(markupFor(media)));

  request.onError(() =>
    editor?.ui.toast({
      type: 'error',
      title: t('Failed to add media'),
    })
  );

  await request.execute();

  uploading.value = false;
  input.value = '';
}

function follow() {
  if (painted.value && area.value) {
    painted.value.scrollTop = area.value.scrollTop;
    painted.value.scrollLeft = area.value.scrollLeft;
  }
}

// A tab belongs in the markup being written, not in the walk to the next field.
function indent(event: KeyboardEvent) {
  const box = event.target as HTMLTextAreaElement;
  const { selectionStart, selectionEnd } = box;

  event.preventDefault();

  buffer.value = `${box.value.slice(0, selectionStart)}  ${box.value.slice(selectionEnd)}`;
  model.value = buffer.value;

  nextTick(() => {
    box.selectionStart = box.selectionEnd = selectionStart + 2;
  });
}
</script>

<template>
  <div class="flex flex-col gap-1">
    <label
      v-if="field.label"
      class="text-sm font-medium text-gray-700"
    >
      {{ field.label }}
    </label>

    <div class="overflow-hidden rounded border focus-within:border-blue-500">
      <div class="flex items-center justify-between gap-2 border-b bg-gray-50 px-2 py-1">
        <span class="font-mono text-[11px] font-semibold uppercase tracking-wide text-gray-500">
          {{ language }}
        </span>

        <label
          v-if="allowsMedia"
          class="flex cursor-pointer items-center gap-1 rounded px-1 text-[11px] font-semibold text-blue-600 hover:bg-blue-50"
        >
          <i-lucide-image class="size-3.5" />

          {{ uploading ? t('Uploading...') : t('Add media') }}

          <input
            type="file"
            class="hidden"
            accept="image/*,video/*"
            :disabled="uploading"
            @change="addMedia"
          />
        </label>
      </div>

      <div class="relative">
        <pre
          ref="painted"
          aria-hidden="true"
          class="code-layer pointer-events-none absolute inset-0 overflow-auto"
        ><code v-html="highlighted"></code></pre>

        <textarea
          ref="area"
          :value="buffer"
          rows="12"
          spellcheck="false"
          autocapitalize="off"
          autocomplete="off"
          autocorrect="off"
          class="code-layer relative block w-full resize-y border-0 bg-transparent text-transparent caret-gray-900 outline-none"
          @input="input"
          @focus="typing = true"
          @blur="leave"
          @scroll="follow"
          @keydown.tab="indent"
        ></textarea>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Both layers must lay every character out the same way, or the colours drift. */
.code-layer {
  margin: 0;
  padding: 0.5rem;
  border: 0;
  font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
  font-size: 0.75rem;
  line-height: 1.625;
  tab-size: 2;
  white-space: pre-wrap;
  overflow-wrap: break-word;
  word-break: break-word;
}

.code-layer :deep(.token.comment) {
  color: #6b7280;
}

.code-layer :deep(.token.tag),
.code-layer :deep(.token.selector),
.code-layer :deep(.token.keyword) {
  color: #be123c;
}

.code-layer :deep(.token.attr-name),
.code-layer :deep(.token.property) {
  color: #1d4ed8;
}

.code-layer :deep(.token.attr-value),
.code-layer :deep(.token.string) {
  color: #047857;
}

.code-layer :deep(.token.punctuation) {
  color: #6b7280;
}

.code-layer :deep(.token.number),
.code-layer :deep(.token.unit),
.code-layer :deep(.token.function) {
  color: #b45309;
}

.code-layer :deep(.token.doctype),
.code-layer :deep(.token.cdata) {
  color: #9ca3af;
}

textarea.code-layer::selection {
  background: rgb(191 219 254);
}
</style>
