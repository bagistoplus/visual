<script setup lang="ts">
import type { PropertyField } from '@craftile/types';
import { useEditor, EditorContent } from '@tiptap/vue-3';
import Document from '@tiptap/extension-document';
import Paragraph from '@tiptap/extension-paragraph';
import Text from '@tiptap/extension-text';
import Bold from '@tiptap/extension-bold'
import Italic from '@tiptap/extension-italic';
import Underline from '@tiptap/extension-underline';
import Heading from '@tiptap/extension-heading';
import BulletList from '@tiptap/extension-bullet-list';
import OrderedList from '@tiptap/extension-ordered-list';
import ListItem from '@tiptap/extension-list-item';

import { Menu } from '@ark-ui/vue/menu'
import useI18n from '../composables/i18n';

interface Props {
  field: PropertyField;
}

const { t } = useI18n();

const props = defineProps<Props>();
const model = defineModel<string>();

const editor = useEditor({
  content: model.value,
  extensions: [Document, Paragraph, Text, Bold, Italic, Underline, Heading, BulletList, OrderedList, ListItem],
  injectCSS: false,
  editorProps: {
    attributes: {
      class: 'p-3 prose prose-sm prose-gray focus:outline-none',
    }
  },
  onUpdate({ editor }) {
    model.value = editor.getHTML();
  }
});

watch(model, (newContent) => {
  if (editor.value && editor.value.getHTML() !== newContent) {
    editor.value.commands.setContent(newContent as string);
  }
})

onBeforeMount(() => {
  editor.value?.destroy();
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
    <section
      class="border rounded-lg"
      v-if="editor"
    >
      <header class="h-10 px-3 flex gap-2 items-center border-b bg-zinc-200 [&>button]:p-1 [&>button]:rounded-md [&>button:hover]:bg-zinc-300">
        <button
          title="Bold"
          @click="editor.chain().focus().toggleBold().run()"
          :class="{ 'bg-zinc-300 text-blue-500': editor!.isActive('bold') }"
        >
          <i-heroicons-bold class="w-4 h-4" />
        </button>
        <button
          title="Italic"
          @click="editor.chain().focus().toggleItalic().run()"
          :class="{ 'bg-zinc-300 text-blue-500': editor.isActive('italic') }"
        >
          <i-heroicons-italic class="w-4 h-4" />
        </button>
        <button
          title="Underline"
          @click="editor.chain().focus().toggleUnderline().run()"
          :class="{ 'bg-zinc-300 text-blue-500': editor.isActive('underline') }"
        >
          <i-ri-underline class="w-4 h-4" />
        </button>
        <template v-if="!field.inline">
          <!-- heading menu-->
          <Menu.Root>
            <Menu.Trigger class="flex outline-none items-center">
              <i-ri-heading class="w-4 h-4" />
              <i-heroicons-chevron-down class="w-3 h-3" />
            </Menu.Trigger>
            <Menu.Positioner>
              <Menu.Content class="shadow-sm rounded-md bg-white border outline-none overflow-hidden">
                <Menu.ItemGroup>
                  <Menu.ItemGroupLabel class="bg-zinc-300 px-3 py-2">Headings</Menu.ItemGroupLabel>
                  <Menu.Item
                    value="h1"
                    class="text-2xl flex items-center px-3 py-2 gap-2 cursor-pointer font-bold hover:bg-zinc-100"
                    @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
                  >
                    {{ t('Heading 1') }}
                  </Menu.Item>
                  <Menu.Item
                    value="h2"
                    class="text-xl flex items-center px-3 py-2 gap-2 font-semibold cursor-pointer hover:bg-zinc-100"
                    @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                  >
                    {{ t('Heading 2') }}
                  </Menu.Item>
                  <Menu.Item
                    value="h2"
                    class="text-lg flex items-center px-3 py-2 gap-2 font-medium cursor-pointer hover:bg-zinc-100"
                    @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
                  >
                    {{ t('Heading 3') }}
                  </Menu.Item>
                  <Menu.Item
                    value="h2"
                    class="text-md flex items-center px-3 py-2 gap-2 font-medium cursor-pointer hover:bg-zinc-100"
                    @click="editor.chain().focus().toggleHeading({ level: 4 }).run()"
                  >
                    {{ t('Heading 4') }}
                  </Menu.Item>
                  <Menu.Item
                    value="h2"
                    class="text-sm flex items-center px-3 py-2 gap-2 font-medium cursor-pointer hover:bg-zinc-100"
                    @click="editor.chain().focus().toggleHeading({ level: 5 }).run()"
                  >
                    {{ t('Heading 5') }}
                  </Menu.Item>
                  <Menu.Item
                    value="h2"
                    class="text-xs flex items-center px-3 py-2 gap-2 font-medium cursor-pointer hover:bg-zinc-100"
                    @click="editor.chain().focus().toggleHeading({ level: 6 }).run()"
                  >
                    {{ t('Heading 6') }}
                  </Menu.Item>
                </Menu.ItemGroup>
              </Menu.Content>
            </Menu.Positioner>
          </Menu.Root>
          <button
            title="Bullet list"
            @click="editor.chain().focus().toggleBulletList().run()"
            :class="{ 'bg-zinc-300 text-blue-500': editor.isActive('bulletList') }"
          >
            <i-ri-list-unordered class="w-4 h-4" />
          </button>
          <button
            title="Numbered list"
            @click="editor.chain().focus().toggleOrderedList().run()"
            :class="{ 'bg-zinc-300 text-blue-500': editor.isActive('orderedList') }"
          >
            <i-ri-list-ordered class="w-4 h-4" />
          </button>
        </template>
      </header>
      <div class="focus-within:ring focus-within:ring-zinc-700 rounded-b overflow-hidden [&>div]:overflow-y-auto [&>div]:max-h-48">
        <EditorContent :editor="editor" />
      </div>
    </section>
  </div>
</template>
