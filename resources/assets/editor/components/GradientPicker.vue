<script setup lang="ts">
import type { PropertyField } from '@craftile/types';
import { Popover } from '@ark-ui/vue/popover';
import { Slider } from '@ark-ui/vue/slider';
import ColorPicker from './ColorPicker.vue';
import { Button } from '@craftile/editor/ui';

interface GradientStop {
  color: string; // hexa format (#rrggbbaa)
  position: number; // 0-100
}

interface GradientValue {
  type: 'linear' | 'radial';
  angle?: number;
  stops: GradientStop[];
}

interface Props {
  field: PropertyField;
}

const props = defineProps<Props>();
const model = defineModel<GradientValue>({
  default: () => ({
    type: 'linear',
    angle: 90,
    stops: [
      { color: '#000000ff', position: 0 },
      { color: '#ffffffff', position: 100 },
    ],
  }),
});

// Helper to trigger emit after mutation
function triggerUpdate() {
  model.value = { ...model.value };
}

const opened = ref(false);
const selectedStopIndex = ref<number | null>(0);

// Initialize with default if null
onBeforeMount(() => {
  console.log(model.value)
  if (!model.value) {
    model.value = {
      type: 'linear',
      angle: 90,
      stops: [
        { color: '#000000ff', position: 0 },
        { color: '#ffffffff', position: 100 },
      ],
    };
  }
});

// Gradient preview style
const gradientPreview = computed(() => {
  if (!model.value) {
    return 'linear-gradient(90deg, #000000 0%, #ffffff 100%)';
  }
  const { type, angle, stops } = model.value;
  const stopsStr = stops
    .map((s) => {
      return `${hexaToRgba(s.color)} ${s.position}%`;
    })
    .join(', ');

  if (type === 'linear') {
    return `linear-gradient(${angle}deg, ${stopsStr})`;
  }

  return `radial-gradient(circle, ${stopsStr})`;
});

function hexaToRgba(hexa: string): string {
  const hex = hexa.replace('#', '');
  const r = parseInt(hex.slice(0, 2), 16);
  const g = parseInt(hex.slice(2, 4), 16);
  const b = parseInt(hex.slice(4, 6), 16);
  const a = hex.length === 8 ? parseInt(hex.slice(6, 8), 16) / 255 : 1;
  return `rgba(${r}, ${g}, ${b}, ${a})`;
}

function addStop(position: number) {
  // Find the two stops that this position is between
  const sortedStops = [...model.value.stops].sort((a, b) => a.position - b.position);
  let beforeStop = sortedStops[0];
  let afterStop = sortedStops[sortedStops.length - 1];

  for (let i = 0; i < sortedStops.length - 1; i++) {
    if (sortedStops[i].position <= position && sortedStops[i + 1].position >= position) {
      beforeStop = sortedStops[i];
      afterStop = sortedStops[i + 1];
      break;
    }
  }

  // Interpolate color between the two stops
  const ratio =
    (position - beforeStop.position) / (afterStop.position - beforeStop.position);
  const interpolatedColor = interpolateColor(beforeStop.color, afterStop.color, ratio);

  const newStop: GradientStop = {
    color: interpolatedColor,
    position: Math.round(position),
  };

  model.value.stops.push(newStop);
  model.value.stops.sort((a, b) => a.position - b.position);
  triggerUpdate();

  selectedStopIndex.value = model.value.stops.indexOf(newStop);
}

function interpolateColor(color1: string, color2: string, ratio: number): string {
  const r1 = parseInt(color1.slice(1, 3), 16);
  const g1 = parseInt(color1.slice(3, 5), 16);
  const b1 = parseInt(color1.slice(5, 7), 16);
  const a1 = color1.length === 9 ? parseInt(color1.slice(7, 9), 16) : 255;

  const r2 = parseInt(color2.slice(1, 3), 16);
  const g2 = parseInt(color2.slice(3, 5), 16);
  const b2 = parseInt(color2.slice(5, 7), 16);
  const a2 = color2.length === 9 ? parseInt(color2.slice(7, 9), 16) : 255;

  const r = Math.round(r1 + (r2 - r1) * ratio);
  const g = Math.round(g1 + (g2 - g1) * ratio);
  const b = Math.round(b1 + (b2 - b1) * ratio);
  const a = Math.round(a1 + (a2 - a1) * ratio);

  return `#${r.toString(16).padStart(2, '0')}${g.toString(16).padStart(2, '0')}${b.toString(16).padStart(2, '0')}${a.toString(16).padStart(2, '0')}`;
}

