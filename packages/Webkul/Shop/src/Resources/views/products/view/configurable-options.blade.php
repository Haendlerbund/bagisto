@if (Webkul\Product\Helpers\ProductType::hasVariants($product->type))

    @inject ('configurableOptionHelper', 'Webkul\Product\Helpers\ConfigurableOption')

    <?php

    /** @var \Webkul\Product\Models\Product $product */
    /** @var \Webkul\Product\Helpers\ConfigurableOption $configurableOptionHelper */

    $config = $configurableOptionHelper->getVariantsConfig($product)


    ?>

    {!! view_render_event('bagisto.shop.products.view.configurable-options.before', ['product' => $product]) !!}

    <configurable-options name="selected_configurable_option" :config='@json($config)' />

    {!! view_render_event('bagisto.shop.products.view.configurable-options.after', ['product' => $product]) !!}

@endif
