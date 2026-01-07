import type { PluginContext } from '@craftile/editor';

import HeroiconsCog6Tooth from '~icons/heroicons/cog-6-tooth';
import HeroiconsPhoto from '~icons/heroicons/photo';
import HeaderTitle from '../../components/HeaderTitle.vue';
import HeaderTools from '../../components/HeaderTools.vue';
import ThemeSettingsPanel from '../../components/ThemeSettingsPanel.vue';
import MediaPanel from '../../components/MediaPanel.vue';
import CategoryPicker from '../../components/CategoryPicker.vue';
import ProductPicker from '../../components/ProductPicker.vue';
import CmsPagePicker from '../../components/CmsPagePicker.vue';
import FontPicker from '../../components/FontPicker.vue';
import LinkPicker from '../../components/LinkPicker.vue';
import ColorSchemePicker from '../../components/ColorSchemePicker.vue';
import ColorSchemeGroup from '../../components/ColorSchemeGroup.vue';
import IconPicker from '../../components/IconPicker.vue';
import ImagePicker from '../../components/ImagePicker.vue';
import RichtextEditor from '../../components/RichtextEditor.vue';
import GradientPicker from '../../components/GradientPicker.vue';
import SpacingField from '../../components/SpacingField.vue';
import PublishAction from '../../components/PublishAction.vue';
import PreviewAction from '../../components/PreviewAction.vue';
import ConfirmPublish from '../../components/ConfirmPublish.vue';
import BackButton from '../../components/BackButton.vue';
import useI18n from '../../composables/i18n';

const { t } = useI18n();

function configureHeader(ui: PluginContext['editor']['ui']) {
  ui.removeHeaderAction('back-button');
  ui.removeHeaderAction('title');

  ui.registerHeaderAction({
    id: 'back-button',
    slot: 'left',
    render: BackButton,
  });

  ui.registerHeaderAction({
    id: 'title',
    slot: 'left',
    render: HeaderTitle,
  });

  ui.registerHeaderAction({
    id: 'tools',
    slot: 'center',
    render: HeaderTools,
  });

  ui.registerHeaderAction({
    id: 'preview',
    slot: 'right',
    render: PreviewAction,
  });

  ui.registerHeaderAction({
    id: 'publish',
    slot: 'right',
    render: PublishAction,
  });

  ui.registerSidebarPanel({
    id: 'theme-settings',
    title: 'Theme settings',
    icon: HeroiconsCog6Tooth,
    render: ThemeSettingsPanel,
  });

  ui.registerSidebarPanel({
    id: 'media',
    title: 'Medias',
    icon: HeroiconsPhoto,
    render: MediaPanel,
  });

  ui.registerModal({
    id: 'confirm-publish',
    title: t('Publish edits ?'),
    size: 'lg',
    render: ConfirmPublish,
  });
}

function registerPropertyFields(ui: PluginContext['editor']['ui']) {
  ui.registerPropertyField({
    type: 'category',
    render: CategoryPicker,
  });

  ui.registerPropertyField({
    type: 'product',
    render: ProductPicker,
  });

  ui.registerPropertyField({
    type: 'cms-page',
    render: CmsPagePicker,
  });

  ui.registerPropertyField({
    type: 'font',
    render: FontPicker,
  });

  ui.registerPropertyField({
    type: 'link',
    render: LinkPicker,
  });

  ui.registerPropertyField({
    type: 'color-scheme',
    render: ColorSchemePicker,
  });

  ui.registerPropertyField({
    type: 'color-scheme-group',
    render: ColorSchemeGroup,
  });

  ui.registerPropertyField({
    type: 'icon',
    render: IconPicker,
  });

  ui.registerPropertyField({
    type: 'image',
    render: ImagePicker,
  });

  ui.registerPropertyField({
    type: 'richtext',
    render: RichtextEditor,
  });

  ui.registerPropertyField({
    type: 'gradient',
    render: GradientPicker,
  });

  ui.registerPropertyField({
    type: 'spacing',
    render: SpacingField,
  });
}

export function configureUI(ui: PluginContext['editor']['ui']) {
  configureHeader(ui);
  registerPropertyFields(ui);
}
