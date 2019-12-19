<template>
    <div class="attributes">

        <input
            type="hidden"
            :name="name"
            :value="(selectedProduct) ? selectedProduct.id : null"
        />

        <ConfigurableAttribute
            v-for="attribute in attributes"
            :key="attribute.code"
            :attribute="attribute"
            :selectedOption="selectedOptions[attribute.code]"
            :disabled="isDisabled(attribute.code)"
            @selected="setSelectedOption"
        />

    </div>
</template>

<script>
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
            selectedProduct: null,
            selectedOptions,
            dependents,
        }
    },
    computed: {
        attributes() {
            return this.config.attributes
        }
    },
    methods: {
        setSelectedOption(code, option) {
            this.selectedOptions[code] = option
        },
        isSelected(code) {
            return (this.selectedOptions[code])
        },
        isDisabled(code) {
            const dependent = this.dependents[code]
            return (dependent && !this.isSelected(dependent))
        },
    },
}
</script>