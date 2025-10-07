export default function useI18n() {
  const messages = window.editorConfig.messages || {};

  const t = (key: string) => {
    return messages[key] || key;
  };

  return { t };
}
