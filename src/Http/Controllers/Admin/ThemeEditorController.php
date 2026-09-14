<?php

namespace BagistoPlus\Visual\Http\Controllers\Admin;

use BagistoPlus\Visual\Facades\ThemePathsResolver;
use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\Http\Controllers\Controller;
use BagistoPlus\Visual\Persistence\CreateTemplate;
use BagistoPlus\Visual\Persistence\Data\EditorUpdateData;
use BagistoPlus\Visual\Persistence\Data\FullPageEditorData;
use BagistoPlus\Visual\Persistence\Data\ThemeSettingsUpdateData;
use BagistoPlus\Visual\Persistence\PersistEditorUpdates;
use BagistoPlus\Visual\Persistence\PersistSectionUpdates;
use BagistoPlus\Visual\Persistence\PersistThemeSettings;
use BagistoPlus\Visual\Persistence\PublishTheme;
use BagistoPlus\Visual\Persistence\RenderPreview;
use BagistoPlus\Visual\Persistence\SectionStore;
use BagistoPlus\Visual\Settings\Support\ImageTransformer;
use BagistoPlus\Visual\Settings\Support\VideoTransformer;
use BagistoPlus\Visual\Support\ChannelThemeResolver;
use BagistoPlus\Visual\Support\SchemaTextTranslator;
use BagistoPlus\Visual\Support\TemplateDiscovery;
use BagistoPlus\Visual\Theme\Theme;
use BagistoPlus\Visual\ThemeEditor;
use BagistoPlus\Visual\ThemeSettingsLoader;
use BladeUI\Icons\Factory;
use BladeUI\Icons\IconsManifest;
use Craftile\Laravel\Data\UpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Webkul\CMS\Models\Page;
use Webkul\CMS\Repositories\PageRepository;

class ThemeEditorController extends Controller
{
    public function __construct(
        protected PageRepository $pageRepository,
        protected PersistEditorUpdates $persistEditorUpdates,
        protected PersistThemeSettings $persistThemeSettings,
        protected PublishTheme $publishTheme,
        protected RenderPreview $renderPreview,
        protected ThemeSettingsLoader $themeSettingsLoader,
        protected TemplateDiscovery $templateDiscovery,
        protected CreateTemplate $createTemplate,
        protected ChannelThemeResolver $channelThemeResolver,
        protected PersistSectionUpdates $persistSectionUpdates,
        protected SectionStore $sectionStore,
    ) {}

    public function index(string $themeCode)
    {
        return view()->make('visual::admin.editor.index', [
            'config' => [
                'baseUrl' => parse_url(route('visual.admin.editor', ['theme' => $themeCode]), PHP_URL_PATH),
                'imagesBaseUrl' => Storage::disk(Visual::imagesDisk())->url(''),
                'videosBaseUrl' => Storage::disk(Visual::videosDisk())->url(''),
                'storefrontUrl' => url('/').'?'.http_build_query(['_designMode' => $themeCode]),
                'channels' => $this->getChannels(),
                'defaultChannel' => core()->getDefaultChannelCode(),
                'theme' => $this->loadTheme($themeCode),
                'templates' => $this->loadTemplates($themeCode),
                'routes' => [
                    'themesIndex' => route('visual.admin.themes.index'),
                    'persistUpdates' => route('visual.admin.editor.api.persist'),
                    'persistThemeSettings' => route('visual.admin.editor.api.persist_settings'),
                    'publishTheme' => route('visual.admin.editor.api.publish'),
                    'discardTheme' => route('visual.admin.editor.api.discard'),
                    'uploadSectionMedia' => route('visual.admin.editor.api.section_media'),
                    'listSectionMedia' => route('visual.admin.editor.api.section_media.list'),
                    'copySectionMedia' => route('visual.admin.editor.api.section_media.copy'),
                    'createTemplate' => route('visual.admin.editor.api.templates.create'),
                    'uploadImage' => route('visual.admin.editor.api.upload'),
                    'listImages' => route('visual.admin.editor.api.images'),
                    'uploadVideo' => route('visual.admin.editor.api.videos.upload'),
                    'listVideos' => route('visual.admin.editor.api.videos.index'),
                    'getCmsPages' => route('visual.admin.editor.api.cms_pages'),
                    'getIcons' => route('visual.admin.editor.api.icons'),
                ],
                'messages' => Lang::get('visual::theme-editor'),
                'editorLocale' => app()->getLocale(),
                'haveEdits' => $this->checkIfHaveEdits($themeCode),
                'draftCount' => $this->draftCount($themeCode),
            ],
        ]);
    }

