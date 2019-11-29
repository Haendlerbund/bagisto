<?php

namespace Webkul\Product\Helpers;

use Webkul\Product\Models\ProductFlat;
use Webkul\Attribute\Repositories\AttributeOptionRepository as AttributeOption;
use Webkul\Product\Models\Product;

/**
 * Configurable Option Helper
 *
 * @author    Jitendra Singh <jitendra@webkul.com>
 * @copyright 2018 Webkul Software Pvt Ltd (http://www.webkul.com)
 */
class ConfigurableOption extends AbstractProduct
{
    /**
     * AttributeOptionRepository object
     *
     * @var array
     */
    protected $attributeOption;

    /**
     * ProductImage object
     *
     * @var array
     */
    protected $productImage;

    /**
     * @var array
     */
    protected $preselection;

    /**
     * Create a new controller instance.
     *
     * @param Webkul\Attribute\Repositories\AttributeOptionRepository $attributeOption
     * @param Webkul\Product\Helpers\ProductImage                     $productImage
     *
     * @return void
     */
    public function __construct(
        AttributeOption $attributeOption,
        ProductImage $productImage
    )
    {
        $this->attributeOption = $attributeOption;

        $this->productImage = $productImage;

        $this->preselection = request()->all();
    }

    /**
     * Returns the allowed variants.
     *
     * @param ProductFlat $product
     *
     * @return array
     */
    public function getAllowedProducts(ProductFlat $product): array
    {
        static $variants = [];

        if (count($variants)) {
            return $variants;
        }

        foreach ($product->variants as $variant) {
            if ($variant->isSaleable()) {
                $variants[] = $variant;
            }
        }

        return $variants;
    }

    /**
     * Returns the allowed variants of the given products as JSON.
     *
     * @param ProductFlat $product
     *
     * @return array
     * @see packages/Webkul/Shop/src/Resources/views/products/view/configurable-options.blade.php
     */
    public function getVariantsConfig(ProductFlat $product): array
    {
        $options = $this->getOptions($product, $this->getAllowedProducts($product));

        $config = [
            'attributes'     => $this->getAttributesData($product, $options),
            'index'          => $options['index'] ?? [],
            'regular_price'  => [
                'formated_price' => core()->currency($product->getTypeInstance()->getMinimalPrice()),
                'price'          => $product->getTypeInstance()->getMinimalPrice(),
            ],
            'variant_prices' => $this->getVariantPrices($product),
            'variant_images' => $this->getVariantImages($product),
            'chooseText'     => trans('shop::app.products.choose-option'),
        ];

        return $config;
    }

    /**
     * Get allowed attributes
     *
     * @param ProductFlat $product
     *
     * @return object
     */
    public function getAllowedAttributes(ProductFlat $product): ?object
    {
        return $product->product->super_attributes;
    }

    /**
     * Get Configurable Product Options
     *
     * @param ProductFlat $currentProduct
     * @param array       $allowedProducts
     *
     * @return array
     */
    public function getOptions(ProductFlat $currentProduct, array $allowedProducts): array
    {
        $options = [];

        $allowAttributes = $this->getAllowedAttributes($currentProduct);

        foreach ($allowedProducts as $product) {
            if ($product instanceof ProductFlat) {
                $productId = $product->product_id;
            } else {
                $productId = $product->id;
            }

            foreach ($allowAttributes as $productAttribute) {
                $productAttributeId = $productAttribute->id;

                $attributeValue = $product->{$productAttribute->code};

                if ($attributeValue == '' && $product instanceof ProductFlat)
                    $attributeValue = $product->product->{$productAttribute->code};

                $options[$productAttributeId][$attributeValue][] = $productId;

                $options['index'][$productId][$productAttributeId] = $attributeValue;
            }
        }

        return $options;
    }

    /**
     * Get product attributes
     *
     * @param Product $product
     * @param array   $options
     *
     * @return array
     */
    public function getAttributesData($product, array $options = [])
    {
        $defaultValues = [];

        $attributes = [];

        $allowAttributes = $this->getAllowedAttributes($product);

        foreach ($allowAttributes as $attribute) {

            $attributeOptionsData = $this->getAttributeOptionsData($attribute, $options);

            if ($attributeOptionsData) {
                $attributeId = $attribute->id;

                $attributes[] = [
                    'id'          => $attributeId,
                    'code'        => $attribute->code,
                    'label'       => $attribute->name ? $attribute->name : $attribute->admin_name,
                    'swatch_type' => $attribute->swatch_type,
                    'options'     => $attributeOptionsData,
                ];
            }
        }

        return $attributes;
    }

    /**
     * @param Attribute $attribute
     * @param array     $options
     *
     * @return array
     */
    protected function getAttributeOptionsData($attribute, array $options): array
    {
        $attributeOptionsData = [];

        foreach ($attribute->options as $attributeOption) {

            $optionId = $attributeOption->id;

            if (isset($options[$attribute->id][$optionId])) {
                $attributeOptionsData[] = [
                    'id'           => $optionId,
                    'label'        => $attributeOption->label,
                    'selected'     => $this->isPreselected($attribute->code, $attributeOption->label),
                    'swatch_value' => $attribute->swatch_type == 'image'
                        ? $attributeOption->swatch_value_url
                        : $attributeOption->swatch_value,
                    'products'     => $options[$attribute->id][$optionId],
                ];
            }
        }

        return $attributeOptionsData;
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return bool
     */
    protected function isPreselected(string $key, string $value): bool
    {
        $key = trim(strtolower($key));
        $value = trim(strtolower($value));

        return array_key_exists($key, $this->preselection) && $this->preselection[$key] === $value;
    }

    /**
     * Get product prices for configurable variations
     *
     * @param ProductFlat $product
     *
     * @return array
     */
    protected function getVariantPrices(ProductFlat $product): array
    {
        $prices = [];

        foreach ($this->getAllowedProducts($product) as $variant) {
            if ($variant instanceof ProductFlat) {
                $variantId = $variant->product_id;
            } else {
                $variantId = $variant->id;
            }

            $prices[$variantId] = $variant->getTypeInstance()->getProductPrices();
        }

        return $prices;
    }

    /**
     * Get product images for configurable variations
     *
     * @param ProductFlat $product
     *
     * @return array
     */
    protected function getVariantImages(ProductFlat $product): array
    {
        $images = [];

        foreach ($this->getAllowedProducts($product) as $variant) {
            if ($variant instanceof \Webkul\Product\Models\ProductFlat) {
                $variantId = $variant->product_id;
            } else {
                $variantId = $variant->id;
            }

            $images[$variantId] = $this->productImage->getGalleryImages($variant);
        }

        return $images;
    }
}