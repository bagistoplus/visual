<?php

return [
    'announcement-bar' => [
        'name' => 'Announcement Bar',
        'description' => 'A simple announcement bar to display important information to your customers.',
        'settings' => [
            'text_label' => 'Announcement Text',
            'default_text' => 'Free shipping on all orders over $50',
            'link_label' => 'Link',
            'background_color_label' => 'Background Color',
            'text_color_label' => 'Text Color',
        ],
    ],
    'header' => [
        'name' => 'Header',
        'description' => '',
        'blocks' => [
            'logo' => [
                'name' => 'Name/Logo',
                'settings' => [
                    'logo_image_label' => 'Upload Logo',
                    'logo_text_label' => 'Logo Text',
                ],
            ],
            'nav' => [
                'name' => 'Navigation',
            ],
            'currency' => [
                'name' => 'Currency selector',
            ],
            'locale' => [
                'name' => 'Language selector',
            ],
            'search' => [
                'name' => 'Search form',
            ],
            'user' => [
                'name' => 'User menu',
            ],
            'cart' => [
                'name' => 'Cart preview',
            ],
        ],
    ],
    'footer' => [
        'name' => 'Footer',
        'description' => '',
        'settings' => [
            'heading_label' => 'Heading',
            'heading_default' => 'My Store',
            'description_label' => 'Description',
            'description_default' => 'Add a description of your store here',

            'show_social_links_label' => 'Show social links',
            'show_social_links_info' => 'You can configure links in theme settings',
        ],
        'blocks' => [
            'group' => [
                'name' => 'Links group',
                'settings' => [
                    'title_label' => 'Name',
                    'title_default' => 'Links group',
                ],
            ],
            'link' => [
                'name' => 'Link',
                'settings' => [
                    'link_label' => 'Link',
                ],
            ],
        ],
    ],
    'hero' => [
        'name' => 'Hero',
        'description' => '',
        'settings' => [
            'image_label' => 'Image',
            'height_label' => 'Height',
            'height_small' => 'Small',
            'height_medium' => 'Medium',
            'height_large' => 'Large',
            'header_content' => 'Content',
            'content_position_label' => 'Content position',
            'content_position_top' => 'Top',
            'content_position_middle' => 'Middle',
            'content_position_bottom' => 'Bottom',
            'show_overlay_label' => 'Show overlay',
            'overlay_opacity_label' => 'Overlay opacity',
        ],
        'blocks' => [
            'heading' => [
                'name' => 'Heading',
                'settings' => [
                    'heading_label' => 'Heading',
                    'heading_default' => 'Hero heading',
                    'heading_size_label' => 'Heading size',
                    'heading_size_small' => 'Small',
                    'heading_size_medium' => 'Medium',
                    'heading_size_large' => 'Large',
                ],
            ],
            'subheading' => [
                'name' => 'Subheading',
                'settings' => [
                    'subheading_label' => 'Subheading',
                    'subheading_default' => 'Hero Subheading',
                ],
            ],
            'button' => [
                'name' => 'Call to action',
                'settings' => [
                    'text_label' => 'Button text',
                    'text_default' => 'Shop now',
                    'link_label' => 'Button Link',
                ],
            ],
        ],
    ],
    'category-list' => [
        'name' => 'Category List',
        'description' => '',
        'settings' => [
            'heading_label' => 'Title',
            'heading_default' => 'Featured Categories',
            'heading_size_label' => 'Title Size',
            'columns_desktop_label' => 'Number of Columns (Desktop)',
            'columns_mobile_label' => 'Number of Columns (Mobile)',
        ],
        'blocks' => [
            'category' => [
                'name' => 'Category',
                'settings' => [
                    'category_label' => 'Category',
                ],
            ],
        ],
    ],
    'featured-products' => [
        'name' => 'Featured Products',
        'description' => '',

        'settings' => [
            'heading_label' => 'Heading',
            'heading_default' => 'Featured Products',
            'subheading_label' => 'Subheading',
            'subheading_default' => 'Check out our latest products',
            'nb_products_label' => 'Number of Products to show',
            'nb_products_info' => 'Only used when no product block is added',
            'product_type_label' => 'Product Type',
            'product_type_info' => 'Only used when no product block is added',
        ],

        'blocks' => [
            'product' => [
                'name' => 'Product',
                'settings' => [
                    'product_label' => 'Product',
                    'product_info' => 'Select a product to display',
                ],
            ],
        ],
    ],
    'newsletter' => [
        'name' => 'Newsletter',
        'description' => '',

        'settings' => [
            'title_label' => 'Title',
            'title_default' => 'Sign up for our newsletter',
            'description_label' => 'Description',
            'description_default' => 'Use this text to share information about your brand with your customers. Describe a product, share announcements, or welcome customers to your store.',
            'custom_design_label' => 'Custom design',
            'background_color_label' => 'Background Color',
            'text_color_label' => 'Text Color',
            'button_color_label' => 'Button Color',
            'button_text_color_label' => 'Button Text Color',
        ],
    ],

    'product-details' => [
        'name' => 'Product Details',
        'description' => 'Product Details',

        'settings' => [
            'position_label' => 'Position',
            'position_right' => 'Right',
            'position_under_gallery' => 'Under images gallery',
        ],
        'blocks' => [
            'text' => [
                'name' => 'Text',
                'settings' => [
                    'text_label' => 'Text',
                ],
            ],
            'title' => [
                'name' => 'Title',
            ],
            'price' => [
                'name' => 'Price',
            ],
            'rating' => [
                'name' => 'Rating',
            ],
            'short-description' => [
                'name' => 'Short description',
            ],
            'quantity-selector' => [
                'name' => 'Quantity selector',
            ],
            'buy-buttons' => [
                'name' => 'Buy buttons',
                'settings' => [
                    'enable_buy_now_label' => 'Show buy now button',
                    'enable_buy_now_info' => 'Enable this option to display a \'Buy Now\' button, allowing customers to proceed directly to checkout for a faster purchasing experience.',
                ],
            ],
            'description' => [
                'name' => 'Product description',
            ],
            'separator' => [
                'name' => 'Separator',
            ],
            'variant-picker' => [
                'name' => 'Variant Picker',
            ],
            'grouped-options' => [
                'name' => 'Grouped product options',
            ],
            'bundle-options' => [
                'name' => 'Product bundle options',
            ],
            'downloadable-options' => [
                'name' => 'Downloadable products options',
            ],
        ],
    ],
];
