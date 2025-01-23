@props([
    'title' => 'Accordion',
])

<div x-data="{
    id: null,
    get expanded() {
        return this.active.includes(this.id);
    },
    toggle() {
        if (this.expanded) {
            this.active = this.active.filter(id => id !== this.id);
        } else {
            this.active.push(this.id)
        }
    },
    init() {
        this.id = Array.from(this.$el.parentElement.children).findIndex(el => el === this.$el);
    }
}" role="region" {{ $attributes->merge(['class' => 'border-t']) }}>
  @if (isset($trigger))
    {{ $trigger }}
  @else
    <button x-on:click="toggle" x-bind:aria-expanded="expanded" type="button"
      class="flex w-full items-center justify-between py-2 font-bold">
      <span>{{ $title }}</span>
      <x-lucide-chevron-up x-cloak x-show="expanded" class="ml-4 h-5 w-5" />
      <x-lucide-chevron-down x-cloak x-show="!expanded" class="ml-4 h-5 w-5" />
    </button>
  @endif

  <div x-cloak x-show="expanded" x-collapse>
    {{ $slot }}
  </div>
</div>
