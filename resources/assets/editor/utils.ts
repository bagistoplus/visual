import type { Setting } from "./types";

export function groupSettings(settings: Setting[]) {
  const groupedSettings: { name: string; settings: Setting[]; }[] = [];
  let group = {
    name: 'Settings',
    settings: [] as Setting[]
  };

  settings.forEach(setting => {
    if (setting.type === 'header') {
      if (group.settings.length > 0) {
        groupedSettings.push({ ...group });
      }

      group = {
        name: setting.label,
        settings: [] as Setting[]
      }
      return;
    }

    group.settings.push(setting);
  });

  if (group.settings.length > 0) {
    groupedSettings.push({ ...group })
  }

  return groupedSettings;
}
