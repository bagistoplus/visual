import { useFetchIcons } from '../api';

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

const { execute, isFetching, onFetchResponse } = useFetchIcons({ immediate: true });

onFetchResponse(async (response) => {
  const responseData = await response.json();

  if (!sets.value.length && responseData.sets) {
    sets.value = responseData.sets;
  }

  if (responseData.currentSet) {
    loadedSets.value.set(responseData.currentSet, responseData.icons);
    loadingSets.value.delete(responseData.currentSet);
  }
});

function fetchSet(setId: string) {
  if (loadedSets.value.has(setId) || loadingSets.value.has(setId)) {
    return;
  }

  loadingSets.value.add(setId);

  execute({ set: setId });
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
