
<?php
/**
 * Plugin Name: GLiTCH Force Price
 * Description: Applique automatiquement un prix unique à toutes les variations WooCommerce. F*ck les plugins payants.
 * Version: 1.0
 * Author: Aël (pour Pierre)
 */

add_action('woocommerce_product_after_variable_attributes', 'glitch_add_global_price_field', 10, 3);
add_action('woocommerce_process_product_meta_variable', 'glitch_save_global_price_field', 10, 1);
add_action('save_post_product', 'glitch_apply_global_price_to_variations', 20, 1);

function glitch_add_global_price_field($loop, $variation_data, $variation) {
    if ($loop === 0) {
        global $post;
        $global_price = get_post_meta($post->ID, '_glitch_global_price', true);
        echo '<div class="options_group">';
        woocommerce_wp_text_input(array(
            'id' => '_glitch_global_price',
            'label' => '💸 Prix unique pour toutes les variations',
            'desc_tip' => true,
            'description' => 'Remplis ce champ et clique "Mettre à jour" pour appliquer à toutes les variations.',
            'value' => $global_price,
            'type' => 'number',
            'custom_attributes' => array(
                'step' => '0.01',
                'min' => '0'
            )
        ));
        echo '</div>';
    }
}

function glitch_save_global_price_field($post_id) {
    if (isset($_POST['_glitch_global_price'])) {
        update_post_meta($post_id, '_glitch_global_price', wc_clean($_POST['_glitch_global_price']));
    }
}

function glitch_apply_global_price_to_variations($post_id) {
    $product = wc_get_product($post_id);
    if (!$product || $product->get_type() !== 'variable') return;

    $global_price = get_post_meta($post_id, '_glitch_global_price', true);
    if ($global_price === '') return;

    $variations = $product->get_children();
    foreach ($variations as $variation_id) {
        $variation = wc_get_product($variation_id);
        $variation->set_regular_price($global_price);
        $variation->set_manage_stock(false);
        $variation->set_stock_status('instock');
        $variation->save();
    }
}
?>
