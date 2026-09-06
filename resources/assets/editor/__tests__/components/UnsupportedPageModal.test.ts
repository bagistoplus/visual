import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import UnsupportedPageModal from '../../components/UnsupportedPageModal.vue';
import { createState } from '../../state';
import { TEMPLATES_DOCS_URL } from '../../constants/docs';

const { CRAFTILE_EDITOR, selectTemplate } = vi.hoisted(() => ({
  CRAFTILE_EDITOR: Symbol('CRAFTILE_EDITOR'),
  selectTemplate: vi.fn(() => true),
}));

vi.mock('../../craftile/plugin', () => ({
  CRAFTILE_EDITOR,
}));

vi.mock('../../composables/useTemplateSelection', async (importOriginal) => {
  const actual = await importOriginal<typeof import('../../composables/useTemplateSelection')>();

  return {
    ...actual,
    useTemplateSelection: () => ({ selectTemplate }),
  };
});

const templates = [
  { template: '__separator__', label: '', icon: '', previewUrl: '' },
  { template: 'cart', label: 'Cart', icon: '', previewUrl: 'https://example.test/cart' },
  { template: 'index', label: 'Home page', icon: '', previewUrl: 'https://example.test' },
];

function mountModal(reason: 'missing-template' | 'load-failed', availableTemplates = templates) {
  (window as any).editorConfig = { messages: {}, templates: availableTemplates };

  const state = createState({ templates: availableTemplates, unsupportedPage: reason });
  const editor = { ui: { closeModal: vi.fn() } };

  const wrapper = mount(UnsupportedPageModal, {
    global: {
      provide: { [CRAFTILE_EDITOR]: editor },
      stubs: { Button: { template: '<button type="button" :disabled="$attrs.disabled"><slot /></button>' } },
    },
  });

  return { wrapper, state, editor };
}

describe('UnsupportedPageModal', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('shows the missing template explanation', () => {
    const { wrapper } = mountModal('missing-template');

    expect(wrapper.text()).toContain('unsupported_page_missing_template');
    expect(wrapper.text()).toContain('unsupported_page_hint');
    expect(wrapper.text()).not.toContain('unsupported_page_load_failed');
  });

  it('shows the load failure explanation', () => {
    const { wrapper } = mountModal('load-failed');

    expect(wrapper.text()).toContain('unsupported_page_load_failed');
    expect(wrapper.text()).toContain('unsupported_page_hint');
    expect(wrapper.text()).not.toContain('unsupported_page_missing_template');
  });

  it('opens the templates documentation in a new tab', async () => {
    const open = vi.spyOn(window, 'open').mockImplementation(() => null);
    const { wrapper } = mountModal('missing-template');

    await wrapper.findAll('button')[0].trigger('click');

    expect(open).toHaveBeenCalledWith(TEMPLATES_DOCS_URL, '_blank');
    open.mockRestore();
  });

  it('selects the index template and closes the modal', async () => {
    const { wrapper, editor } = mountModal('missing-template');

    await wrapper.findAll('button')[1].trigger('click');

    expect(selectTemplate).toHaveBeenCalledWith(expect.objectContaining({ template: 'index' }));
    expect(editor.ui.closeModal).toHaveBeenCalledWith('unsupported-page');
  });

  it('falls back to the first real template when index is missing', async () => {
    const { wrapper } = mountModal('missing-template', templates.filter((t) => t.template !== 'index'));

    await wrapper.findAll('button')[1].trigger('click');

    expect(selectTemplate).toHaveBeenCalledWith(expect.objectContaining({ template: 'cart' }));
  });
});
