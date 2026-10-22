<script setup lang="ts">
import type { PropertyField } from '@craftile/types';
import { Button } from '@craftile/editor/ui';
import { Dialog } from '@ark-ui/vue/dialog';
import { machine, connect } from '@zag-js/file-upload';
import { normalizeProps, useMachine } from '@zag-js/vue';
import { copySectionMedia, uploadSectionMedia, type SectionMedia } from '../api';
import useI18n from '../composables/i18n';
import { useCraftileEditor } from '../composables/useCraftileEditor';
import { useSectionMedia } from '../composables/useSectionMedia';
import { sectionRowId } from '../utils/sectionBlockIds';
import type { Image } from '../types';

interface Props {
  field: PropertyField;
}

defineProps<Props>();

/**
 * The image of a Bagisto section, which is a path and nothing else.
 *
 * Bagisto files a section's uploads under the row it is stored as and deletes
 * every file that section's options stop naming, so an image is uploaded into
 * the section being edited and a file another section owns is copied before it
 * is pointed at. Neither alt text nor a focal point is offered: a section type
 * declares a path, and the storefront reads a path.
 */
const model = defineModel<string | null>();

const { t } = useI18n();
const editor = useCraftileEditor()!;
const { files, loading, load, remember, find } = useSectionMedia();

const opened = ref(false);
const picked = ref<string | null>(null);
const uploading = ref<Image[]>([]);
const copying = ref(false);

const section = computed(() => sectionRowId(editor.ui.state.selectedBlockId));

const current = computed(() => find(model.value));

const library = computed<Image[]>(() =>
  files.value.map((file) => ({
    path: file.path,
    url: file.url,
    name: file.path.split('/').pop() ?? file.path,
  }))
);

const service = useMachine(machine, {
  id: 'section-image-picker',
  accept: 'image/*',
  maxFiles: 10,
  onFileAccept: (details) => upload(details.files),
});

const fileUpload = computed(() => connect(service, normalizeProps));

async function upload(accepted: File[]) {
  if (accepted.length === 0 || section.value === null) {
    return;
  }

  uploading.value = accepted.map((file) => ({
    path: file.name,
    name: file.name,
    url: URL.createObjectURL(file),
    uploading: true,
  }));

  for (const file of accepted) {
    const request = uploadSectionMedia(section.value, file);

    request.onSuccess((media) => {
      remember({ ...media, section: section.value! });
      picked.value = media.path;
    });

    request.onError((error) =>
      editor.ui.toast({
        type: 'error',
        title: t('Failed to upload image'),
        description: error.message || error.toString(),
      })
    );

    await request.execute();
  }

  uploading.value = [];
  fileUpload.value.clearFiles();
}

/**
 * A file this section already owns is pointed at as it is. One another section
 * owns is copied first, so that the section owning it cannot take it away on
 * its next save.
 */
async function confirm() {
  const chosen = files.value.find((file: SectionMedia) => file.path === picked.value);

  if (!chosen || section.value === null) {
    opened.value = false;

    return;
  }

  if (chosen.section === section.value) {
    model.value = chosen.path;
    opened.value = false;

    return;
  }

  copying.value = true;

  const request = copySectionMedia(section.value, chosen.path);

  request.onSuccess((copy) => {
    remember({ ...copy, section: section.value! });
    model.value = copy.path;
    opened.value = false;
  });

  request.onError((error) =>
    editor.ui.toast({
      type: 'error',
      title: t('Failed to add media'),
      description: error.message || error.toString(),
    })
  );

  await request.execute();

  copying.value = false;
}

function onOpen() {
  // Under the path the library knows it by, which is not always the one it was
  // stored under, so that what the section draws opens marked as chosen.
  picked.value = current.value?.path ?? null;
  load();
}

// The url a stored path is served from is only known to the server, so a
// picker already pointed at a file has to read the library to draw it.
onMounted(() => {
  if (model.value) {
    load();
  }
});
</script>

