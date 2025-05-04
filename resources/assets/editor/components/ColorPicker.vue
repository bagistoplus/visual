<script setup lang="ts">
  import { ColorPicker, parseColor } from '@ark-ui/vue/color-picker'

  const value = defineModel({
    set(val: any) {
      return val.toString('hex');
    },

    get(val: string) {
      try {
        return parseColor(val);
      } catch (e) {
        // Fallback to default color if parsing fails
        console.error('Invalid color value:', val, e);
        return parseColor('#000000');
      }
    }
  });
  const props = withDefaults(defineProps<{ usedColors?: string[], label?: string }>(), { usedColors: () => [] });
  const usedColors = computed(() => props.usedColors.map(c => parseColor(c)));
  const selected = ref<any[]>([]);

  function onSelect({ value: color }: any) {
    value.value = color.toString('hex');

    if (usedColors.value.some(c => c.toHexInt() === color.toHexInt())) {
      return;
    }

    if (selected.value.some(c => c.toHexInt() === color.toHexInt())) {
      return;
    }

    selected.value.unshift(color);
  }
</script>

<template>
  <ColorPicker.Root
    format="rgba"
    class="flex flex-col gap-1.5"
    :model-value="value"
    @value-change-end="onSelect"
  >
    <ColorPicker.Label class="font-medium text-sm">
      {{ label }}
    </ColorPicker.Label>
    <ColorPicker.Control class="flex gap-2">
      <ColorPicker.Trigger class="appearance-none rounded cursor-pointer inline-flex items-center justify-center outline-none h-10 min-w-10 gap-2 border border-zinc-300">
        <ColorPicker.TransparencyGrid class="rounded" />
        <ColorPicker.ValueSwatch class="h-7 w-7 rounded shadow" />
      </ColorPicker.Trigger>

      <ColorPicker.ChannelInput
        channel="hex"
        class="appearance-none rounded bg-none outline-0 relative w-full border px-3 h-10 min-w-10 focus:ring focus:ring-gray-700"
      />
      <!-- <ColorPicker.ChannelInput channel="alpha" /> -->
      <!-- <ColorPicker.ValueText /> -->
    </ColorPicker.Control>

    <ColorPicker.Positioner class="w-60 !z-20 pl-1">
      <ColorPicker.Content class="flex flex-col p-4 gap-3 rounded bg-white border shadow-sm data-[state=open]:animate-fade-in data-[state=closed]:animate-fade-out data-[state=closed]:hidden">
        <ColorPicker.Area class="rounded overflow-hidden h-36 touch-none forced-color-adjust-none">
          <ColorPicker.AreaBackground class="h-full rounded" />
          <ColorPicker.AreaThumb class="rounded-full w-2.5 h-2.5 outline-none border-2 border-white" />
        </ColorPicker.Area>

        <div class="flex gap-2">
          <ColorPicker.EyeDropperTrigger class="appearance-none rounded cursor-pointer inline-flex outline-none relative select-none items-center justify-center border h-8 min-w-8">
            <i-heroicons-eye-dropper class="w-4 h-4" />
          </ColorPicker.EyeDropperTrigger>
          <div class="flex flex-col gap-2 w-full">
            <ColorPicker.ChannelSlider
              channel="hue"
              class="rounded w-full"
            >
              <ColorPicker.ChannelSliderTrack class="h-2.5 rounded" />
              <ColorPicker.ChannelSliderThumb class="h-2.5 w-2.5 cursor-pointer rounded-full outline-none border-2 border-white -translate-x-1/2 -translate-y-1/2" />
            </ColorPicker.ChannelSlider>
            <ColorPicker.ChannelSlider
              channel="alpha"
              class="rounded w-full"
            >
              <ColorPicker.TransparencyGrid />
              <ColorPicker.ChannelSliderTrack class="h-2.5 rounded" />
              <ColorPicker.ChannelSliderThumb class="h-2.5 w-2.5 cursor-pointer rounded-full outline-none border-2 border-white -translate-x-1/2 -translate-y-1/2" />
            </ColorPicker.ChannelSlider>
          </div>
        </div>

        <ColorPicker.View
          format="rgba"
          class="flex gap-3"
        >
          <ColorPicker.ChannelInput
            channel="hex"
            class="flex-1 border px-3 h-8 w-0 rounded outline-0 focus:ring focus:ring-gray-700"
          />
          <ColorPicker.ChannelInput
            channel="alpha"
            class=" flex-none border px-3 h-8 rounded outline-0 focus:ring focus:ring-gray-700"
          />
        </ColorPicker.View>

        <div v-if="selected.length > 0">
          <p class="text-xs font-medium">{{ $t('Recently selected') }}</p>

          <ColorPicker.SwatchGroup class="grid grid-cols-6 gap-2 mt-2">
            <ColorPicker.SwatchTrigger
              v-for="color in selected.slice(0, 6)"
              :value="color"
              :key="color!.toHexInt()"
            >
              <ColorPicker.Swatch
                :value="color"
                class="w-6 h-6 rounded border"
              />
            </ColorPicker.SwatchTrigger>
          </ColorPicker.SwatchGroup>
        </div>

        <div>
          <p class="text-xs font-medium">{{ $t('Currently used') }}</p>

          <ColorPicker.SwatchGroup class="grid grid-cols-6 gap-2 mt-2">
            <ColorPicker.SwatchTrigger
              v-for="color in usedColors"
              :value="color"
              :key="color"
            >
              <ColorPicker.Swatch
                :value="color"
                class="w-6 h-6 rounded border"
              />
            </ColorPicker.SwatchTrigger>
          </ColorPicker.SwatchGroup>
        </div>
      </ColorPicker.Content>
    </ColorPicker.Positioner>
    <ColorPicker.HiddenInput />
  </ColorPicker.Root>
</template>