<template>
    <div
        class="attribute control-group"
        :class="[errors.has('super_attribute[' + attribute.id + ']') ? 'has-error' : '']"
    >
        <label class="required">{{ attribute.label }}</label>

        <select
            v-if="isDropdown"
            class="control"
            v-validate="'required'"
            :name="['super_attribute[' + attribute.id + ']']"
            :disabled="disabled"
            :id="['attribute_' + attribute.id]"
            :data-vv-as="'&quot;' + attribute.label + '&quot;'"
            :data-attribute-label="attribute.label.toLowerCase()"
            @change="selectOption"
        >

            <option :value="null" :selected="!selectedOption">
                Bitte auswählen
            </option>

            <OptionDropdown
                v-for='option in options'
                :key="option.id"
                :option="option"
                :allowedProductIds="allowedProductIds"
                :selectedOption="selectedOption"
            />

        </select>

        <span v-if="isRadio" class="swatch-container">

            <OptionRadio
                v-for='option in options'
                :key="option.id"
                :option="option"
                :allowedProductIds="allowedProductIds"
                :selectedOption="selectedOption"
                :attribute="attribute"
                :swatchType="swatchType"
                @change="selectOption"
            />

        </span>

        <span class="control-error" v-if="errors.has('super_attribute[' + attribute.id + ']')">
            {{ errors.first('super_attribute[' + attribute.id + ']') }}
        </span>
    </div>
</template>

<script>
import OptionDropdown from './option-dropdown'
import OptionRadio from './option-radio'

export default {
    components: {
        OptionDropdown,
        OptionRadio,
    },
    props: {
        attribute: Object,
        selectedOption: Object,
        disabled: Boolean,
        allowedProductIds: Array,
    },
    computed: {
        options() {
            return this.attribute.options
        },
        swatchType() {
            const { swatch_type } = this.attribute
            if (!swatch_type || swatch_type === '') {
                return 'dropdown'
            }
            return swatch_type
        },
        isDropdown() {
            return (this.swatchType === 'dropdown')
        },
        isRadio() {
            return (!this.isDropdown)
        },
    },
    methods: {
        selectOption(e) {
            const optionId = e.currentTarget.value
            const option = this.options.find(option => option.id == optionId)
            this.$emit('selected', this.attribute.code, option)
        },
    },
}
</script>