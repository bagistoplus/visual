export const images = import.meta.glob(['../images/**'], {
  eager: true,
  query: '?url',
  import: 'default',
});
