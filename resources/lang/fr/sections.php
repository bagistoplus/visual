<?php

return [
    'announcement-bar' => [
        'name' => 'Barre d\'annonce',
        'description' => 'Une simple barre d\'annonce pour afficher des informations importantes à vos clients.',
        'settings' => [
            'text_label' => 'Texte de l\'annonce',
            'default_text' => 'Livraison gratuite sur toutes les commandes de plus de 50 $',
            'link_label' => 'Lien',
            'background_color_label' => 'Couleur de fond',
            'text_color_label' => 'Couleur du texte',
        ],
    ],
    'header' => [],
    'footer' => [],
    'hero' => [
        'name' => 'Héros',
        'description' => '',
        'settings' => [
            'image_label' => 'Image',
            'height_label' => 'Hauteur',
            'height_small' => 'Petite',
            'height_medium' => 'Moyenne',
            'height_large' => 'Grande',
            'header_content' => 'Contenu',
            'content_position_label' => 'Position du contenu',
            'content_position_top' => 'Haut',
            'content_position_middle' => 'Milieu',
            'content_position_bottom' => 'Bas',
            'show_overlay_label' => 'Afficher l\'overlay',
            'overlay_opacity_label' => 'Opacité de l\'overlay',
        ],
        'blocks' => [
            'heading' => [
                'name' => 'Titre',
                'settings' => [
                    'heading_label' => 'Titre',
                    'heading_default' => 'Titre principal',
                    'heading_size_label' => 'Taille du titre',
                    'heading_size_small' => 'Petite',
                    'heading_size_medium' => 'Moyenne',
                    'heading_size_large' => 'Grande',
                ],
            ],
            'subheading' => [
                'name' => 'Sous-titre',
                'settings' => [
                    'subheading_label' => 'Sous-titre',
                    'subheading_default' => 'Sous-titre principal',
                ],
            ],
            'button' => [
                'name' => 'Appel à l\'action',
                'settings' => [
                    'text_label' => 'Texte du bouton',
                    'text_default' => 'Acheter maintenant',
                    'link_label' => 'Lien du bouton',
                ],
            ],
        ],
    ],
    'category-list' => [
        'name' => 'Liste des catégories',
        'description' => '',
        'settings' => [
            'heading_label' => 'Titre',
            'heading_default' => 'Catégories en vedette',
            'heading_size_label' => 'Taille du titre',
            'columns_desktop_label' => 'Nombre de colonnes (Bureau)',
            'columns_mobile_label' => 'Nombre de colonnes (Mobile)',
        ],
        'blocks' => [
            'category' => [
                'name' => 'Catégorie',
                'settings' => [
                    'category_label' => 'Catégorie',
                ],
            ],
        ],
    ],
    'featured-products' => [
        'name' => 'Produits en vedette',
        'description' => '',

        'settings' => [
            'heading_label' => 'Titre',
            'heading_default' => 'Produits en vedette',
            'subheading_label' => 'Sous-titre',
            'subheading_default' => 'Découvrez nos derniers produits',
            'nb_products_label' => 'Nombre de produits à afficher',
            'nb_products_info' => 'Utilisé uniquement lorsqu\'aucun bloc produit n\'est ajouté',
            'product_type_label' => 'Type de produit',
            'product_type_info' => 'Utilisé uniquement lorsqu\'aucun bloc produit n\'est ajouté',
        ],

        'blocks' => [
            'product' => [
                'name' => 'Produit',
                'settings' => [
                    'product_label' => 'Produit',
                    'product_info' => 'Sélectionnez un produit à afficher',
                ],
            ],
        ],
    ],
    'newsletter' => [
        'name' => 'Newsletter',
        'description' => '',

        'settings' => [
            'title_label' => 'Titre',
            'title_default' => 'Inscrivez-vous à notre newsletter',
            'description_label' => 'Description',
            'description_default' => 'Utilisez ce texte pour partager des informations sur votre marque avec vos clients. Décrivez un produit, partagez des annonces ou accueillez des clients dans votre boutique.',
            'custom_design_label' => 'Design personnalisé',
            'background_color_label' => 'Couleur de fond',
            'text_color_label' => 'Couleur du texte',
            'button_color_label' => 'Couleur du bouton',
            'button_text_color_label' => 'Couleur du texte du bouton',
        ],
    ],
];
