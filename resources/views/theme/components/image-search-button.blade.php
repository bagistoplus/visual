@pushOnce('scripts')
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.data('VisualImageSearch', () => ({
        librariesLoaded: false,
        isSearching: false,
        maxImageSize: 2000000, // 2MB
        uploadedImageUrl: null,

        handleImageSelection(event) {
          const imageFile = event.target.files?.[0];
          if (!this.validateImage(imageFile)) return;

          this.isSearching = true;

          this.uploadImage(imageFile);

          if (!this.librariesLoaded) {
            this.loadLibraries();
          }
        },

        validateImage(imageFile) {
          if (!imageFile) return false;

          if (!imageFile.type.includes('image/')) {
            this.showError("@lang('shop::app.search.images.index.only-images-allowed')");
            return false;
          }

          if (imageFile.size > this.maxImageSize) {
            this.showError("@lang('shop::app.search.images.index.size-limit-error')");
            return false;
          }

          return true;
        },

        uploadImage(imageFile) {
          const formData = new FormData();
          formData.append('image', imageFile);

          fetch("/search/upload", {
              method: 'POST',
              body: formData,
              credentials: 'include',
              headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
              }
            })
            .then(response => {
              if (!response.ok) {
                this.showError("@lang('shop::app.search.images.index.something-went-wrong')");
              };

              return response.text();
            })
            .then(data => {
              this.uploadedImageUrl = data;

              if (this.librariesLoaded) {
                this.analyzeImage();
              }
            })
            .catch(error => {
              console.log(error)
              this.showError("@lang('shop::app.search.images.index.something-went-wrong')");
              this.resetSearch();
            });
        },

        /**
         * Analyzes the uploaded image using MobileNet
         */
        async analyzeImage() {
          try {
            const net = await mobilenet.load();
            const results = await net.classify(document.getElementById('uploaded-image'));

            const analysedTerms = results.flatMap(result =>
              result.className.split(',').map(term => term.trim())
            );

            this.storeSearchResults(analysedTerms);
            this.redirectToSearchResults(analysedTerms);
          } catch (error) {
            console.error(error)
            this.showError('Something went wrong while analyzing the image.');
            this.resetSearch();
          }
        },

        storeSearchResults(terms) {
          localStorage.searchedImageUrl = this.uploadedImageUrl;
          localStorage.searchedTerms = terms.join('_');
        },

        redirectToSearchResults(terms) {
          const queryString = terms[0].split(' ').join('+');
          const url = new URL("{{ route('shop.search.index') }}");

          url.searchParams.append('query', queryString);
          url.searchParams.append('image-search', 1);

          window.location.href = url.toString().replace(/%2B/g, ' ');
        },

        loadLibraries() {
          this.loadScript('https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@latest/dist/tf.min.js')
            .then(() => this.loadScript(
              'https://cdn.jsdelivr.net/npm/tensorflow-models-mobilenet-patch@2.1.1/dist/mobilenet.min.js'))
            .then(() => {
              this.librariesLoaded = true;

              // If we already have an uploaded image URL, analyze it
              if (this.uploadedImageUrl) {
                this.analyzeImage();
              }
            }).catch(error => {
              this.resetSearch();
              this.showError("@lang('shop::app.search.images.index.something-went-wrong')");
            })
        },

        loadScript(src) {
          return new Promise((resolve, reject) => {
            if (document.querySelector(`script[src="${src}"]`)) {
              resolve();
              return;
            }

            const script = document.createElement('script');
            script.src = src;
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
          });
        },

        resetSearch() {
          this.isSearching = false;
          this.$refs.fileInput.value = '';
        },

        showError(message) {
          this.$dispatch('show-toast', {
            message,
            type: 'error'
          })
        }
      }));
    });
  </script>
@endPushOnce

<div x-data="VisualImageSearch" {{ $attributes->merge(['class' => 'flex items-center']) }}>
  <button
    type="button"
    aria-label="@lang('shop::app.search.images.index.search')"
    class="hover:text-primary transition-colors"
    x-bind:class="{ 'cursor-not-allowed': isSearching }"
    x-bind:disabled="isSearching"
    x-on:click="$refs.fileInput.click()"
  >
    <x-lucide-camera x-show="!isSearching" class="h-5 w-5" />
    <x-lucide-loader-2 x-show="isSearching" class="h-5 w-5 animate-spin" />
  </button>

  <input
    ref="imageSearchInput"
    type="file"
    class="hidden"
    x-ref="fileInput"
    accept="image/*"
    x-on:change="handleImageSelection($event)"
  />

  <img
    id="uploaded-image"
    class="hidden"
    x-bind:src="uploadedImageUrl"
    alt="uploaded image url"
    width="20"
    height="20"
  />
</div>