<template>
  <div>
    <label
      v-if="field.label"
      class="text-sm block mb-1 font-medium text-gray-700"
    >
      {{ field.label }}
    </label>

    <Dialog.Root
      v-model:open="opened"
      :modal="false"
      :close-on-interact-outside="false"
      @open-change="(details) => details.open && onOpen()"
    >
      <div
        v-if="!model"
        class="min-h-24 border border-dashed rounded flex flex-col items-center justify-center gap-1"
      >
        <Dialog.Trigger
          :disabled="section === null"
          class="text-blue-600 text-sm bg-zinc-200 rounded px-2.5 py-1.5 hover:bg-zinc-100 hover:text-blue-800 disabled:opacity-50"
        >
          {{ t('Select image') }}
        </Dialog.Trigger>

        <p
          v-if="section === null"
          class="text-xs text-zinc-500"
        >
          {{ t('Available once the section is saved') }}
        </p>
      </div>
      <div v-else>
        <div class="rounded-t p-3 bg-zinc-100 relative">
          <img
            v-if="current"
            :src="current.url"
            alt=""
            class="object-cover h-32 w-full object-center"
          >
          <div
            v-else
            class="flex h-32 w-full items-center justify-center text-xs text-zinc-500"
          >
            {{ t('Loading...') }}
          </div>
          <button
            class="absolute top-4 right-4 bg-zinc-700/30 text-zinc-200 p-px rounded"
            type="button"
            :aria-label="t('Remove')"
            @click="model = null"
          >
            <i-heroicons-x-mark class="w-4 h-4" />
          </button>
        </div>
        <div class="rounded-b p-3 bg-zinc-100 border-t border-white flex gap-2">
          <Dialog.Trigger class="text-sm flex-1 text-center bg-white border rounded py-1">
            {{ t('Change image') }}
          </Dialog.Trigger>
        </div>
      </div>

      <Dialog.Positioner class="flex absolute inset-0 z-50 h-full w-full items-center justify-center">
        <Dialog.Content class="bg-white shadow flex flex-col w-full h-full overflow-hidden">
          <header class="flex-none h-12 border-b border-zinc-200 flex gap-3 px-4 items-center justify-between">
            <Dialog.Title>{{ field.label || t('Image Picker') }}</Dialog.Title>
            <button
              class="cursor-pointer rounded-lg p-0.5 text-zinc-700 hover:bg-zinc-300"
              @click="opened = false"
            >
              <i-heroicons-x-mark class="w-5 h-5" />
            </button>
          </header>

          <section class="flex-1 flex flex-col gap-3 min-h-0 p-3 overflow-y-auto">
            <div
              v-bind="fileUpload.getRootProps()"
              accept="image/*"
            >
              <div
                v-bind="fileUpload.getDropzoneProps()"
                class="flex flex-col gap-3 items-center justify-center h-32 bg-zinc-50/50 border border-zinc-300 border-dashed rounded-lg"
              >
                <p>{{ t('Drop your images here') }}</p>
                <button
                  v-bind="fileUpload.getTriggerProps()"
                  class="cursor-pointer bg-blue-500 text-white shadow-lg rounded border px-2.5 py-1.5 text-sm"
                >
                  {{ t('Add images') }}
                </button>
              </div>
              <input v-bind="fileUpload.getHiddenInputProps()">
            </div>

            <p
              v-if="loading && library.length === 0"
              class="text-sm text-zinc-500"
            >
              {{ t('Loading...') }}
            </p>

            <div class="grid grid-cols-2 gap-3">
              <ImagePreview
                v-for="image in uploading"
                :key="image.path"
                :image="image"
              />
              <ImagePreview
                v-for="image in library"
                :key="image.path"
                :image="image"
                :selected="picked === image.path"
                @click="picked = image.path"
              />
            </div>
          </section>

          <footer class="flex-none flex items-center gap-3 p-3 justify-end h-12 border-t border-zinc-200">
            <Button @click="opened = false">
              {{ t('Cancel') }}
            </Button>
            <Button
              variant="primary"
              :disabled="copying"
              @click="confirm"
            >
              {{ t('Select') }}
            </Button>
          </footer>
        </Dialog.Content>
      </Dialog.Positioner>
    </Dialog.Root>
  </div>
</template>
