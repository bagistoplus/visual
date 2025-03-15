@props([
    'name' => '',
])

@error($name)
  <p class="text-danger mt-1 text-sm">{{ $message }}</p>
@enderror