    public function persistUpdates(Request $request)
    {
        $validated = $request->validate([
            'theme' => ['required', 'string', Rule::in($this->getEditableThemes())],
            'channel' => ['required', 'string', Rule::in($this->getChannelCodes())],
            'locale' => ['required', 'string', Rule::in($this->getLocaleCodes($request->input('channel')))],
            'template' => ['required', 'array'],
            'template.url' => ['required', 'string'],
            'template.name' => ['required', 'string'],
            'template.sources' => ['required', 'string'],
            'updates' => ['required', 'array'],
            'updates.changes' => ['required', 'array'],
            'updates.changes.added' => ['present', 'array'],
            'updates.changes.updated' => ['present', 'array'],
            'updates.changes.removed' => ['present', 'array'],
            'updates.changes.moved' => ['nullable', 'array'],
            'updates.blocks' => ['present', 'array'],
            'updates.regions' => ['present', 'array'],
        ]);

        if ($this->resolveTheme($validated['theme'])->editorMode()->isBagisto()) {
            return $this->persistSectionUpdatesAndRender($validated);
        }

        $data = EditorUpdateData::fromValidated($validated);
        $result = $this->persistEditorUpdates->handle($data);
        $loadedBlocks = $result['loadedBlocks'] ?? [];

        $allBlockIds = $this->buildRenderSet($data, $loadedBlocks);

        return $this->renderPreview->execute($data->templateUrl, $allBlockIds);
    }

    public function persistThemeSettings(Request $request)
    {
        $validated = $request->validate([
            'theme' => ['required', 'string', Rule::in($this->getEditableThemes())],
            'channel' => ['required', 'string', Rule::in($this->getChannelCodes())],
            'locale' => ['required', 'string', Rule::in($this->getLocaleCodes($request->input('channel')))],
            'template' => ['required', 'array'],
            'template.url' => ['required', 'string'],
            'updates' => ['required', 'array'],
        ]);

        $data = ThemeSettingsUpdateData::fromValidated($validated);
        $this->persistThemeSettings->handle($data);

        return $this->renderPreview->execute($data->templateUrl);
    }

    public function publishTheme(Request $request)
    {
        $validated = $request->validate([
            'theme' => ['required', 'string'],
            'channel' => ['required_with:page', 'nullable', 'string', Rule::in($this->getChannelCodes())],
            'locale' => ['required_with:page', 'nullable', 'string', Rule::in($this->getLocaleCodes($request->input('channel')))],
            'template' => ['required_with:page', 'nullable', 'string'],
            'page' => ['nullable', 'array'],
        ]);

        if ($this->resolveTheme($validated['theme'])->editorMode()->isBagisto()) {
            $this->sectionStore->publish(
                $this->channelId($validated['channel'] ?? core()->getDefaultChannelCode()),
                $validated['theme'],
            );

            return response()->json([
                'success' => true,
                'message' => 'Theme published successfully',
            ]);
        }

        // If page data is provided, persist it first before publishing
        if (isset($validated['page'])) {
            $this->persistEditorUpdates->handleFullPage(FullPageEditorData::fromValidated($validated));
        }

        $this->publishTheme->handle($validated['theme']);

        return response()->json([
            'success' => true,
            'message' => 'Theme published successfully',
        ]);
    }

    /**
     * Drop every unpublished change a theme's sections are holding.
     *
     * Offered instead of undo on a theme whose sections Bagisto stores: its
     * block ids are assigned by the row they are stored as and renumber as
     * entries are added and removed, so an undone step could land on a block
     * other than the one it was recorded against.
     */
    public function discardTheme(Request $request)
    {
        $validated = $request->validate([
            'theme' => ['required', 'string', Rule::in($this->getEditableThemes())],
            'channel' => ['nullable', 'string', Rule::in($this->getChannelCodes())],
        ]);

        abort_unless($this->resolveTheme($validated['theme'])->editorMode()->isBagisto(), 404);

        $this->sectionStore->discard(
            $this->channelId($validated['channel'] ?? core()->getDefaultChannelCode()),
            $validated['theme'],
        );

        return response()->json([
            'success' => true,
            'message' => 'Edits discarded successfully',
        ]);
    }

