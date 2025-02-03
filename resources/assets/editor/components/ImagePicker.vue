<script setup lang="ts">
  import { Dialog } from '@ark-ui/vue/dialog';
  import { machine, connect } from "@zag-js/file-upload";
  import { normalizeProps, useMachine } from "@zag-js/vue";
  import { useUploadImage } from '../api';
  import { useStore } from '../store';
  import { Image } from '../types';

  interface Props {
    label: string;
  }

  // @see https://stackoverflow.com/a/5717133
  const isValidUrl = (str: string) => {
    var pattern = new RegExp('^(https?:\\/\\/)?' + // protocol
      '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|' + // domain name
      '((\\d{1,3}\\.){3}\\d{1,3}))' + // OR ip (v4) address
      '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*' + // port and path
      '(\\?[;&a-z\\d%_.~+=-]*)?' + // query string
      '(\\#[-a-z\\d_]*)?$', 'i'); // fragment locator
    return !!pattern.test(str);
  }

  const store = useStore();
  const props = defineProps<Props>();
  const model = defineModel({
    set(value: Image | null) {
      return value ? value.path : null;
    },

    get(v: string | null): Image | null {
      if (!v) {
        return null;
      }

      return { path: v, url: isValidUrl(v) ? v : window.ThemeEditor.imagesBaseUrl + v, name: v };
    }
  });

  const opened = ref(false);
  const uploadingImages = ref<Image[]>([]);
  const currentValue = ref<Image>();

  const [state, send] = useMachine(machine({
    id: "imagepicker",
    accept: "image/*",
    maxFiles: 10,
    onFileAccept(details) {
      fileUpload.value.clearFiles();

      uploadingImages.value = details.files.map(file => ({
        url: URL.createObjectURL(file),
        path: file.name,
        name: file.name,
        uploading: true
      }));

      const formData = new FormData;

      details.files.forEach(file => {
        formData.append('image[]', file);
      });

      const { data, onFetchResponse } = useUploadImage(formData);

      onFetchResponse(() => {
        store.images = [...data.value, ...store.images];
        uploadingImages.value = [];

        onImageSelect(store.images[0]);
      })
    },
  }));

  const fileUpload = computed(() => connect(state.value, send, normalizeProps));

  function onImageSelect(image: Image) {
    model.value = image;
  }

  function openDialog() {
    opened.value = true;
    currentValue.value = model.value as Image;
  }

  function onCancel() {
    model.value = currentValue.value;
    opened.value = false;
  }

  function removeImage() {
    model.value = null;
  }

  onMounted(() => store.fetchImages());
</script>

<template>
  <div>
    <div class="flex flex-col gap-2">
      <label
        v-if="label"
        class="text-sm"
      >{{ label }}</label>
      <div
        v-if="!model"
        class="min-h-24 border border-dashed rounded flex flex-col items-center justify-center"
      >
        <button
          @click="openDialog"
          class="text-blue-600 text-sm bg-zinc-200 rounded px-2.5 py-1.5 hover:bg-zinc-100 hover:text-blue-800"
        >{{ $t('Select image') }}</button>
      </div>
      <div v-else>
        <div class="rounded-t p-3 bg-neutral-100 relative">
          <img
            :src="model.url"
            alt=""
            class="object-cover"
          >
          <button
            class="absolute top-4 right-4 bg-gray-700/30 text-gray-200 p-px rounded"
            @click="removeImage"
          >
            <i-heroicons-x-mark class="w-4 h-4" />
          </button>
        </div>
        <div class="rounded-b p-3 bg-neutral-100 border-t border-white">
          <button
            @click="openDialog"
            class="text-sm w-full text-center bg-white border rounded py-1"
          >{{ $t('Change image') }}</button>
        </div>
      </div>
    </div>

    <Dialog.Root v-model:open="opened">
      <Teleport to="body">
        <Dialog.Backdrop class="fixed h-screen w-screen inset-0" />
        <Dialog.Positioner class="flex fixed top-14 left-14 bottom-0 w-[17.85rem] items-center justify-center">
          <Dialog.Content class="bg-white shadow flex flex-col w-full h-full overflow-hidden">
            <header class="flex-none h-12 border-b border-neutral-200 flex gap-3 px-4 items-center justify-between">
              <Dialog.Title>{{ label || $t('Image Picker') }}</Dialog.Title>
              <Dialog.CloseTrigger class="cursor-pointer rounded-lg p-0.5 text-neutral-700 hover:bg-neutral-300">
                <i-heroicons-x-mark class="w-5 h-5" />
              </Dialog.CloseTrigger>
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
                  <p>{{ $t('Drop your images here') }}</p>
                  <button
                    v-bind="fileUpload.getTriggerProps()"
                    class="cursor-pointer bg-blue-500 text-white shadow-lg rounded border px-2.5 py-1.5 text-sm"
                  >
                    {{ $t('Add images') }}
                  </button>
                </div>
                <input v-bind="fileUpload.getHiddenInputProps()" />
              </div>

              <div class="grid grid-cols-2 gap-3">
                <ImagePreview
                  v-for="image in uploadingImages"
                  :key="image.path"
                  :image="image"
                />
                <ImagePreview
                  v-for="image in store.images"
                  :key="image.path"
                  :image="image"
                  :selected="!!model && model.path === image.path"
                  @click="onImageSelect(image)"
                />
              </div>
            </section>
            <footer class="flex-none flex items-center gap-3 p-3 justify-end h-12 border-t border-neutral-200">
              <button
                @click="onCancel"
                class="text-sm shadow px-3 py-1 rounded bg-neutral-100 border"
              >{{ $t('Cancel') }}</button>
              <Dialog.CloseTrigger class="text-sm shadow px-3 py-1 rounded bg-gray-800 text-white border hover:bg-gray-700">Select</Dialog.CloseTrigger>
            </footer>
          </Dialog.Content>
        </Dialog.Positioner>
      </Teleport>
    </Dialog.Root>
  </div>
</template>
