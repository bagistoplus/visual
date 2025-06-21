<script lang="ts" setup>
  import { Dialog } from '@ark-ui/vue/dialog';
  import { Field } from '@ark-ui/vue/field';

  const isDirty = ref(false);
  const opened = ref(false);
  const model = defineModel<string | null>();
  const initialValue = ref<string | null | undefined>(null);
  const search = ref('');
  const selectedSet = ref('lucide');
  const initialized = ref(false);

  const { sets, isFetching, getIcons, findIconById, findSetByIconId, fetchSet } = useIconStore();

  const icons = computed(() => {
    const list = getIcons(selectedSet.value);

    if (!search.value) {
      return list;
    }

    return list.filter((icon) =>
      icon.name.toLowerCase().includes(search.value.toLowerCase())
    );
  });

  const selectedIcon = computed(() => {
    return model.value ? findIconById(model.value) : null;
  });

  watch(() => sets.value, (newSets) => {
    if (initialized.value || newSets.length === 0 || !model.value) {
      return;
    }

    const currentIconSet = findSetByIconId(model.value);

    if (currentIconSet) {
      selectedSet.value = currentIconSet.id;
      fetchSet(currentIconSet.id);
    }

    initialized.value = true;
  }, { immediate: true });

  function onChangeSet() {
    fetchSet(selectedSet.value);
  }

  function onSelectIcon(icon: any) {
    model.value = icon.id;
    isDirty.value = true;
  }

  function onCancel() {
    opened.value = false;
    model.value = initialValue.value;
    isDirty.value = false;
  }
</script>

<template>
  <div>
    <Dialog.Root
      v-model:open="opened"
      :modal="false"
      :close-on-interact-outside="false"
      @open-change="isDirty = false; initialValue = model"
    >
      <Dialog.Trigger class="flex items-center px-3 py-2.5 gap-4 border rounded relative">
        <span
          v-if="selectedIcon"
          v-html="selectedIcon.svg"
          class="w-8 h-8 text-gray-600"
        ></span>
        <i-heroicons-question-mark-circle
          v-else
          class="w-8 h-8 text-gray-600"
        />

        <button
          v-if="selectedIcon"
          class="absolute -top-2 -right-2 bg-zinc-100 rounded-full p-1 hover:bg-zinc-200"
          @click.stop="model = null"
        >
          <i-heroicons-x-mark class="w-4 h-4" />
        </button>
      </Dialog.Trigger>
      <Dialog.Positioner class="flex fixed z-50 top-14 left-14 bottom-0 w-[19.9rem] items-center justify-center">
        <Dialog.Content class="bg-white shadow flex flex-col w-full h-full overflow-hidden">
          <header class="flex-none h-12 border-b border-neutral-200 flex gap-3 px-4 items-center justify-between">
            <Dialog.Title>{{ $t('Icon Picker') }}</Dialog.Title>
            <button
              @click="onCancel"
              class="cursor-pointer rounded-lg p-0.5 text-neutral-700 hover:bg-neutral-300"
            >
              <i-heroicons-x-mark class="w-5 h-5" />
            </button>
          </header>
          <div class="flex-1 flex flex-col  min-h-0  overflow-y-auto">
            <div class="p-3">
              <select
                v-model="selectedSet"
                @change="onChangeSet"
                class="border rounded-t border-b-0 h-10 px-3 w-full focus:outline-none "
              >
                <option
                  v-for="set in sets"
                  :value="set.id"
                >{{ set.name }}</option>
              </select>
              <Field.Root class="relative">
                <i-heroicons-magnifying-glass class="absolute left-3 top-2.5 text-gray-500" />
                <Field.Input
                  v-model="search"
                  class="w-full pr-3 pl-9 h-10 text-surface-500 rounded-b border border-gray-300 focus:outline-none focus:ring focus:ring-gray-700"
                  placeholder="Search"
                />
              </Field.Root>
            </div>

            <div
              v-if="isFetching"
              class="flex items-center justify-center py-6"
            >
              <i-lucide-loader-2 class="w-6 h-6 animate-spin text-gray-500" />
            </div>

            <div
              v-else
              class="p-3 grid grid-cols-4  gap-3"
            >
              <button
                v-for="icon in icons"
                type="button"
                class="flex justify-center p-3 border rounded text-gray-500 "
                :class="{
                  'border-blue-300 bg-blue-50 hover:bg-blue-50': model === icon.id,
                  'hover:bg-gray-100': model !== icon.id,
                }"
                @click="onSelectIcon(icon)"
              >
                <span
                  v-html="icon.svg"
                  class="w-full "
                />
              </button>
            </div>
          </div>
          <footer class="flex-none flex items-center gap-3 p-3 justify-end h-12 border-t border-neutral-200">
            <button
              @click="onCancel"
              class="text-sm shadow px-3 py-1 rounded bg-neutral-100 border"
            >{{ $t('Cancel') }}</button>
            <Dialog.CloseTrigger
              class="text-sm shadow px-3 py-1 rounded bg-gray-800 text-white border hover:bg-gray-700"
              :class="{ 'opacity-40 cursor-not-allowed': !isDirty }"
              :disabled="!isDirty"
            >Select</Dialog.CloseTrigger>
          </footer>
        </Dialog.Content>
      </Dialog.Positioner>
    </Dialog.Root>
  </div>
</template>
