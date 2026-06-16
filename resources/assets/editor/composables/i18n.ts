export default function useI18n() {
  const messages = window.editorConfig.messages || {};

  const t = (key: string, replacements: Record<string, string | number> = {}) => {
    let message = typeof messages[key] === 'string' ? messages[key] : key;

    Object.entries(replacements).forEach(([name, value]) => {
      message = message.split(`:${name}`).join(String(value));
    });

    return message;
  };

  return { t };
}
