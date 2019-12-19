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
            selectedProduct: null,
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
                if (option) {
                    params.set(code, this.slugify(option.label))
                }
            })
            return params
        },
    },
    watch: {
        searchParams(params) {
            window.history.pushState(
                Object.entries(params),
                document.title,
                `?${params.toString()}`
            )
        }
    },
    created() {
        this.preselectBySearchParams()
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
                if (!attribute) {
                    return
                }
                const option = attribute.options.find(option => value === this.slugify(option.label))
                if (!option) {
                    return
                }
                this.selectedOptions[key] = option
            })
        },
    },
}
</script>