    /**
     * Keep a file among a section's own media, so that markup written into a
     * code field can point at something the storefront serves.
     *
     * Bagisto files these under the section row and deletes any it stops
     * naming, so the file has to be written into the field before the next save
     * or it is cleaned up again.
     */
    public function uploadSectionMedia(Request $request)
    {
        $validated = $request->validate([
            'theme' => ['required', 'string', Rule::in($this->getEditableThemes())],
            'section' => ['required', 'integer'],
            'file' => ['required', 'file', 'mimes:bmp,jpeg,jpg,png,webp,mp4,webm,ogg', 'max:51200'],
        ]);

        abort_unless($this->resolveTheme($validated['theme'])->editorMode()->isBagisto(), 404);
        abort_unless($this->sectionStore->themeOf($validated['section']) === $validated['theme'], 404);

        return response()->json(
            $this->sectionStore->storeMedia($validated['section'], $request->file('file'))
        );
    }

    /**
     * Every file the theme's sections hold, so that a section can be pointed at
     * one another section already uses.
     */
    public function listSectionMedia(Request $request)
    {
        $validated = $request->validate([
            'theme' => ['required', 'string', Rule::in($this->getEditableThemes())],
        ]);

        abort_unless($this->resolveTheme($validated['theme'])->editorMode()->isBagisto(), 404);

        return response()->json($this->sectionStore->media($validated['theme']));
    }

    /**
     * Keep a copy of another section's file among this one's own media.
     *
     * The file has to be copied rather than referenced: Bagisto deletes every
     * file a section's own options stop naming, so the section that owns it
     * would take it away on its next save.
     */
    public function copySectionMedia(Request $request)
    {
        $validated = $request->validate([
            'theme' => ['required', 'string', Rule::in($this->getEditableThemes())],
            'section' => ['required', 'integer'],
            'path' => ['required', 'string'],
        ]);

        abort_unless($this->resolveTheme($validated['theme'])->editorMode()->isBagisto(), 404);
        abort_unless($this->sectionStore->themeOf($validated['section']) === $validated['theme'], 404);

        $media = $this->sectionStore->copyMedia(
            $validated['section'],
            $validated['path'],
            $validated['theme']
        );

        abort_if(is_null($media), 404);

        return response()->json($media);
    }

