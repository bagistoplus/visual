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
    'footer' => [],
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
];
