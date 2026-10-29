<?php

namespace BagistoPlus\Visual\Http\Controllers\Admin;

use BagistoPlus\Visual\Http\Controllers\Controller;
use BagistoPlus\Visual\Persistence\DeleteThemeCopy;
use BagistoPlus\Visual\Persistence\DuplicateTheme;
use BagistoPlus\Visual\Persistence\UpdateThemeCopy;
use BagistoPlus\Visual\Theme\Theme;
use BagistoPlus\Visual\Theme\ThemeCopies;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ThemesController extends Controller
{
    /**
     * Bagisto's catalog of themes, which knows the author, version and
     * description of the themes Bagisto ships. Added in 2.4.10.
     */
    protected const THEME_CATALOG = 'Webkul\Theme\ThemeCatalog';

    public function __construct(protected ThemeCopies $copies) {}

    public function index()
    {
        $catalog = $this->catalog();

        $themes = collect(config('themes.shop', []))
            ->map(fn (array $attributes, $code) => Theme::make(['code' => $code] + $attributes))
            ->filter(fn (Theme $theme) => $theme->editorMode()->isEditable())
            ->map(fn (Theme $theme) => $this->card($theme, $catalog->get($theme->code, [])))
            ->merge(collect($this->copies->unavailable())->map(fn (array $meta, string $code) => $this->unavailableCard($code, $meta)))
            ->values();

        return view()->make('visual::admin.themes.index', [
            'themes' => $themes,
            'can' => [
                'duplicate' => $this->allowedTo('bagisto_visual.themes.duplicate'),
                'edit' => $this->allowedTo('bagisto_visual.themes.edit'),
                'delete' => $this->allowedTo('bagisto_visual.themes.delete'),
            ],
        ]);
    }

    public function duplicate(Request $request, string $theme, DuplicateTheme $duplicateTheme): RedirectResponse
    {
        if (! Theme::forCode($theme)?->isVisualTheme()) {
            abort(404);
        }

        $data = $this->validateCopy($request);

        $duplicateTheme->handle($theme, $data['name'], $data['description'] ?? null);

        session()->flash('success', trans('visual::admin.themes.copy-created'));

        return redirect()->route('visual.admin.themes.index');
    }

    public function update(Request $request, string $theme, UpdateThemeCopy $updateThemeCopy): RedirectResponse
    {
        if (! $this->copies->isCopy($theme)) {
            abort(404);
        }

        $data = $this->validateCopy($request);

        $updateThemeCopy->handle($theme, $data['name'], $data['description'] ?? null);

        session()->flash('success', trans('visual::admin.themes.copy-updated'));

        return redirect()->route('visual.admin.themes.index');
    }

    public function destroy(string $theme, DeleteThemeCopy $deleteThemeCopy): RedirectResponse
    {
        if (! $this->copies->isCopy($theme)) {
            abort(404);
        }

        try {
            $deleteThemeCopy->handle($theme);
        } catch (DomainException $e) {
            session()->flash('error', $e->getMessage());

            return redirect()->route('visual.admin.themes.index');
        }

        session()->flash('success', trans('visual::admin.themes.copy-deleted'));

        return redirect()->route('visual.admin.themes.index');
    }

    protected function allowedTo(string $permission): bool
    {
        return (bool) app('Webkul\User\Bouncer')->hasPermission($permission);
    }

    /**
     * @return array{name: string, description?: ?string}
     */
    protected function validateCopy(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);
    }

    /**
     * What a theme's card shows: what its config declares, then what Bagisto's
     * catalog knows of it. The version is read from the config itself, since
     * the theme fills in a placeholder for one that was never declared.
     *
     * @param  array<string, mixed>  $entry
     * @return array<string, mixed>
     */
    protected function card(Theme $theme, array $entry): array
    {
        $config = config('themes.shop.'.$theme->code, []);

        $copy = $this->copies->all()[$theme->code] ?? null;

        return [
            'code' => $theme->code,
            'name' => $theme->name,
            'isVisual' => $theme->isVisualTheme(),
            'isCopy' => $copy !== null,
            'parentName' => $copy ? config("themes.shop.{$copy['parent']}.name", $copy['parent']) : null,
            'unavailable' => false,
            'author' => filled($theme->author) ? $theme->author : ($entry['author'] ?? null),
            'version' => $config['version'] ?? $entry['version'] ?? null,
            'description' => filled($theme->description) ? $theme->description : ($entry['description'] ?? null),
            'screenshot' => filled($theme->previewImage) ? asset($theme->previewImage) : ($entry['screenshot'] ?? null),
            'documentationUrl' => filled($theme->documentationUrl) ? $theme->documentationUrl : null,
        ];
    }

    /**
     * A copy whose parent is not registered, which can only be deleted.
     *
     * @param  array{parent: string, name: string, description?: ?string}  $meta
     * @return array<string, mixed>
     */
    protected function unavailableCard(string $code, array $meta): array
    {
        return [
            'code' => $code,
            'name' => $meta['name'],
            'isVisual' => true,
            'isCopy' => true,
            'parentName' => $meta['parent'],
            'unavailable' => true,
            'author' => null,
            'version' => null,
            'description' => $meta['description'] ?? null,
            'screenshot' => null,
            'documentationUrl' => null,
        ];
    }

    /**
     * Bagisto's catalog entries keyed by theme code, or none on a release
     * without a catalog.
     *
     * @return Collection<string, array<string, mixed>>
     */
    protected function catalog(): Collection
    {
        if (! class_exists(self::THEME_CATALOG)) {
            return collect();
        }

        return collect(app(self::THEME_CATALOG)->all())->keyBy('code');
    }
}