function removeStop(index: number) {
  if (model.value.stops.length <= 2) return;

  // Remove the stop
  model.value.stops.splice(index, 1);
  triggerUpdate();

  // Always keep a stop selected
  if (selectedStopIndex.value === index) {
    // Select the previous stop, or first stop if we removed the first one
    selectedStopIndex.value = Math.max(0, index - 1);
  } else if (selectedStopIndex.value !== null && selectedStopIndex.value > index) {
    selectedStopIndex.value--;
  }
}

function selectStop(index: number) {
  selectedStopIndex.value = index;
}

// Drag state
const isDragging = ref(false);
const dragStopIndex = ref<number | null>(null);
const hasDragged = ref(false);
const lastClickTime = ref(0);

function onGradientBarClick(event: MouseEvent) {
  // Don't add stop if we just finished dragging or just clicked a stop
  const now = Date.now();
  if (hasDragged.value || (now - lastClickTime.value) < 200) {
    hasDragged.value = false;
    return;
  }

  const rect = (event.currentTarget as HTMLElement).getBoundingClientRect();
  const position = ((event.clientX - rect.left) / rect.width) * 100;
  addStop(position);
}

function onStopMouseDown(event: MouseEvent, index: number) {
  event.preventDefault();
  event.stopPropagation();

  lastClickTime.value = Date.now();
  isDragging.value = true;
  dragStopIndex.value = index;
  selectedStopIndex.value = index;
  hasDragged.value = false;

  const gradientBar = (event.target as HTMLElement).parentElement as HTMLElement;
  const rect = gradientBar.getBoundingClientRect();
  const startX = event.clientX;

  const onMouseMove = (e: MouseEvent) => {
    if (isDragging.value && dragStopIndex.value !== null) {
      // Only mark as dragged if moved more than 3px
      if (Math.abs(e.clientX - startX) > 3) {
        hasDragged.value = true;
      }
      const position = ((e.clientX - rect.left) / rect.width) * 100;
      const clampedPosition = Math.max(0, Math.min(100, position));

      // Update position directly
      model.value.stops[dragStopIndex.value].position = Math.round(clampedPosition);
      triggerUpdate();
    }
  };

  const onMouseUp = () => {
    isDragging.value = false;
    dragStopIndex.value = null;
    document.removeEventListener('mousemove', onMouseMove);
    document.removeEventListener('mouseup', onMouseUp);

    // Reset hasDragged after a short delay
    setTimeout(() => {
      hasDragged.value = false;
    }, 100);
  };

  document.addEventListener('mousemove', onMouseMove);
  document.addEventListener('mouseup', onMouseUp);
}

const selectedStop = computed(() => {
  if (selectedStopIndex.value !== null && model.value) {
    return model.value.stops[selectedStopIndex.value];
  }
  return null;
});

watch(
  () => selectedStop.value?.position,
  (newPos) => {
    if (newPos !== undefined && selectedStopIndex.value !== null && model.value) {
      // Re-sort stops when position changes
      const stop = model.value.stops[selectedStopIndex.value];
      model.value.stops.sort((a, b) => a.position - b.position);
      triggerUpdate();

      // Update selectedStopIndex to follow the moved stop
      selectedStopIndex.value = model.value.stops.findIndex((s) => s === stop);
    }
  }
);
</script>