    public function createTemplate(Request $request)
    {
        $validated = $request->validate([
            'theme' => ['required', 'string', Rule::in($this->getEditableThemes())],
            'channel' => ['required', 'string', Rule::in($this->getChannelCodes())],
            'locale' => ['required', 'string', Rule::in($this->getLocaleCodes($request->input('channel')))],
            'type' => ['required', 'string', Rule::in(TemplateDiscovery::ASSIGNABLE_TYPES)],
            'name' => ['required', 'string', 'max:25'],
            'basedOn' => ['nullable', 'string'],
        ]);

        $theme = Theme::make(config("themes.shop.{$validated['theme']}"));

        try {
            $template = ($this->createTemplate)(
                theme: $theme,
                channel: $validated['channel'],
                locale: $validated['locale'],
                type: $validated['type'],
                name: $validated['name'],
                basedOn: $validated['basedOn'] ?? null,
            );
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $templates = collect($this->loadTemplates($validated['theme']));
        $baseTemplate = $templates->firstWhere('template', $validated['type']);
        $createdTemplate = [
            'template' => $template['key'],
            'label' => $template['label'],
            'icon' => $baseTemplate['icon'] ?? '',
            'previewUrl' => $baseTemplate['previewUrl'] ?? '',
            'type' => $validated['type'],
            'supportsVariants' => false,
            'isJsonTemplate' => true,
        ];

        return response()->json([
            'template' => $template,
            'createdTemplate' => $createdTemplate,
            'templates' => $templates
                ->reject(fn ($existing) => $existing['template'] === $createdTemplate['template'])
                ->push($createdTemplate)
                ->values()
                ->all(),
        ]);
    }

    public function uploadImages(Request $request)
    {
        /** @var Collection<int, UploadedFile> $images */
        $images = collect($request->file('image'));

        return $images->map(function ($image) {
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $image->guessExtension();
            $storedName = bin2hex($originalName).'_'.uniqid().'.'.$extension;

            $path = $image->storeAs(
                Visual::imagesDirectory(),
                $storedName,
                Visual::imagesDisk(),
            );

            return [
                'path' => $path,
                'name' => $originalName,
                'url' => Storage::disk(Visual::imagesDisk())->url($path),
            ];
        });
    }

    public function listImages()
    {
        $diskName = Visual::imagesDisk();
        $files = Storage::disk($diskName)
            ->files(Visual::imagesDirectory());

        return collect($files)->map(function ($file) {
            $image = (new ImageTransformer)($file);

            return [
                'name' => $image->name,
                'path' => $image->path,
                'url' => $image->url,
            ];
        });
    }

    public function uploadVideos(Request $request)
    {
        $request->validate([
            'video' => ['required', 'array'],
            'video.*' => [
                'required',
                'file',
                'mimetypes:video/mp4,video/x-m4v,video/webm,video/ogg',
                'max:'.Visual::videosMaxUploadSize(),
            ],
        ]);

        /** @var Collection<int, UploadedFile> $videos */
        $videos = collect($request->file('video'));

        return $videos->map(function ($video) {
            $originalName = pathinfo($video->getClientOriginalName(), PATHINFO_FILENAME);
            $mimeType = $video->getMimeType();
            $extension = match ($mimeType) {
                'video/mp4', 'video/x-m4v' => 'mp4',
                'video/webm' => 'webm',
                'video/ogg' => 'ogg',
                default => $video->guessExtension(),
            };
            $storedName = bin2hex($originalName).'_'.uniqid().'.'.$extension;

            $path = $video->storeAs(
                Visual::videosDirectory(),
                $storedName,
                Visual::videosDisk(),
            );

            return [
                'path' => $path,
                'name' => $originalName,
                'url' => Storage::disk(Visual::videosDisk())->url($path),
                'mime_type' => $mimeType,
            ];
        });
    }

    public function listVideos()
    {
        $diskName = Visual::videosDisk();
        $files = Storage::disk($diskName)
            ->files(Visual::videosDirectory());

        return collect($files)->map(function ($file) {
            $video = (new VideoTransformer)->transform($file);

            return [
                'name' => $video->name,
                'path' => $video->path,
                'url' => $video->url,
                'mime_type' => $video->sources[0]['mime_type'] ?? null,
            ];
        });
    }

    public function cmsPages(Request $request)
    {
        $currentLocale = core()->getRequestedLocaleCode();

        return Page::query()
            ->select('cms_pages.id')
            ->join('cms_page_translations', function ($join) use ($currentLocale) {
                $join->on('cms_pages.id', '=', 'cms_page_translations.cms_page_id')
                    ->where('cms_page_translations.locale', '=', $currentLocale);
            })
            ->when($request->has('query'), function ($query) use ($request) {
                $query->where('cms_page_translations.page_title', $this->caseInsensitiveLike(), "%{$request->query('query')}%");
            })
            ->get();
    }

    public function icons(Request $request, Factory $factory, IconsManifest $iconsManifest)
    {
        $sets = $factory->all();
        $selectedSet = $request->input('set', 'lucide');

        if (isset($sets['default'])) {
            unset($sets['default']);
        }

        $set = $sets[$selectedSet];
        $icons = collect();

        foreach ($set['paths'] as $path) {
            if (! File::isDirectory(($path))) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if ($file->getExtension() !== 'svg') {
                    continue;
                }

                $name = $file->getFilenameWithoutExtension();

                $icons->push([
                    'name' => $name,
                    'id' => $set['prefix'].'-'.$name,
                    'svg' => File::get($file->getRealPath()),
                ]);
            }
        }

        return [
            'currentSet' => $selectedSet,
            'sets' => collect($sets)->map(fn ($set, $key) => ['id' => $key, 'prefix' => $set['prefix'], 'name' => Str::headline($key)])->values(),
            'icons' => $icons->values(),
        ];
    }

