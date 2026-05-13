@pushOnce('scripts')
  <script>
    (() => {
      const template = document.getElementById('v-datagrid-table-template');

      if (!template || template.innerHTML.includes(':title="action.title"')) {
        return;
      }

      template.innerHTML = template.innerHTML.replace(
        'v-for="action in record.actions"',
        'v-for="action in record.actions" v-show="action.url" :title="action.title" :aria-label="action.title"'
      );
    })
    ();
  </script>
@endPushOnce
