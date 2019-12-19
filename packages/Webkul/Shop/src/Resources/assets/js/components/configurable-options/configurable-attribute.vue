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

            <ConfigurableAttributeOption
                v-for='option in options'
                :key="option.id"
                :option="option"
                :allowedProductIds="allowedProductIds"
                :selectedOption="selectedOption"
            />

        </select>

        <span v-if="isRadio" class="swatch-container">
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
                    :value="option"
                    :data-vv-as="'&quot;' + attribute.label + '&quot;'"
                    @change="configure(attribute, $event.target.value)"/>

                <span v-if="attribute.swatch_type == 'color'"
                    :style="{ background: option.swatch_value }"></span>

                <img v-if="attribute.swatch_type == 'image'" :src="option.swatch_value"/>

                <span
                    :data-attribute-value="option.label.toLowerCase()"
                    v-if="attribute.swatch_type == 'text'">
                    {{ option.label }}
                </span>

            </label>

            <span v-if="! attribute.options.length"
                class="no-options">{{ __('shop::app.products.select-above-options') }}</span>
        </span>

        <span class="control-error" v-if="errors.has('super_attribute[' + attribute.id + ']')">
            {{ errors.first('super_attribute[' + attribute.id + ']') }}
        </span>
    </div>
</template>

<script>
import ConfigurableAttributeOption from './configurable-attribute-option'

export default {
    components: {
        ConfigurableAttributeOption,
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
            return (this.swatchType === 'radio')
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