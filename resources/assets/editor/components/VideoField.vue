<script setup lang="ts">
import type { PropertyField } from '@craftile/types';
import { Tabs } from '@ark-ui/vue/tabs';
import { Field } from '@ark-ui/vue/field';
import type { VideoExternalSource, VideoSettingValue } from '../types';
import useI18n from '../composables/i18n';

const props = defineProps<{ field: PropertyField }>();

const { t } = useI18n();

type EditableVideo = VideoSettingValue & {
  name?: string;
  previewUrl?: string | null;
  externalId?: string | null;
};

type UploadSource = { path: string };
type ExternalSource = { url: string; host?: string | null };

const model = defineModel<VideoSettingValue | string | null>();
const tab = ref<'upload' | 'external'>('upload');
const draftUrl = ref('');
const draftError = ref('');

const externalSources = computed<VideoExternalSource[]>(() => props.field.externalSources ?? []);
const acceptsExternal = computed(() => !!props.field.acceptsExternal && externalSources.value.length > 0);

function isUrl(value: string) {
  return value.startsWith('http://') || value.startsWith('https://');
}

function videoUrl(path: string) {
  return isUrl(path) ? path : window.editorConfig.videosBaseUrl + '/' + path;
}

function youtubeId(url: string): string | null {
  try {
    const parsed = new URL(url);
    const host = parsed.hostname.toLowerCase();

    if (host === 'youtu.be') {
      return parsed.pathname.split('/').filter(Boolean)[0] ?? null;
    }

    if (host !== 'youtube.com' && !host.endsWith('.youtube.com')) {
      return null;
    }

    if (parsed.pathname === '/watch') {
      return parsed.searchParams.get('v');
    }

    const match = parsed.pathname.match(/^\/(embed|shorts)\/([^/?#]+)/);

    return match?.[2] ?? null;
  } catch {
    return null;
  }
}

function vimeoId(url: string): string | null {
  try {
    const parsed = new URL(url);

    const host = parsed.hostname.toLowerCase();

    if (host !== 'vimeo.com' && !host.endsWith('.vimeo.com')) {
      return null;
    }

    return parsed.pathname.split('/').filter(Boolean).reverse().find(segment => /^\d+$/.test(segment)) ?? null;
  } catch {
    return null;
  }
}

function directVideo(url: string) {
  try {
    const parsed = new URL(url);
    return /\.(mp4|webm|ogg|ogv)$/i.test(parsed.pathname);
  } catch {
    return false;
  }
}

function detectExternal(url: string, requireAcceptedSource = false): EditableVideo | null {
  const youtube = youtubeId(url);
  const vimeo = vimeoId(url);

  if (youtube && sourceAllowed('youtube', requireAcceptedSource)) {
    return {
      mode: 'external',
      url,
      host: 'youtube',
      previewUrl: `https://img.youtube.com/vi/${youtube}/hqdefault.jpg`,
      externalId: youtube,
      name: 'YouTube video',
    };
  }

  if (vimeo && sourceAllowed('vimeo', requireAcceptedSource)) {
    return {
      mode: 'external',
      url,
      host: 'vimeo',
      externalId: vimeo,
      name: 'Vimeo video',
    };
  }

  for (const source of externalSources.value) {
    if (source.kind !== 'video' || !source.jsPattern) {
      continue;
    }

    const regex = new RegExp(source.jsPattern, source.jsFlags || '');

    if (regex.test(url)) {
      return {
        mode: 'external',
        url,
        host: source.host,
        name: source.label,
      };
    }
  }

  if (!requireAcceptedSource && directVideo(url)) {
    return {
      mode: 'external',
      url,
      host: null,
      name: url,
    };
  }

  return null;
}

function sourceAllowed(host: string, requireAcceptedSource: boolean) {
  return !requireAcceptedSource || externalSources.value.some(source => source.host === host);
}

function getUploadSource(value: VideoSettingValue | string | null | undefined): UploadSource | null {
  if (!value) {
    return null;
  }

  if (typeof value === 'string') {
    return isUrl(value) ? null : { path: value };
  }

  if (value.upload?.path) {
    return { path: value.upload.path };
  }

  if (value.mode === 'upload' && value.path) {
    return { path: value.path };
  }

  return null;
}

function getExternalSource(value: VideoSettingValue | string | null | undefined): ExternalSource | null {
  if (!value) {
    return null;
  }

  if (typeof value === 'string') {
    return isUrl(value) ? { url: value } : null;
  }

  if (value.external?.url) {
    return {
      url: value.external.url,
      host: value.external.host,
    };
  }

  if (value.mode === 'external' && value.url) {
    return {
      url: value.url,
      host: value.host,
    };
  }

  return null;
}

function normalizeUploadSource(source: UploadSource | null): EditableVideo | null {
  if (!source) {
    return null;
  }

  return {
    mode: 'upload',
    path: source.path,
    url: videoUrl(source.path),
    name: source.path,
  };
}

function normalizeExternalSource(source: ExternalSource | null): EditableVideo | null {
  if (!source) {
    return null;
  }

  return detectExternal(source.url, false) ?? {
    mode: 'external',
    url: source.url,
    host: source.host,
    name: source.url,
  };
}

function getActiveSource(value: VideoSettingValue | string | null | undefined): EditableVideo | null {
  if (!value) {
    return null;
  }

  if (typeof value === 'string') {
    return isUrl(value)
      ? normalizeExternalSource({ url: value })
      : normalizeUploadSource({ path: value });
  }

  return value.mode === 'external'
    ? normalizeExternalSource(getExternalSource(value))
    : normalizeUploadSource(getUploadSource(value));
}

function retainedValue(
  mode: 'upload' | 'external',
  upload: UploadSource | null,
  external: ExternalSource | null
): VideoSettingValue | null {
  if (!upload && !external) {
    return null;
  }

  return {
    mode,
    ...(upload ? { upload } : {}),
    ...(external ? { external } : {}),
  };
}

function withUploadSource(value: VideoSettingValue | string | null | undefined, upload: UploadSource): VideoSettingValue {
  return retainedValue('upload', upload, getExternalSource(value))!;
}

function withExternalSource(value: VideoSettingValue | string | null | undefined, external: ExternalSource): VideoSettingValue {
  return retainedValue('external', getUploadSource(value), external)!;
}

function withMode(value: VideoSettingValue | string | null | undefined, mode: 'upload' | 'external'): VideoSettingValue | null {
  return retainedValue(mode, getUploadSource(value), getExternalSource(value));
}

const currentVideo = computed(() => getActiveSource(model.value));
const savedExternalPreview = computed(() => normalizeExternalSource(getExternalSource(model.value)));
const externalPreview = computed(() => {
  const value = draftUrl.value.trim();

  if (!value) {
    return savedExternalPreview.value;
  }

  return detectExternal(value, true);
});
const externalInput = ref<{ $el?: HTMLInputElement } | null>(null);

function focusExternalInput() {
  externalInput.value?.$el?.focus();
}

watch(currentVideo, (video) => {
  if (video?.mode === 'external') {
    tab.value = 'external';
  } else if (video?.mode === 'upload') {
    tab.value = 'upload';
  }
}, { immediate: true });

watch(savedExternalPreview, (video) => {
  draftUrl.value = video?.url ?? '';
  draftError.value = '';
}, { immediate: true });

watch(tab, (value) => {
  if (value === 'upload' && getUploadSource(model.value)) {
    model.value = withMode(model.value, 'upload');
  }

  if (value === 'external' && getExternalSource(model.value)) {
    model.value = withMode(model.value, 'external');
  }
});

function clearActiveSource() {
  const upload = tab.value === 'upload' ? null : getUploadSource(model.value);
  const external = tab.value === 'external' ? null : getExternalSource(model.value);
  const nextMode = upload ? 'upload' : 'external';

  model.value = retainedValue(nextMode, upload, external);

  if (tab.value === 'external') {
    draftUrl.value = '';
  }

  draftError.value = '';
}

function onUploadChange(value: VideoSettingValue | string | null) {
  if (!value) {
    const upload = getUploadSource(model.value);

    if (!upload) {
      return;
    }

    clearActiveSource();
    return;
  }

  const upload = getUploadSource(value);

  if (!upload) {
    return;
  }

  model.value = withUploadSource(model.value, upload);
}

function commitExternal() {
  const value = draftUrl.value.trim();

  if (!value) {
    if (getExternalSource(model.value)) {
      clearActiveSource();
    }

    draftError.value = '';
    return;
  }

  const detected = detectExternal(value, true);

  if (!detected) {
    draftError.value = t('Enter a valid video URL');
    return;
  }

  model.value = withExternalSource(model.value, {
    url: value,
    host: detected.host,
  });
  draftError.value = '';
}

const placeholder = computed(() => {
  const labels = externalSources.value.map(source => source.label);

  return labels.length
    ? t('Paste a {sources} URL').replace('{sources}', labels.join(', '))
    : t('Paste a video URL');
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

    <Tabs.Root
      v-if="acceptsExternal"
      v-model="tab"
      class="flex flex-col gap-3"
    >
      <Tabs.List class="grid grid-cols-2 rounded border border-zinc-200 bg-white p-0.5 text-sm">
        <Tabs.Trigger
          value="upload"
          class="rounded px-2 py-1 data-[selected]:bg-zinc-200"
        >
          {{ t('Upload') }}
        </Tabs.Trigger>
        <Tabs.Trigger
          value="external"
          class="rounded px-2 py-1 data-[selected]:bg-zinc-200"
        >
          {{ t('External URL') }}
        </Tabs.Trigger>
      </Tabs.List>

      <Tabs.Content value="upload">
        <VideoPicker
          :field="field"
          :model-value="model ?? null"
          @update:model-value="onUploadChange"
        />
      </Tabs.Content>

      <Tabs.Content
        value="external"
        class="flex flex-col gap-3"
      >
        <Field.Root
          :invalid="!!draftError"
          class="flex flex-col gap-1"
          @keydown.capture.stop
        >
          <Field.Input
            ref="externalInput"
            v-model="draftUrl"
            type="url"
            class="w-full rounded border border-zinc-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none data-[invalid]:border-red-500 data-[invalid]:focus:border-red-500"
            :placeholder="placeholder"
            @blur="commitExternal"
            @keydown.enter.prevent.stop="commitExternal"
            @input="draftError = ''"
          />
          <Field.ErrorText class="text-xs text-red-600">
            {{ draftError }}
          </Field.ErrorText>
        </Field.Root>

        <button
          v-if="externalPreview"
          type="button"
          class="group relative block w-full rounded overflow-hidden text-left"
          @mousedown.prevent
          @click="focusExternalInput"
        >
          <VideoPreview
            :video="externalPreview"
            compact
          />
          <div class="absolute inset-0 hidden group-hover:flex items-center justify-center bg-black/50 text-white text-sm font-medium pointer-events-none">
            {{ t('Change') }}
          </div>
          <span
            class="absolute top-2 right-2 bg-zinc-700/60 text-zinc-100 p-1 rounded cursor-pointer"
            role="button"
            :title="t('Remove video')"
            @click.stop="clearActiveSource"
          >
            <i-heroicons-x-mark class="w-3.5 h-3.5" />
          </span>
        </button>
      </Tabs.Content>
    </Tabs.Root>

    <VideoPicker
      v-else
      :field="field"
      :model-value="model ?? null"
      @update:model-value="onUploadChange"
    />
  </div>
</template>
