<div class="relative" x-data="{ showUserMenu: false }">
  <button class="relative p-2" aria-label="user menu" @click="showUserMenu = !showUserMenu">
    <x-lucide-user class="hover:text-primary h-5 w-5 transition-colors" />
  </button>
  <div x-show="showUserMenu" x-transition
    class="bg-surface absolute right-0 mt-2 rounded-lg border py-2 text-neutral-600 shadow-lg"
    @click.outside="showUserMenu = false">
    @guest('customer')
      <div class="w-72">
        <div class="border-surface-600 border-b px-4 py-3">
          <p class="text-lg font-semibold">
            @lang('shop::app.components.layouts.header.welcome-guest')
          </p>
          <p class="mt-1 text-sm">
            @lang('shop::app.components.layouts.header.dropdown-text')
          </p>
        </div>
        <div class="p-4">
          <div class="grid grid-cols-2 gap-2">
            <a class="bg-primary text-primary-100 flex items-center justify-center gap-2 rounded-lg px-4 py-2 transition-opacity hover:opacity-90"
              href="{{ route('shop.customer.session.create') }}">
              <x-lucide-log-in class="h-4 w-4" />
              @lang('shop::app.components.layouts.header.sign-in')
            </a>
            <a class="border-primary text-primary hover:bg-primary-50 hover:text-primary-600 flex items-center justify-center gap-2 rounded-lg border px-4 py-2 transition-colors"
              href="{{ route('shop.customers.register.index') }}">
              <x-lucide-user-plus class="h-4 w-4" />
              @lang('shop::app.components.layouts.header.sign-up')
            </a>
          </div>
        </div>
      </div>
    @endguest

    @auth('customer')
      @php
        $menuItems = collect([
            [
                'route' => 'shop.customers.account.profile.index',
                'text' => __('shop::app.components.layouts.header.profile'),
                'icon' => 'lucide-user-circle-2',
                'show' => true,
            ],
            [
                'route' => 'shop.customers.account.orders.index',
                'text' => __('shop::app.components.layouts.header.orders'),
                'icon' => 'lucide-package',
                'show' => true,
            ],
            [
                'route' => 'shop.customers.account.wishlist.index',
                'text' => __('shop::app.components.layouts.header.wishlist'),
                'icon' => 'lucide-heart',
                'show' => !!core()->getConfigData('customer.settings.wishlist.wishlist_option'),
            ],
        ])->filter(fn($item) => $item['show']);
      @endphp
      <div class="w-64">
        <div class="border-surface-600 border-b px-4 py-3">
          <div class="flex items-center gap-3">
            <div class="bg-primary-50 flex h-10 w-10 items-center justify-center rounded-full">
              <x-lucide-user class="text-primary h-5 w-5" />
            </div>
            <div>
              <p class="text-on-surface font-medium">
                {{ auth()->guard('customer')->user()->first_name }}
                {{ auth()->guard('customer')->user()->last_name }}
              </p>
              <p class="text-secondary text-sm">
                {{ auth()->guard('customer')->user()->email }}
              </p>
            </div>
          </div>
        </div>
        <div class="py-2">
          @foreach ($menuItems as $item)
            <a href="{{ route($item['route']) }}"
              class="hover:text-primary hover:bg-surface-alt flex items-center gap-3 px-4 py-2 transition-colors">
              @svg($item['icon'], ['class' => 'h-5 w-5'])
              {{ $item['text'] }}
            </a>
          @endforeach
        </div>
        <div class="border-surface-600 border-t pt-2">
          <form action="{{ route('shop.customer.session.destroy') }}">
            @csrf
            @method('delete')
            <button type="submit"
              class="hover:text-danger hover:bg-surface-alt flex w-full items-center gap-3 px-4 py-2 transition-colors">
              <x-lucide-log-out class="h-5 w-5" />
              @lang('shop::app.components.layouts.header.logout')
            </button>
          </form>
        </div>
      </div>
    @endauth
  </div>
</div>
