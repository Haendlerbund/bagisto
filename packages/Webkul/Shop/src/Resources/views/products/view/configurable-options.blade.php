@if ($product->type == 'configurable')

    @inject ('configurableOptionHelper', 'Webkul\Product\Helpers\ConfigurableOption')

    <?php

    /** @var \Webkul\Product\Models\Product $product */
    /** @var \Webkul\Product\Helpers\ConfigurableOption $configurableOptionHelper */

    $config = $configurableOptionHelper->getVariantsConfig($product)

    ?>

    {!! view_render_event('bagisto.shop.products.view.configurable-options.before', ['product' => $product]) !!}

    <product-options config='@json($config)'></product-options>

    {!! view_render_event('bagisto.shop.products.view.configurable-options.after', ['product' => $product]) !!}

    @push('scripts')

        <script type="text/x-template" id="product-options-template">
            <div class="attributes">

                <input type="hidden" id="selected_configurable_option" name="selected_configurable_option"
                       :value="selectedProductId">

                <div v-for='(attribute, index) in childAttributes' class="attribute control-group"
                     :class="[errors.has('super_attribute[' + attribute.id + ']') ? 'has-error' : '']">
                    <label class="required">@{{ attribute.label }}</label>

                    <span
                        v-if="! attribute.swatch_type || attribute.swatch_type == '' || attribute.swatch_type == 'dropdown'">
                        <select
                            class="control testclass"
                            v-validate="'required'"
                            :name="['super_attribute[' + attribute.id + ']']"
                            :disabled="attribute.disabled"
                            @change="configure(attribute, $event.target.value)"
                            :id="['attribute_' + attribute.id]"
                            :data-vv-as="'&quot;' + attribute.label + '&quot;'"
                            :data-attribute-label="attribute.label.toLowerCase()"
                        >

                            <option v-for='(option, index) in attribute.options'
                                    :value="option.id"
                                    :selected="isPreselected(attribute.value, option.label)"
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
        </script>

        <script>

            Vue.component('product-options', {

                template: '#product-options-template',

                inject: ['$validator'],

                props: {
                    config: {
                        type: Object,
                        required: true,
                    },
                },

                data: function () {
                    return {
                        childAttributes: [],

                        selectedProductId: '',

                        simpleProduct: null,

                        galleryImages: []
                    }
                },

                computed: {
                    searchParams: function () {
                        url = new URL(window.location.href);
                        return url.searchParams.entries();
                    },

                    attributes: function () {
                        return this.config.attributes.slice();
                    },
                },

                created: function () {
                    this.galleryImages = galleryImages.slice(0);

                    var childAttributes = this.childAttributes,
                        attributes = config.attributes.slice(),
                        index = attributes.length,
                        attribute;

                    var url = new URL(window.location.href);
                    var params = url.searchParams.entries();

                    while (index--) {
                        attribute = attributes[index];

                        attribute.options = [];

                        attribute = Object.assign(attribute, {
                            childAttributes: childAttributes.slice(),
                            prevAttribute: attributes[index - 1],
                            nextAttribute: attributes[index + 1]
                        });

                        if (index && attribute.prevAttribute.value == '') {
                            attribute.disabled = true;
                        } else {
                            this.fillSelect(attribute);
                        }

                        if (url.searchParams.has(attribute.code)) {

                            let attributeCode = url.searchParams.get(attribute.code).toLowerCase();
                            let attributeValue = attribute.options.find(option => option.label.toLowerCase() == attributeCode);
                            if (attributeValue) {
                                attribute.value = attributeValue.label;
                            }

                            if (attribute.nextAttribute) {
                                this.fillSelect(attribute.nextAttribute);
                            }
                            // this.fillSelect(attribute.nextAttribute);
                            // console.log('attribute', attribute);
                            // console.log('attributeOptions', attribute.options.find());
                            // attribute.value = attribute.id;
                        }

                        childAttributes.unshift(attribute);

                    }
                },

                mounted: function () {

                    // preselect items with values from url (e.g. GET params like ?color=green&size=xs)
                    // url = new URL(window.location.href);

                    // for (const [key, value] of url.searchParams.entries()) {
                    //     // handle the case of <select>:
                    //     element = document.querySelector('select[data-attribute-label="' + key + '"]');
                    //     if (element) {
                    //         for (var i = 0; i < element.options.length; i++) {
                    //             if (element.options[i].text.toLowerCase() == value) {
                    //                 element.selectedIndex = i;
                    //                 break;
                    //             }
                    //         }
                    //     }

                    //     // handle the case of swatch:
                    //     element = document.querySelector('input[data-attribute-value="' + value + '"]');
                    //     if (element) {
                    //         element = element[0];
                    //         element.click();
                    //     }
                    // }
                },

                methods: {
                    isPreselected: function (attributeValue, optionLabel) {
                        let isSelected = false;

                        console.log('attributeValue', attributeValue);
                        console.log('optionLabel', optionLabel);

                        if (attributeValue != '') {
                            if (attributeValue === optionLabel) {
                                isSelected = true;
                            }
                        }

                        return isSelected;
                    },

                    configure: function (attribute, value) {
                        this.simpleProduct = this.getSelectedProductId(attribute, value);

                        if (value) {
                            attribute.selectedIndex = this.getSelectedIndex(attribute, value);

                            if (attribute.nextAttribute) {
                                attribute.nextAttribute.disabled = false;

                                this.fillSelect(attribute.nextAttribute);

                                this.resetChildren(attribute.nextAttribute);
                            } else {
                                this.selectedProductId = this.simpleProduct;
                            }
                        } else {
                            attribute.selectedIndex = 0;

                            this.resetChildren(attribute);

                            this.clearSelect(attribute.nextAttribute)
                        }

                        this.reloadPrice();
                        this.changeProductImages();
                        this.changeStock(this.simpleProduct);
                    },

                    getSelectedIndex: function (attribute, value) {
                        var selectedIndex = 0;

                        attribute.options.forEach(function (option, index) {
                            if (option.id == value) {
                                selectedIndex = index;
                            }
                        });

                        return selectedIndex;
                    },

                    getSelectedProductId: function (attribute, value) {
                        var options = attribute.options,
                            matchedOptions;

                        matchedOptions = options.filter(function (option) {
                            return option.id == value;
                        });

                        if (matchedOptions[0] != undefined && matchedOptions[0].allowedProducts != undefined) {
                            return matchedOptions[0].allowedProducts[0];
                        }

                        return undefined;
                    },

                    fillSelect: function (attribute) {
                        var options = this.getAttributeOptions(attribute.id),
                            prevOption,
                            index = 1,
                            allowedProducts,
                            i,
                            j;

                        this.clearSelect(attribute)

                        attribute.options = [{'id': '', 'label': this.config.chooseText, 'products': []}];

                        if (attribute.prevAttribute) {
                            prevOption = attribute.prevAttribute.options[attribute.prevAttribute.selectedIndex];
                        }

                        if (options) {
                            for (i = 0; i < options.length; i++) {
                                allowedProducts = [];

                                if (prevOption) {
                                    for (j = 0; j < options[i].products.length; j++) {
                                        if (prevOption.allowedProducts && prevOption.allowedProducts.indexOf(options[i].products[j]) > -1) {
                                            allowedProducts.push(options[i].products[j]);
                                        }
                                    }
                                } else {
                                    allowedProducts = options[i].products.slice(0);
                                }

                                if (allowedProducts.length > 0) {
                                    options[i].allowedProducts = allowedProducts;

                                    attribute.options[index] = options[i];

                                    index++;
                                }
                            }
                        }
                    },

                    resetChildren: function (attribute) {
                        if (attribute.childAttributes) {
                            attribute.childAttributes.forEach(function (set) {
                                set.selectedIndex = 0;
                                set.disabled = true;
                            });
                        }
                    },

                    clearSelect: function (attribute) {
                        if (!attribute)
                            return;

                        if (!attribute.swatch_type || attribute.swatch_type == '' || attribute.swatch_type == 'dropdown') {
                            var element = document.getElementById("attribute_" + attribute.id);

                            if (element) {
                                element.selectedIndex = "0";
                            }
                        } else {
                            var elements = document.getElementsByName('super_attribute[' + attribute.id + ']');

                            var this_this = this;

                            elements.forEach(function (element) {
                                element.checked = false;
                            })
                        }
                    },

                    getAttributeOptions: function (attributeId) {
                        var this_this = this,
                            options;

                        this.config.attributes.forEach(function (attribute, index) {
                            if (attribute.id == attributeId) {
                                options = attribute.options;
                            }
                        })

                        return options;
                    },

                    reloadPrice: function () {
                        var selectedOptionCount = 0;

                        this.childAttributes.forEach(function (attribute) {
                            if (attribute.selectedIndex) {
                                selectedOptionCount++;
                            }
                        });

                        var priceLabelElement = document.querySelector('.price-label');
                        var priceElement = document.querySelector('.final-price');

                        if (this.childAttributes.length == selectedOptionCount) {
                            priceLabelElement.style.display = 'none';

                            priceElement.innerHTML = this.config.variant_prices[this.simpleProduct].final_price.formated_price;

                            eventBus.$emit('configurable-variant-selected-event', this.simpleProduct)
                        } else {
                            priceLabelElement.style.display = 'inline-block';

                            priceElement.innerHTML = this.config.regular_price.formated_price;

                            eventBus.$emit('configurable-variant-selected-event', 0)
                        }
                    },

                    changeProductImages: function () {
                        galleryImages.splice(0, galleryImages.length)

                        this.galleryImages.forEach(function (image) {
                            galleryImages.push(image)
                        });

                        if (this.simpleProduct) {
                            this.config.variant_images[this.simpleProduct].forEach(function (image) {
                                galleryImages.unshift(image)
                            });
                        }
                    },

                    changeStock: function (productId) {
                        var inStockElement = document.getElementById('in-stock');

                        if (productId) {
                            inStockElement.style.display = "block";
                        } else {
                            inStockElement.style.display = "none";
                        }
                    },
                }

            });

        </script>
    @endpush

@endif