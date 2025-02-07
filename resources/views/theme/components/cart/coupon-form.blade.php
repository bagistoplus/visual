<form class="space-y-2"><label for="coupon" class="block text-sm font-medium text-gray-700">Have a coupon?</label>
  <div class="flex gap-2">
    <div class="relative flex-1">
      <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center"><svg
          xmlns="http://www.w3.org/2000/svg"
          width="24"
          height="24"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
          class="lucide lucide-ticket h-5 w-5 text-gray-400"
        >
          <path
            d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"
          ></path>
          <path d="M13 5v2"></path>
          <path d="M13 17v2"></path>
          <path d="M13 11v2"></path>
        </svg></div><input
        id="coupon"
        type="text"
        placeholder="Enter code"
        class="focus:ring-primary focus:border-primary w-full rounded-lg border border-gray-300 py-2 pl-10 pr-3 focus:ring-2"
        value=""
      >
    </div><button type="submit"
      class="bg-primary rounded-lg px-4 py-2 text-white transition-opacity hover:opacity-90">Apply</button>
  </div>
</form>
