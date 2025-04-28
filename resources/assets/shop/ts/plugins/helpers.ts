import type { Alpine as AlpineType } from 'alpinejs';

type HttpMethod = 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';

interface RequestOptions extends RequestInit {
  headers?: Record<string, string>;
}

type DataPayload = Record<string, any> | null;

interface CurrencyData {
  symbol?: string;
  code?: string;
  currency_position?: 'left' | 'left_with_space' | 'right' | 'right_with_space';
  decimal?: number;
  group_separator?: string;
  decimal_separator?: string;
}

export type RequestFn = (
  url: string,
  method?: HttpMethod,
  data?: DataPayload,
  customOptions?: RequestOptions
) => Promise<any>;

/**
 * Get the browser's language and format it for Intl compatibility.
 */
function getLocale(): string {
  const rawLocale = document.documentElement.getAttribute('lang') || 'en-US';
  return rawLocale === 'ar'
    ? 'ar-SA'
    : rawLocale.replace(/([a-z]{2})_([A-Z]{2})/g, '$1-$2');
}

/**
 * Safely parse currency data from the DOM.
 */
function getCurrencyData(): CurrencyData {
  try {
    const el = document.getElementById('currency-data');
    return JSON.parse(el?.textContent || '{}');
  } catch (e) {
    console.warn('Invalid currency data:', e);
    return {};
  }
}

/**
 * Convert an object to a query string.
 * This function handles nested objects and arrays, encoding them properly for use in URLs.
 * It also skips null and undefined values.
 */
function toQueryString(obj: Record<string, any>, prefix = ''): string {
  const query = Object.entries(obj).flatMap(([key, value]) => {
    const fullKey = prefix ? `${prefix}[${key}]` : key;

    if (value === null || value === undefined) {
      return [];
    }

    if (Array.isArray(value)) {
      return value.flatMap((val, index) => {
        const arrayKey = `${fullKey}[${index}]`;

        if (typeof val === 'object' && val !== null) {
          return toQueryString(val, arrayKey);
        }

        return `${encodeURIComponent(arrayKey)}=${encodeURIComponent(val)}`;
      });
    }

    if (typeof value === 'object') {
      return toQueryString(value, fullKey);
    }

    return `${encodeURIComponent(fullKey)}=${encodeURIComponent(value)}`;
  });

  return query.join('&');
}

export default function (Alpine: AlpineType) {
  /**
   * Format a number as a price string.
   *
   * usage: `x-text="$formatPrice(1234.56)"` or `x-bind:value="$formatPrice(1234.56)"`
   */
  Alpine.magic('formatPrice', () => (value: number) => {
    const locale = getLocale();
    const currency = getCurrencyData();

    const currencyCode = currency.code || 'USD';
    const symbol = currency.symbol || currencyCode;

    const formatter = new Intl.NumberFormat(locale, {
      style: 'currency',
      currency: currencyCode,
      minimumFractionDigits: currency.decimal ?? 2,
    });

    const formattedCurrency = formatter
      .formatToParts(value)
      .map((part) => {
        switch (part.type) {
          case 'currency':
            return ''; // We'll handle the symbol ourselves.
          case 'group':
            return currency.group_separator || part.value;
          case 'decimal':
            return currency.decimal_separator || part.value;
          default:
            return part.value;
        }
      })
      .join('');

    switch (currency.currency_position) {
      case 'left':
        return `${symbol}${formattedCurrency}`;
      case 'left_with_space':
        return `${symbol} ${formattedCurrency}`;
      case 'right':
        return `${formattedCurrency}${symbol}`;
      case 'right_with_space':
        return `${formattedCurrency} ${symbol}`;
      default:
        return formattedCurrency;
    }
  });

  Alpine.magic('request', (): RequestFn => {
    return async (
      url,
      method = 'GET',
      data = null,
      customOptions = {}
    ): Promise<any> => {
      const options: RequestOptions = {
        method: method.toUpperCase() as HttpMethod,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN':
            document
              .querySelector('meta[name="csrf-token"]')
              ?.getAttribute('content') || '',
        },
        ...customOptions,
      };

      if (method.toUpperCase() === 'GET' && data) {
        const params = toQueryString(data);
        const separator = url.includes('?') ? '&' : '?';
        url += separator + params;
      } else if (data) {
        options.headers = {
          ...options.headers,
        };

        if (data instanceof FormData) {
          options.body = data;
        } else {
          options.headers['Accept'] = 'application/json';
          options.headers['Content-Type'] = 'application/json';
          options.body = JSON.stringify(data);
        }
      }

      try {
        const response = await fetch(url, options);

        if (!response.ok) {
          try {
            const errorData = await response.json();
            throw errorData;
          } catch {
            throw new Error(
              `Request failed with status ${response.status}: ${response.statusText}`
            );
          }
        }

        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
          return await response.json();
        } else {
          return await response.text();
        }
      } catch (error) {
        console.error('Request error:', error);
        throw error;
      }
    };
  });
}
