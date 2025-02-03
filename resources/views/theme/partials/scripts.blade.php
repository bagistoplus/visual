<script>
  document.addEventListener('alpine:init', () => {
    Alpine.magic('formatPrice', () => (value) => {
      let locale = document.documentElement.getAttribute("lang");
      const currency = JSON.parse(document.getElementById('currency-data')?.textContent || '{}');
      const symbol = currency.symbol !== '' ? currency.symbol : currency.code;

      locale = locale === 'ar' ? 'ar-SA' : locale.replace(/([a-z]{2})_([A-Z]{2})/g, '$1-$2');

      if (!currency.code || !currency.currency_position) {
        return new Intl.NumberFormat(locale, {
          style: 'currency',
          currency: currency.code,
        }).format(value);
      }

      const formatter = new Intl.NumberFormat(locale, {
        style: 'currency',
        currency: currency.code,
        minimumFractionDigits: currency.decimal ?? 2, // Use currency.decimal or default to 2.
      });

      const formattedCurrency = formatter.formatToParts(price)
        .map(part => {
          if (part.type === 'currency') {
            // Remove the built-in currency part since we'll add it later.
            return '';
          } else if (part.type === 'group') {
            return currency.group_separator || part.value;
          } else if (part.type === 'decimal') {
            return currency.decimal_separator || part.value;
          }
          return part.value;
        })
        .join('');

      switch (currency.currency_position) {
        case 'left':
          return symbol + formattedCurrency;
        case 'left_with_space':
          return symbol + ' ' + formattedCurrency;
        case 'right':
          return formattedCurrency + symbol;
        case 'right_with_space':
          return formattedCurrency + ' ' + symbol;
        default:
          // Fallback: just return the formatted currency without any extra symbol manipulation.
          return formattedCurrency;
      }
    });
  });
</script>
@stack('scripts')
