<?php

namespace BagistoPlus\Visual\Sections;

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
}
