<?php

namespace BagistoPlus\Visual\Sections;

use BagistoPlus\Visual\Sections\Settings\Checkbox;
use BagistoPlus\Visual\Sections\Settings\Link;
use BagistoPlus\Visual\Sections\Settings\Text;
use BagistoPlus\Visual\Sections\Settings\Textarea;

class Footer extends BladeSection
{
    protected static string $view = 'shop::sections.footer';

    protected static string $schema = __DIR__.'/../../resources/schemas/footer.json';

    public function getLinks()
    {
        $blocks = collect($this->section->blocks);

        if ($blocks->filter(fn ($b) => $b->type === 'link')->isEmpty()) {
            return $this->getDefaultLinks();
        }

        return $blocks->reduce(function ($carry, $block) {
            if ($block->type === 'group') {
                $carry[] = [
                    'group' => $block->settings->title,
                    'links' => [],
                ];
            } elseif ($block->type === 'link') {
                $lastGroupIndex = count($carry) - 1;
                $carry[$lastGroupIndex]['links'][] = [
                    'text' => $block->settings->text,
                    'url' => $block->settings->link,
                ];
            }

            return $carry;
        }, []);
    }

    protected function getDefaultLinks()
    {
        return [
            [
                'group' => 'Company',
                'links' => [
                    ['text' => 'About Us', 'url' => route('shop.cms.page', 'about-us')],
                    ['text' => 'Contact Us', 'url' => route('shop.home.contact_us')],
                    ['text' => 'About Us', 'url' => route('shop.cms.page', 'customer-service')],
                ],
            ],
            [
                'group' => 'Policy',
                'links' => [
                    ['text' => 'Privacy Policy', 'url' => route('shop.cms.page', 'privacy-policy')],
                    ['text' => 'Payment Policy', 'url' => route('shop.cms.page', 'payment-policy')],
                    ['text' => 'Shipping Policy', 'url' => route('shop.cms.page', 'shipping-policy')],
                ],
            ],
            [
                'group' => 'Account',
                'links' => [
                    ['text' => 'Sign In', 'url' => route('shop.customer.session.index')],
                    ['text' => 'Create Account', 'url' => route('shop.customers.register.index')],
                    ['text' => 'Forget Password', 'url' => route('shop.customers.forgot_password.create')],
                ],
            ],
        ];
    }

    public static function settings(): array
    {
        return [
            Text::make('heading', __('visual::sections.footer.settings.heading_label'))
                ->default(__('visual::sections.footer.settings.heading_default')),

            Textarea::make('description', __('visual::sections.footer.settings.description_label'))
                ->default(__('visual::sections.footer.settings.description_default')),

            Checkbox::make('show_social_links', __('visual::sections.footer.settings.show_social_links_label'))
                ->default(true)
                ->info(__('visual::sections.footer.settings.show_social_links_info')),
        ];
    }

    public static function blocks(): array
    {
        return [
            Block::make('group', __('visual::sections.footer.blocks.group.name'))
                ->settings([
                    Text::make('title', __('visual::sections.footer.blocks.group.settings.title_label'))
                        ->default(__('visual::sections.footer.blocks.group.settings.title_default')),
                ]),

            Block::make('link', __('visual::sections.footer.blocks.link.name'))
                ->settings([
                    Text::make('text', 'Text')
                        ->default('Link text'),

                    Link::make('link', __('visual::sections.footer.blocks.link.settings.link_label'))
                        ->default('/'),
                ]),
        ];
    }
}
