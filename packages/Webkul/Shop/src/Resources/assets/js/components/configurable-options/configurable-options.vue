<template>
    <div class="attributes">

        <input
            type="hidden"
            :name="name"
            :value="selectedProductId"
        />

        <!-- TODO: add support for swatch_type color, image and text -->
        <ConfigurableAttribute
            v-for="attribute in attributes"
            :key="attribute.code"
            :attribute="attribute"
            :selectedOption="selectedOptions[attribute.code]"
            :disabled="isDisabled(attribute.code)"
            :allowedProductIds="getAllowedProductIds(attribute.code)"
            @selected="setSelectedOption"
        />

    </div>
</template>

<script>
import slugify from 'slugify'
import ConfigurableAttribute from './configurable-attribute'

export default {
    components: {
        ConfigurableAttribute,
    },
    props: {
        config: Object,
        name: String,
    },
    data() {
        let selectedOptions = {}
        let dependents = {}
        let prevAttributeCode = null

        this.config.attributes.forEach((attribute) => {
            selectedOptions[attribute.code] = null
            dependents[attribute.code] = prevAttributeCode
            prevAttributeCode = attribute.code
        })

        return {
            selectedOptions,
            dependents,
        }
    },
    computed: {
        attributes() {
            return this.config.attributes
        },
        searchParams() {
            const params = new URLSearchParams()
            Object.entries(this.selectedOptions).map(([code, option]) => {
                if (!option) return
                params.set(code, this.slugify(option.label))
            })
            return params
        },
        allProductIds() {
            let productIds = new Set()
            this.attributes.forEach((attribute) => {
                attribute.options.forEach((option) => {
                    option.products.forEach((productId) => {
                        productIds.add(productId)
                    })
                })
            })
            return [...productIds]
        },
        allowedProductIds() {
            return this.getAllowedProductIds(null)
        },
        selectedProductId() {
            return this.allowedProductIds[0]
        },
        galleryImages() {
            return window.galleryImages.slice(0) || [];
        },
    },
    watch: {
        searchParams(params) {
            window.history.pushState(
                Object.entries(params),
                document.title,
                `?${params.toString()}`
            )
        },
        selectedProductId() {
            this.reloadPrice()
            this.changeProductImages()
            this.changeStock()
        },
        selectedOptions(options) {
            const entries = Object.entries(options)
            Object.entries(options)
                .filter((entry) => (entry[1]))
                .map((entry) => {
                    const [code, option] = entry
                    if (!this.isAllowedToSelect(code, option)) { 
                        this.setSelectedOption(code, null)
                    }
                })
        },
    },
    created() {
        this.preselectBySearchParams()
    },
    methods: {
        setSelectedOption(code, option) {
            this.selectedOptions = {
                ...this.selectedOptions,
                [code]: option,
            }
        },
        getAllowedProductIds(code) {
            let attributes = [...this.attributes]
            if (code) {
                const attributeIndex = this.attributes.findIndex((attribute) => attribute.code === code)
                attributes = attributes.filter((attr, index) => index < attributeIndex)
            }
            return attributes
                .filter(({ code }) => (this.selectedOptions[code]))
                .reduce((productIds, { code }) => {
                    const { products } = this.selectedOptions[code]
                    return productIds.filter(productId => products.includes(productId))
                }, this.allProductIds)
        },
        isAllowedToSelect(code, option) {
            const allowedProductIds = this.getAllowedProductIds(code)
            return (allowedProductIds.find(productId => option.products.includes(productId)))
        },
        isSelected(code) {
            return (this.selectedOptions[code])
        },
        isDisabled(code) {
            const dependent = this.dependents[code]
            return (dependent && !this.isSelected(dependent))
        },
        slugify(value) {
            return slugify(value, {
                lower: true,
                replacement: '-',
            })
        },
        preselectBySearchParams() {
            const urlParams = new URLSearchParams(window.location.search)
            urlParams.forEach((value, key) => {
                const attribute = this.attributes.find(attribute => attribute.code === key)
                if (!attribute) return
                const option = attribute.options.find(option => value === this.slugify(option.label))
                if (!option) return
                this.setSelectedOption(key, option)
            })
        },
        reloadPrice() {
            const priceLabelElement = document.querySelector('.price-label');
            const priceElement = document.querySelector('.final-price');

            if (this.selectedProductId) {
                priceLabelElement.style.display = 'none';
                priceElement.innerHTML = this.config.variant_prices[this.selectedProductId].final_price.formated_price;
                eventBus.$emit('configurable-variant-selected-event', this.selectedProductId)
                return;
            }

            priceLabelElement.style.display = 'inline-block';
            priceElement.innerHTML = this.config.regular_price.formated_price;
            eventBus.$emit('configurable-variant-selected-event', 0)
        },
        changeProductImages() {
            if (this.galleryImages.length <= 0) {
                return;
            }

            window.galleryImages.splice(0, window.galleryImages.length)

            this.galleryImages.forEach(image => {
                window.galleryImages.push(image)
            });

            if (this.selectedProductId) {
                this.config.variant_images[this.selectedProductId].forEach(image => {
                    window.galleryImages.unshift(image)
                });
            }
        },
        changeStock() {
            let inStockElement = document.getElementById('in-stock');
            if (!inStockElement) {
                return;
            }
            inStockElement.style.display = (this.selectedProductId) ? 'block' : 'none';
        },
    },
}
</script>