<template>
  <div class="flex flex-col gap-2">
    <label
      v-if="field.label"
      class="text-sm font-medium text-gray-700"
    >
      {{ field.label }}
    </label>

    <!-- Popover Editor -->
    <Popover.Root v-model:open="opened">
      <!-- Gradient Preview as Trigger -->
      <Popover.Trigger as-child>
        <div
          class="h-12 rounded border border-zinc-300 cursor-pointer hover:border-zinc-400 transition-colors"
          :style="{ background: gradientPreview }"
        />
      </Popover.Trigger>
      <!-- <Teleport to="body"> -->
      <Popover.Positioner class="w-[var(--reference-width)] !min-w-auto">
        <Popover.Content class="border border-zinc-300 rounded shadow-lg z-50 bg-white w-full p-2">
          <div class="flex flex-col gap-4">
            <!-- Gradient Type Selector -->
            <div class="flex gap-2">
              <Button
                fullWidth
                :variant="model.type === 'linear' ? 'accent' : 'default'"
                @click="() => { model.type = 'linear'; triggerUpdate(); }"
              >
                Linear
              </Button>
              <Button
                fullWidth
                :variant="model.type === 'radial' ? 'accent' : 'default'"
                @click="() => { model.type = 'radial'; triggerUpdate(); }"
              >
                Radial
              </Button>
            </div>

            <!-- Angle Slider (for linear only) -->

            <Slider.Root
              v-if="model.type === 'linear'"
              :min="0"
              :max="360"
              :model-value="[model.angle ?? 90]"
              @value-change="(details: any) => { model.angle = details.value[0]; triggerUpdate(); }"
              class="relative w-full"
            >
              <Slider.Label class="text-sm font-medium text-zinc-700 mb-1 block">Angle: {{ model.angle }}°</Slider.Label>
              <Slider.Control class="flex-1 flex items-center h-2 select-none touch-none">
                <Slider.Track class="w-full overflow-hidden h-1 rounded-full bg-gray-100">
                  <Slider.Range class="h-full bg-blue-500 rounded-full" />
                </Slider.Track>
                <Slider.Thumb
                  :index="0"
                  class="w-4 h-4 bg-white border-2 border-blue-500 rounded-full shadow-md cursor-pointer hover:scale-110 transition-transform"
                />
              </Slider.Control>
            </Slider.Root>

            <!-- Gradient Bar with Stops -->
            <div class="relative">
              <div
                class="h-4 rounded relative cursor-crosshair"
                :style="{ background: gradientPreview }"
                @click="onGradientBarClick"
              >
                <div
                  v-for="(stop, index) in model.stops"
                  :key="index"
                  class="absolute w-3 h-3 rounded-full border-2 border-white shadow-md cursor-move active:cursor-grabbing top-1/2 -translate-y-1/2 -translate-x-1/2 hover:scale-110 transition-transform"
                  :class="{ 'ring-2 ring-blue-500 scale-110': selectedStopIndex === index }"
                  :style="{
                    left: `${stop.position}%`,
                    backgroundColor: stop.color,
                  }"
                  @mousedown="onStopMouseDown($event, index)"
                  @dblclick.stop="removeStop(index)"
                />
              </div>
              <p class="text-xs text-gray-500 mt-1">
                Click to add stop • Drag to move • Double-click stop to remove
              </p>
            </div>

            <!-- Selected Stop Editor -->
            <div
              v-if="selectedStop"
              class="flex flex-col gap-3 p-3 border rounded bg-gray-50"
            >
              <div class="flex items-center justify-between">
                <p class="text-sm font-medium">Edit Color Stop</p>
                <button
                  type="button"
                  v-if="model.stops.length > 2"
                  class="text-xs text-red-600 hover:text-red-700"
                  @click="removeStop(selectedStopIndex!)"
                >
                  Remove
                </button>
              </div>

              <ColorPicker
                v-model="selectedStop.color"
                label="Color"
              />

              <Slider.Root
                :min="0"
                :max="100"
                :model-value="[selectedStop!.position]"
                @value-change="(details: any) => selectedStop!.position = details.value[0]"
                class="relative w-full"
              >
                <Slider.Label class="text-sm font-medium text-zinc-700 mb-1 block">Position: {{ selectedStop.position }}%</Slider.Label>
                <Slider.Control class="flex-1 flex items-center h-2 select-none touch-none">
                  <Slider.Track class="w-full overflow-hidden h-1 rounded-full bg-gray-100">
                    <Slider.Range class="h-full bg-blue-500 rounded-full" />
                  </Slider.Track>
                  <Slider.Thumb
                    :index="0"
                    class="w-4 h-4 bg-white border-2 border-blue-500 rounded-full shadow-md cursor-pointer hover:scale-110 transition-transform"
                  />
                </Slider.Control>
              </Slider.Root>
            </div>

            <!-- <div class="flex justify-between items-center pt-2 border-t">
              <button
                type="button"
                class="text-sm text-gray-600 hover:text-gray-800"
                @click="selectedStopIndex = null"
                v-if="selectedStopIndex !== null"
              >
                Clear Selection
              </button>
              <button
                type="button"
                class="px-4 py-2 text-sm bg-blue-500 text-white rounded hover:bg-blue-600 ml-auto transition-colors"
                @click="opened = false"
              >
                Done
              </button>
            </div> -->
          </div>
        </Popover.Content>
      </Popover.Positioner>
      <!-- </Teleport> -->
    </Popover.Root>
  </div>
</template>