    /**
     * Bagisto 2.5 exposes db_grammar() so LIKE resolves to ILIKE on PostgreSQL.
     */
    protected function caseInsensitiveLike(): string
    {
        if (function_exists('db_grammar')) {
            return db_grammar()->caseInsensitiveLike();
        }

        return 'LIKE';
    }

    protected function loadTheme($themeCode)
    {
        $theme = $this->resolveTheme($themeCode);
        $settingsBag = $this->themeSettingsLoader->loadThemeSettings($theme);

        return [
            'name' => $theme->name,
            'code' => $theme->code,
            'version' => $theme->version ?? '1.0.0',
            'editorMode' => $theme->editorMode()->value,
            'settings' => $settingsBag->toArray(),
            'settingsSchema' => $this->translateSettingsSchema($theme->settingsSchema),
        ];
    }

    protected function loadTemplates(?string $themeCode = null)
    {
        $templates = collect(app(ThemeEditor::class)->getTemplates())
            ->map(fn ($template) => [
                'template' => $template->template,
                'label' => $template->label,
                'icon' => $template->icon,
                'previewUrl' => $template->resolvePreviewUrl(),
                'type' => $template->type,
                'supportsVariants' => $template->supportsVariants,
                'isJsonTemplate' => false,
            ])
            ->values();

        $themeCode ??= request('theme');
        $theme = $themeCode ? Theme::make(array_merge(['code' => $themeCode], config("themes.shop.{$themeCode}", []))) : null;

        if ($theme?->isVisualTheme()) {
            foreach (TemplateDiscovery::ASSIGNABLE_TYPES as $type) {
                $typeTemplates = $this->templateDiscovery
                    ->forType($theme, $type, core()->getDefaultChannelCode(), app()->getLocale(), true);

                $defaultTemplate = $typeTemplates->firstWhere('key', $type);
                $baseTemplate = $templates->firstWhere('template', $type);

                if ($defaultTemplate?->isJsonTemplate) {
                    $templates = $templates->map(
                        fn ($template) => $template['template'] === $type
                            ? array_merge($template, ['isJsonTemplate' => true])
                            : $template
                    );
                }

                $customTemplates = $typeTemplates
                    ->reject(fn ($template) => $template->key === $type)
                    ->map(fn ($template) => [
                        'template' => $template->key,
                        'label' => $template->label,
                        'icon' => $baseTemplate['icon'] ?? '',
                        'previewUrl' => $baseTemplate['previewUrl'] ?? '',
                        'type' => $type,
                        'supportsVariants' => false,
                        'isJsonTemplate' => $template->isJsonTemplate,
                    ]);

                $templates = $templates->merge($customTemplates);
            }
        }

        return $templates->values()->toArray();
    }

    protected function checkIfHaveEdits(string $themeCode): bool
    {
        if ($this->resolveTheme($themeCode)->editorMode()->isBagisto()) {
            return $this->sectionStore->hasDrafts(
                $this->channelId(core()->getRequestedChannelCode()),
                $themeCode,
            );
        }

        $lastEditFile = ThemePathsResolver::getThemeBaseDataPath($themeCode, 'editor/.last-edit');

        return file_exists($lastEditFile);
    }

    /**
     * How many sections are holding an unpublished change. Null for a theme
     * whose edits are held in files rather than rows, which has nothing to
     * count.
     */
    protected function draftCount(string $themeCode): ?int
    {
        if (! $this->resolveTheme($themeCode)->editorMode()->isBagisto()) {
            return null;
        }

        return $this->sectionStore->countDrafts(
            $this->channelId(core()->getRequestedChannelCode()),
            $themeCode,
        );
    }

    protected function translateSettingsSchema(array $settingsSchema): array
    {
        return app(SchemaTextTranslator::class)->translateThemeSettingsSchema($settingsSchema);
    }

