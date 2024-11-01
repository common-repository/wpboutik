<aside class="py-6 lg:col-span-3">
    <nav class="space-y-1">
	    <?php foreach ( wpboutik_get_account_menu_items() as $endpoint => $label ) : ?>
            <a href="<?php echo esc_url( wpboutik_get_account_endpoint_url( $endpoint ) ); ?>" class="<?php echo wpboutik_get_account_menu_item_classes( $endpoint ); ?>"><?php echo esc_html( $label ); ?></a>
	    <?php endforeach; ?>
    </nav>
</aside>
