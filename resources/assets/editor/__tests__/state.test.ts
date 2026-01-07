import { describe, it, expect, beforeEach, vi } from 'vitest';
import type { Category, Product, CmsPage } from '../types';

let createState: any;
let useState: any;
let populatePreloadedModels: any;

describe('state management', () => {
  beforeEach(async () => {
    vi.resetModules();
    const stateModule = await import('../state');
    createState = stateModule.createState;
    useState = stateModule.useState;
    populatePreloadedModels = stateModule.populatePreloadedModels;
  });

  describe('createState', () => {
    it('should create state with default values', () => {
      const state = createState();

      expect(state.channels).toEqual([]);
      expect(state.channel).toBe('default');
      expect(state.locale).toBe('en');
      expect(state.theme).toBeNull();
      expect(state.templates).toEqual([]);
      expect(state.pageData).toBeNull();
      expect(state.images).toEqual([]);
      expect(state.categories).toBeInstanceOf(Map);
      expect(state.products).toBeInstanceOf(Map);
      expect(state.cmsPages).toBeInstanceOf(Map);
      expect(state.haveEdits).toBe(false);
    });

    it('should create state with custom defaults', () => {
      const customDefaults = {
        haveEdits: true,
        templates: [{ template: 'home', name: 'Home' }] as any,
      };

      const state = createState(customDefaults);

      expect(state.haveEdits).toBe(true);
      expect(state.templates).toHaveLength(1);
      expect(state.channel).toBe('default');
      expect(state.locale).toBe('en');
    });

    it('should initialize Maps correctly', () => {
      const categories = new Map<number, Category>();
      categories.set(1, { id: 1, name: 'Test', slug: 'test' } as Category);

      const state = createState({ categories });

      expect(state.categories.get(1)).toEqual({ id: 1, name: 'Test', slug: 'test' });
    });
  });

  describe('useState', () => {
    it('should throw error if state not initialized', () => {
      expect(() => useState()).toThrow('State not initialized');
    });

    it('should return state after initialization', () => {
      createState();
      const { state } = useState();

      expect(state).toBeDefined();
      expect(state.channel).toBe('default');
    });
  });

  describe('getCategory', () => {
    it('should return category without translations', () => {
      const state = createState();
      state.categories.set(1, {
        id: 1,
        name: 'Electronics',
        slug: 'electronics',
      } as Category);

      const { getCategory } = useState();
      const category = getCategory(1);

      expect(category).toEqual({
        id: 1,
        name: 'Electronics',
        slug: 'electronics',
      });
    });

    it('should return category with translated name and slug', () => {
      const state = createState();
      state.locale = 'fr';
      state.categories.set(1, {
        id: 1,
        name: 'Electronics',
        slug: 'electronics',
        translations: [
          { locale: 'en', name: 'Electronics', slug: 'electronics' },
          { locale: 'fr', name: 'Électronique', slug: 'electronique' },
        ],
      } as any);

      const { getCategory } = useState();
      const category = getCategory(1);

      expect(category?.name).toBe('Électronique');
      expect(category?.slug).toBe('electronique');
    });

    it('should return undefined for non-existent category', () => {
      createState();
      const { getCategory } = useState();

      expect(getCategory(999)).toBeUndefined();
    });

    it('should fallback to default if translation not found', () => {
      const state = createState({ locale: 'de' });
      state.categories.set(1, {
        id: 1,
        name: 'Electronics',
        slug: 'electronics',
        translations: [
          { locale: 'en', name: 'Electronics', slug: 'electronics' },
        ],
      } as any);

      const { getCategory } = useState();
      const category = getCategory(1);

      expect(category?.name).toBe('Electronics');
      expect(category?.slug).toBe('electronics');
    });
  });

  describe('getCategories', () => {
    it('should return all categories', () => {
      const state = createState();
      state.categories.set(1, { id: 1, name: 'Cat1', slug: 'cat1' } as Category);
      state.categories.set(2, { id: 2, name: 'Cat2', slug: 'cat2' } as Category);

      const { getCategories } = useState();
      const categories = getCategories();

      expect(categories).toHaveLength(2);
      expect(categories[0].id).toBe(1);
      expect(categories[1].id).toBe(2);
    });

    it('should translate all categories', () => {
      const state = createState();
      state.locale = 'fr';
      state.categories.set(1, {
        id: 1,
        name: 'Electronics',
        slug: 'electronics',
        translations: [
          { locale: 'fr', name: 'Électronique', slug: 'electronique' },
        ],
      } as any);
      state.categories.set(2, {
        id: 2,
        name: 'Clothing',
        slug: 'clothing',
        translations: [
          { locale: 'fr', name: 'Vêtements', slug: 'vetements' },
        ],
      } as any);

      const { getCategories } = useState();
      const categories = getCategories();

      expect(categories[0].name).toBe('Électronique');
      expect(categories[1].name).toBe('Vêtements');
    });

    it('should return empty array when no categories', () => {
      createState();
      const { getCategories } = useState();

      expect(getCategories()).toEqual([]);
    });
  });

  describe('getProduct', () => {
    it('should return product by id', () => {
      const state = createState();
      state.products.set(1, { id: 1, name: 'Product 1' } as Product);

      const { getProduct } = useState();
      const product = getProduct(1);

      expect(product).toEqual({ id: 1, name: 'Product 1' });
    });

    it('should return undefined for non-existent product', () => {
      createState();
      const { getProduct } = useState();

      expect(getProduct(999)).toBeUndefined();
    });
  });

  describe('getProducts', () => {
    it('should return all products', () => {
      const state = createState();
      state.products.set(1, { id: 1, name: 'Product 1' } as Product);
      state.products.set(2, { id: 2, name: 'Product 2' } as Product);

      const { getProducts } = useState();
      const products = getProducts();

      expect(products).toHaveLength(2);
    });

    it('should return empty array when no products', () => {
      createState();
      const { getProducts } = useState();

      expect(getProducts()).toEqual([]);
    });
  });

  describe('getCmsPage', () => {
    it('should return CMS page without translations', () => {
      const state = createState();
      state.cmsPages.set(1, {
        id: 1,
        url_key: 'about',
        page_title: 'About Us',
      } as CmsPage);

      const { getCmsPage } = useState();
      const page = getCmsPage(1);

      expect(page).toEqual({
        id: 1,
        url_key: 'about',
        page_title: 'About Us',
      });
    });

    it('should return CMS page with translated fields', () => {
      const state = createState();
      state.locale = 'fr';
      state.cmsPages.set(1, {
        id: 1,
        url_key: 'about',
        page_title: 'About Us',
        translations: [
          { locale: 'en', url_key: 'about', page_title: 'About Us' },
          { locale: 'fr', url_key: 'a-propos', page_title: 'À propos' },
        ],
      } as any);

      const { getCmsPage } = useState();
      const page = getCmsPage(1);

      expect(page?.url_key).toBe('a-propos');
      expect(page?.page_title).toBe('À propos');
    });

    it('should return undefined for non-existent page', () => {
      createState();
      const { getCmsPage } = useState();

      expect(getCmsPage(999)).toBeUndefined();
    });
  });

  describe('getCmsPages', () => {
    it('should return all CMS pages', () => {
      const state = createState();
      state.cmsPages.set(1, { id: 1, url_key: 'about', page_title: 'About' } as CmsPage);
      state.cmsPages.set(2, { id: 2, url_key: 'contact', page_title: 'Contact' } as CmsPage);

      const { getCmsPages } = useState();
      const pages = getCmsPages();

      expect(pages).toHaveLength(2);
    });

    it('should translate all CMS pages', () => {
      const state = createState();
      state.locale = 'fr';
      state.cmsPages.set(1, {
        id: 1,
        url_key: 'about',
        page_title: 'About',
        translations: [
          { locale: 'fr', url_key: 'a-propos', page_title: 'À propos' },
        ],
      } as any);

      const { getCmsPages } = useState();
      const pages = getCmsPages();

      expect(pages[0].url_key).toBe('a-propos');
      expect(pages[0].page_title).toBe('À propos');
    });

    it('should return empty array when no pages', () => {
      createState();
      const { getCmsPages } = useState();

      expect(getCmsPages()).toEqual([]);
    });
  });

  describe('currentTemplate', () => {
    it('should return null when no pageData', () => {
      createState();
      const { currentTemplate } = useState();

      expect(currentTemplate.value).toBeNull();
    });

    it('should return current template from pageData', () => {
      const state = createState({
        templates: [
          { template: 'home', name: 'Home Page' },
          { template: 'products', name: 'Products Page' },
        ] as any,
      });
      state.pageData = {
        url: '/home',
        template: 'home',
        sources: 'source',
      };

      const { currentTemplate } = useState();

      expect(currentTemplate.value).toEqual({
        template: 'home',
        name: 'Home Page',
      });
    });

    it('should return null when template not found', () => {
      const state = createState({
        templates: [
          { template: 'home', name: 'Home Page' },
        ] as any,
      });
      state.pageData = {
        url: '/products',
        template: 'products',
        sources: 'source',
      };

      const { currentTemplate } = useState();

      expect(currentTemplate.value).toBeNull();
    });
  });

  describe('populatePreloadedModels', () => {
    it('should populate categories', () => {
      const state = createState();
      const categories = [
        { id: 1, name: 'Cat1', slug: 'cat1' },
        { id: 2, name: 'Cat2', slug: 'cat2' },
      ] as Category[];

      populatePreloadedModels({ categories });

      expect(state.categories.size).toBe(2);
      expect(state.categories.get(1)?.name).toBe('Cat1');
      expect(state.categories.get(2)?.name).toBe('Cat2');
    });

    it('should populate products', () => {
      const state = createState();
      const products = [
        { id: 1, name: 'Product 1' },
        { id: 2, name: 'Product 2' },
      ] as Product[];

      populatePreloadedModels({ products });

      expect(state.products.size).toBe(2);
      expect(state.products.get(1)?.name).toBe('Product 1');
    });

    it('should populate CMS pages', () => {
      const state = createState();
      const cms_pages = [
        { id: 1, url_key: 'about', page_title: 'About' },
        { id: 2, url_key: 'contact', page_title: 'Contact' },
      ] as CmsPage[];

      populatePreloadedModels({ cms_pages });

      expect(state.cmsPages.size).toBe(2);
      expect(state.cmsPages.get(1)?.url_key).toBe('about');
    });

    it('should populate all models at once', () => {
      const state = createState();
      const preloadedModels = {
        categories: [{ id: 1, name: 'Cat1', slug: 'cat1' }] as Category[],
        products: [{ id: 1, name: 'Product 1' }] as Product[],
        cms_pages: [{ id: 1, url_key: 'about', page_title: 'About' }] as CmsPage[],
      };

      populatePreloadedModels(preloadedModels);

      expect(state.categories.size).toBe(1);
      expect(state.products.size).toBe(1);
      expect(state.cmsPages.size).toBe(1);
    });

    it('should update existing models with same id', () => {
      const state = createState();
      state.categories.set(1, { id: 1, name: 'Old Name', slug: 'old' } as Category);

      populatePreloadedModels({
        categories: [{ id: 1, name: 'New Name', slug: 'new' }] as Category[],
      });

      expect(state.categories.get(1)?.name).toBe('New Name');
      expect(state.categories.get(1)?.slug).toBe('new');
    });

    it('should handle empty preloaded models', () => {
      const state = createState();

      populatePreloadedModels({});

      expect(state.categories.size).toBe(0);
      expect(state.products.size).toBe(0);
      expect(state.cmsPages.size).toBe(0);
    });
  });
});