    protected function getChannels()
    {
        return core()->getAllChannels()->map(fn ($channel) => [
            'code' => $channel->code,
            'name' => $channel->name,
            'locales' => $channel->locales,
            'default_locale' => $channel->default_locale->code,
        ]);
    }

    protected function getChannelCodes(): array
    {
        return $this->getChannels()
            ->map(fn ($channel) => $channel['code'])
            ->toArray();
    }

    protected function getLocaleCodes(string $channel): array
    {
        $channel = $this->getChannels()->firstWhere('code', $channel);

        if (! $channel) {
            return [];
        }

        return $channel['locales']->map(fn ($locale) => $locale['code'])->toArray();
    }

    /**
     * Hold what the editor changed about a theme's sections, and answer with
     * the preview as it now stands.
     *
     * @param  array<string, mixed>  $validated
     */
    protected function persistSectionUpdatesAndRender(array $validated)
    {
        $this->persistSectionUpdates->handle(
            UpdateRequest::make($validated['updates']),
            $this->channelId($validated['channel']),
            $validated['theme'],
            $validated['locale'],
        );

        return $this->renderPreview->execute($validated['template']['url']);
    }

    /**
     * The themes the visual editor can be opened on, which is every theme it
     * has a way of reading: a visual theme from its templates, and any other
     * from the sections it is stored as.
     *
     * @return array<int, string>
     */
    protected function getEditableThemes(): array
    {
        return collect(config('themes.shop', []))
            ->map(fn (array $attributes, $code) => Theme::make(['code' => $code] + $attributes))
            ->filter(fn (Theme $theme) => $theme->editorMode()->isEditable())
            ->map(fn (Theme $theme) => $theme->code)
            ->values()
            ->toArray();
    }

    /**
     * The theme a request names, however its config spells itself.
     */
    protected function resolveTheme(string $themeCode): Theme
    {
        $attributes = config("themes.shop.{$themeCode}");

        if (! $attributes) {
            abort(404, "Theme {$themeCode} not found");
        }

        return Theme::make(['code' => $themeCode] + $attributes);
    }

    /**
     * The row id of the channel a code names.
     */
    protected function channelId(string $code): int
    {
        return (int) core()->getAllChannels()->firstWhere('code', $code)?->id;
    }

    /**
     * Build complete render set: changed blocks + all their parents + all their children.
     */
    protected function buildRenderSet(EditorUpdateData $data, array $loadedBlocks): array
    {
        $addedIds = $data->addedBlockIds();
        $changedIds = $data->changedBlockIds();
        $renderSet = [];

        foreach ($changedIds as $id) {
            $renderSet[] = $id;

            // If block is in added and has a parent, include parent's children
            if (in_array($id, $addedIds) && isset($loadedBlocks[$id]['parentId']) && $loadedBlocks[$id]['parentId']) {
                $parentId = $loadedBlocks[$id]['parentId'];
                $this->addChildren($parentId, $loadedBlocks, $renderSet);
            }

            $currentId = $id;
            while (isset($loadedBlocks[$currentId]['parentId']) && $loadedBlocks[$currentId]['parentId']) {
                $parentId = $loadedBlocks[$currentId]['parentId'];
                $renderSet[] = $parentId;

                // If parent is a repeated block, include children of the repeated block's parent
                if (isset($loadedBlocks[$parentId]['repeated']) && $loadedBlocks[$parentId]['repeated']) {
                    if (isset($loadedBlocks[$parentId]['parentId']) && $loadedBlocks[$parentId]['parentId']) {
                        $this->addChildren($loadedBlocks[$parentId]['parentId'], $loadedBlocks, $renderSet);
                    }
                }

                $currentId = $parentId;
            }

            $this->addChildren($id, $loadedBlocks, $renderSet);
        }

        return array_unique($renderSet);
    }

    /**
     * Recursively add all children to the render set.
     */
    protected function addChildren(string $blockId, array $loadedBlocks, array &$renderSet): void
    {
        if (! isset($loadedBlocks[$blockId]['children'])) {
            return;
        }

        foreach ($loadedBlocks[$blockId]['children'] as $childId) {
            $renderSet[] = $childId;
            $this->addChildren($childId, $loadedBlocks, $renderSet);
        }
    }
}
