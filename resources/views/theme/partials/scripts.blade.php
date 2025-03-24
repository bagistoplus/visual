<script>
  document.addEventListener('alpine:init', () => {
    Alpine.magic('toast', (el) => (options) => {
      window.dispatchEvent(
        new CustomEvent('show-toast', {
          detail: options,
        })
      );
    });

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

    // TODO: use qs instead
    const objectToQueryString = (obj, prefix = '') => {
      const params = new URLSearchParams();

      Object.keys(obj).forEach(key => {
        const value = obj[key];
        const paramName = prefix ? `${prefix}[${key}]` : key;

        if (Array.isArray(value)) {
          value.forEach((item, index) => {
            if (item !== null && typeof item === 'object') {
              const nestedParams = objectToQueryString(item, `${paramName}[${index}]`);
              for (const [nestedKey, nestedValue] of nestedParams.entries()) {
                params.append(nestedKey, nestedValue);
              }
            } else {
              params.append(`${paramName}[${index}]`, item);
            }
          });
        } else if (value !== null && typeof value === 'object') {
          const nestedParams = objectToQueryString(value, paramName);
          for (const [nestedKey, nestedValue] of nestedParams.entries()) {
            params.append(nestedKey, nestedValue);
          }
        } else {
          params.append(paramName, value);
        }
      });

      return params;
    };

    Alpine.magic('request', (el) => (url, method = 'GET', data = null, customOptions = {}) => {
      const options = {
        method: method.toUpperCase(),
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        },
        ...customOptions
      };

      // Handle GET requests by appending data to URL as query parameters
      if (method.toUpperCase() === 'GET' && data) {
        const params = objectToQueryString(data);
        const separator = url.includes('?') ? '&' : '?';
        url = url + separator + params.toString();
      } else if (data) {
        // For non-GET requests with data, add JSON content type and stringify body
        options.headers['Content-Type'] = 'application/json';
        options.body = JSON.stringify(data);
      }

      return fetch(url, options)
        .then(response => {
          if (!response.ok) {
            // Try to parse error response as JSON
            return response.json()
              .then(errorData => {
                throw errorData;
              })
              .catch(error => {
                throw new Error(`Request failed with status ${response.status}: ${response.statusText}`);
              })
          }

          // Check content type to determine how to parse response
          const contentType = response.headers.get('content-type');

          if (contentType && contentType.includes('application/json')) {
            return response.json();
          } else {
            return response.text();
          }
        })
        .catch(error => {
          console.error('Request error:', error);
          throw error;
        });
    });
  });
</script>
@stack('scripts')
