import { ref } from 'vue';

interface Font {
  slug: string;
  name: string;
  weights: number[];
  styles: string[];
}

const fonts = ref<Font[]>([]);
const isFetching = ref(false);
const isFetched = ref(false);

export function useBunnyFonts() {
  const fetchFonts = async () => {
    if (isFetched.value || isFetching.value) {
      return fonts.value;
    }

    isFetching.value = true;
    try {
      const response = await fetch('https://fonts.bunny.net/list');
      const data = await response.json();

      const formatted: Font[] = [];
      Object.keys(data).forEach((key) => {
        const fontData = (data as any)[key];
        formatted.push({
          slug: key,
          name: fontData.familyName || key.replace(/-/g, ' '),
          weights: fontData.weights || [400],
          styles: fontData.styles || ['normal'],
        });
      });

      fonts.value = formatted;
      isFetched.value = true;
    } catch (error) {
      console.error('Failed to fetch fonts:', error);
    } finally {
      isFetching.value = false;
    }

    return fonts.value;
  };

  const findFont = (slug: string): Font | undefined => {
    return fonts.value.find((f) => f.slug === slug);
  };

  return {
    fonts,
    isFetching,
    isFetched,
    fetchFonts,
    findFont,
  };
}
