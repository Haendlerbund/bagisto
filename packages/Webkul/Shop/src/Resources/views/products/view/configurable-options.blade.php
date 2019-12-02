@if (Webkul\Product\Helpers\ProductType::hasVariants($product->type))

    @inject ('configurableOptionHelper', 'Webkul\Product\Helpers\ConfigurableOption')

    <?php

    /** @var \Webkul\Product\Models\Product $product */
    /** @var \Webkul\Product\Helpers\ConfigurableOption $configurableOptionHelper */

    $variants = $configurableOptionHelper->getVariantsConfig($product)


    ?>

    {!! view_render_event('bagisto.shop.products.view.configurable-options.before', ['product' => $product]) !!}

    <product-options :variants='@json($variants)' inline-template>
        <div class="attributes">

            <input
              type="hidden"
              id="selected_configurable_option"
              name="selected_configurable_option"
              :value="selectedProductId"
            >

            <div
              v-for='(attribute, index) in childAttributes'
              class="attribute control-group"
              :class="[errors.has('super_attribute[' + attribute.id + ']') ? 'has-error' : '']"
            >
                <label class="required">@{{ attribute.label }}</label>

                <span
                    v-if="! attribute.swatch_type
                    || attribute.swatch_type == ''
                    || attribute.swatch_type == 'dropdown'"
                >
                    <select
                        class="control"
                        v-validate="'required'"
                        :name="['super_attribute[' + attribute.id + ']']"
                        :disabled="attribute.disabled"
                        :id="['attribute_' + attribute.id]"
                        :data-vv-as="'&quot;' + attribute.label + '&quot;'"
                        :data-attribute-label="attribute.label.toLowerCase()"
                        @change="configure(attribute, $event.target.value)"
                    >

                        <option
                            v-for='(option, index) in attribute.options'
                            :value="option.id"
                            :selected="option.selected"
                        >@{{ option.label }}</option>

                    </select>
                </span>

                <span class="swatch-container" v-else>
                    <label class="swatch"
                           v-for='(option, index) in attribute.options'
                           v-if="option.id"
                           :data-id="option.id"
                           :data-attribute-label="attribute.label.toLowerCase()"
                           :for="['attribute_' + attribute.id + '_option_' + option.id]">

                        <input type="radio"
                               v-validate="'required'"
                               :data-attribute-value="option.label.toLowerCase()"
                               :name="['super_attribute[' + attribute.id + ']']"
                               :id="['attribute_' + attribute.id + '_option_' + option.id]"
                               :value="option.id"
                               :data-vv-as="'&quot;' + attribute.label + '&quot;'"
                               @change="configure(attribute, $event.target.value)"/>

                        <span v-if="attribute.swatch_type == 'color'"
                              :style="{ background: option.swatch_value }"></span>

                        <img v-if="attribute.swatch_type == 'image'" :src="option.swatch_value"/>

                        <span
                            :data-attribute-value="option.label.toLowerCase()"
                            v-if="attribute.swatch_type == 'text'">
                            @{{ option.label }}
                        </span>

                    </label>

                    <span v-if="! attribute.options.length"
                          class="no-options">{{ __('shop::app.products.select-above-options') }}</span>
                </span>

                <span class="control-error" v-if="errors.has('super_attribute[' + attribute.id + ']')">
                    @{{ errors.first('super_attribute[' + attribute.id + ']') }}
                </span>
            </div>

        </div>
    </product-options>

    {!! view_render_event('bagisto.shop.products.view.configurable-options.after', ['product' => $product]) !!}

@endif
