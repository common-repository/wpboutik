<?php
if (!is_tax('wpboutik_product_cat')) {
    return;
}

$category = get_queried_object();
$category_id = $category->term_id;

// Récupérer l'image de la catégorie (si elle existe)
$thumbnail_id = get_term_meta($category_id, 'thumbnail_id', true);
$image_url = '';
if ($thumbnail_id) {
    $image_url = wp_get_attachment_url($thumbnail_id);
}

// Récupérer le nombre de produits dans cette catégorie
$products_count = $category->count;

// Générer les microdonnées JSON-LD
$microdata = [
    "@context" => "https://schema.org",
    "@type" => "CollectionPage",
    "name" => $category->name,
    "description" => $category->description,
    "image" => $image_url,
    "mainEntity" => [
        "@type" => "ItemList",
        "itemListElement" => [],
        "numberOfItems" => $products_count
    ]
];


if (have_posts()) {
    $position = 1;
    while (have_posts()) {
        the_post();
        $product_id = get_the_ID();
        $product_name = get_the_title();
        $product_url = get_permalink();
        $product_image_url = wp_get_attachment_url(get_post_thumbnail_id($product_id));

        // Ajouter chaque produit à la liste des éléments
        $product_datas = [
            "@type" => "ListItem",
            "position" => $position,
            "item" => [
                "@type" => "Product",
                "name" => $product_name,
                "url" => $product_url,
                "image" => $product_image_url,
                "description" => get_the_excerpt(),
                "offers" => [
                  "@type" => "Offer",
                  "url" => get_the_permalink(),
                  "priceCurrency" => get_wpboutik_currency(),
                  "price" => get_post_meta( get_the_ID(), 'price', true ),
                  "availability" => wpboutik_product_availability(get_the_ID())
                ]
            ],
        ];
        $rating = get_comments_count_and_average_rating();
        if ($rating->total_comments != 0 && !empty($rating->average_rating)) {
          $product_datas['item']["aggregateRating"] = [
            "@type"       => "AggregateRating",
            "ratingValue" => $rating->average_rating,
            "reviewCount" => $rating->total_comments
          ];
          
        }
        $variants = get_post_meta( get_the_ID(), 'variants', true );
        $variants = json_decode( $variants );
        if (!empty($variants)) {
          $variants_json = [];
          foreach($variants as $variant) {
            $local_json = [
              "@type" => "Product",
              "sku" => wpb_sku_or_default(),
              "name" => get_the_title() .' '. implode(' ', $variant->name),
              "offers" => [
                "@type" => "Offer",
                "url" => get_the_permalink(),
                "priceCurrency" => get_wpboutik_currency(),
                "price" => $variant->price,
                "availability" => wpboutik_product_availability(get_the_ID(), $variant->id)
              ]
            ];
            if (!empty($variant->image_temp)) { 
              $local_json['image'] = WPBOUTIK_APP_URL.$variant->image_temp;
            }
            $variants_json[] = $local_json;
          }
          $product_datas['item']['hasVariant'] = $variants_json;
        }
        $microdata['mainEntity']['itemListElement'][] = $product_datas;
        $position++;
    }
}

// Convertir en JSON
$microdata_json = json_encode($microdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

// Afficher les microdonnées
echo '<script type="application/ld+json">' . $microdata_json . '</script>';
