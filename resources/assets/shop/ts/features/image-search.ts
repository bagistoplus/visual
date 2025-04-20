import { defineComponent } from '../utils/define-component';

interface ImageSearchAPI {
  searchUrl: string;
  messages: {
    invalidFileType: string;
    fileTooLarge: string;
    uploadFailed: string;
    analysisFailed: string;
    libraryLoadFailed: string;
  };
  librariesLoaded: boolean;
  isSearching: boolean;
  uploadedImageUrl: string | null;

  handleImageSelection(event: Event): void;
  validateImage(image: File): boolean;
  uploadImage(image: File): Promise<void>;
  analyzeImage(): Promise<void>;
  storeSearchResults(terms: string[]): void;
  redirectToSearchResults(terms: string[]): void;

  resetSearch(): void;

  loadLibraries(): Promise<void>;
  loadScript(src: string): Promise<void>;
}

// Constants
const MAX_IMAGE_SIZE = 2_000_000; // 2MB
const LIBRARIES = {
  tensorflow:
    'https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@latest/dist/tf.min.js',
  mobilenet:
    'https://cdn.jsdelivr.net/npm/tensorflow-models-mobilenet-patch@2.1.1/dist/mobilenet.min.js',
};

export default defineComponent<ImageSearchAPI>({
  name: 'image-search',

  setup: (props) => ({
    searchUrl: props.searchUrl || '/search',
    messages: {
      invalidFileType: 'Only image files are allowed.',
      fileTooLarge: 'Maximum image size is 2MB.',
      uploadFailed: 'Something went wrong while uploading the image.',
      analysisFailed: 'Something went wrong while analyzing the image.',
      libraryLoadFailed: 'Something went wrong while loading libraries.',
      ...(props.messages || {}),
    },
    librariesLoaded: false,
    isSearching: false,
    uploadedImageUrl: null,

    handleImageSelection(event) {
      const image = (event.target as HTMLInputElement).files?.[0];

      if (!image || !this.validateImage(image)) {
        return;
      }

      this.isSearching = true;
      this.uploadImage(image);

      if (!this.librariesLoaded) {
        this.loadLibraries();
      }
    },

    validateImage(image: File) {
      if (!image) return false;

      if (!image.type.startsWith('image/')) {
        this.$toaster.error(this.messages.invalidFileType);
        return false;
      }

      if (image.size > MAX_IMAGE_SIZE) {
        this.$toaster.error(this.messages.fileTooLarge);
        return false;
      }

      return true;
    },

    async uploadImage(image: File) {
      const formData = new FormData();
      formData.append('image', image);

      try {
        const response = await this.$request(
          '/search/upload',
          'POST',
          formData,
          { credentials: 'include' }
        );

        this.uploadedImageUrl = response;

        if (this.librariesLoaded) {
          await this.analyzeImage();
        }
      } catch (error) {
        this.$toaster.error(this.messages.uploadFailed);
        this.resetSearch();
      }
    },

    async analyzeImage() {
      try {
        const net = await (window as any).mobilenet.load();
        const results = await net.classify(
          document.getElementById('uploaded-image')
        );

        const terms = results.flatMap((r: any) =>
          r.className.split(',').map((t: string) => t.trim())
        );

        this.storeSearchResults(terms);
        this.redirectToSearchResults(terms);
      } catch (error) {
        this.$toaster.error(this.messages.analysisFailed);
        this.resetSearch();
      }
    },

    storeSearchResults(terms) {
      localStorage.setItem('searchedImageUrl', this.uploadedImageUrl as string);
      localStorage.setItem('searchedTerms', terms.join('_'));
    },

    redirectToSearchResults(terms) {
      const q = terms[0].replace(/\s+/g, '+');
      const url = new URL(this.searchUrl, window.location.origin);
      url.searchParams.append('query', q);
      url.searchParams.append('image-search', '1');
      window.location.href = url.toString().replace(/%2B/g, ' ');
    },

    async loadLibraries() {
      try {
        await this.loadScript(LIBRARIES.tensorflow);
        await this.loadScript(LIBRARIES.mobilenet);

        this.librariesLoaded = true;
        if (this.uploadedImageUrl) await this.analyzeImage();
      } catch (error) {
        this.$toaster.error(this.messages.libraryLoadFailed);
        this.resetSearch();
      }
    },

    loadScript(src) {
      return new Promise((resolve, reject) => {
        // Don't load script if it's already loaded
        if (document.querySelector(`script[src="${src}"]`)) {
          return resolve();
        }

        const script = document.createElement('script');
        script.src = src;
        script.onload = () => resolve();
        script.onerror = reject;
        document.head.appendChild(script);
      });
    },

    resetSearch() {
      this.isSearching = false;
      this.uploadedImageUrl = null;
      if (this.$refs.fileInput) {
        (this.$refs.fileInput as HTMLInputElement).value = '';
      }
    },
  }),

  parts: {
    trigger(api) {
      return {
        type: 'button',
        'x-bind:disabled': () => api.isSearching,
        'x-on:click': () => api.$refs.fileInput?.click?.(),
      };
    },

    fileInput(api) {
      return {
        type: 'file',
        accept: 'image/*',
        'x-ref': 'fileInput',
        'x-on:change': (e: Event) => api.handleImageSelection(e),
      };
    },

    preview(api) {
      return {
        id: 'uploaded-image',
        'x-bind:src': () => api.uploadedImageUrl,
      };
    },
  },
});
