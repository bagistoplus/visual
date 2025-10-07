import { useHttpClient } from './http';

export interface Icon {
  id: string;
  name: string;
  svg: string;
}

export interface IconSet {
  id: string;
  name: string;
  prefix: string;
}

const sets = ref<IconSet[]>([]);
const loadedSets = ref(new Map<string, Icon[]>());
const loadingSets = ref(new Set<string>());
const initialized = ref(false);

const requestUrl = ref(window.editorConfig.routes.getIcons);
const { get } = useHttpClient();
const { isFetching, execute, onSuccess, onError } = get(requestUrl);

onSuccess((responseData) => {
  if (!sets.value.length && responseData.sets) {
    sets.value = responseData.sets;
  }

  if (responseData.currentSet) {
    loadedSets.value.set(responseData.currentSet, responseData.icons);
    loadingSets.value.delete(responseData.currentSet);
  }
});

onError((error) => {
  console.error('Failed to fetch icons:', error);
});

async function fetchIcons(params?: Record<string, any>) {
  const url = new URL(window.editorConfig.routes.getIcons, window.location.origin);

  if (params) {
    for (const [key, value] of Object.entries(params)) {
      url.searchParams.append(key, value);
    }
  }

  requestUrl.value = url.href;
  await execute();
}

function ensureInitialized() {
  if (!initialized.value) {
    initialized.value = true;
    fetchIcons();
  }
}

function fetchSet(setId: string) {
  if (loadedSets.value.has(setId) || loadingSets.value.has(setId)) {
    return;
  }

  loadingSets.value.add(setId);

  fetchIcons({ set: setId });
}

function getIcons(setId: string): Icon[] {
  return loadedSets.value.get(setId) || [];
}

function isSetLoading(setId: string): boolean {
  return loadingSets.value.has(setId);
}

function findSetByIconId(iconId: string): IconSet | undefined {
  return sets.value.find((set) => iconId.startsWith(set.prefix));
}

function findIconById(iconId: string): Icon | null {
  const set = findSetByIconId(iconId);

  if (!set) {
    return null;
  }

  const icons = getIcons(set.id);

  return icons.find((icon) => icon.id === iconId) || null;
}

export function useIconStore() {
  // Ensure icons are fetched when first used
  ensureInitialized();

  return {
    sets: computed(() => sets.value),
    getIcons,
    fetchSet,
    isSetLoading,
    isFetching,
    findSetByIconId,
    findIconById,
  };
}
