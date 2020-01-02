<template>
    <label class="swatch"
        :data-id="option.id"
        :data-attribute-label="attributeSlug"
        :for="['attribute_' + attribute.id + '_option_' + option.id]"
    >

        <input type="radio"
            v-validate="'required'"
            :data-attribute-value="optionSlug"
            :name="['super_attribute[' + attribute.id + ']']"
            :id="['attribute_' + attribute.id + '_option_' + option.id]"
            :value="option.id"
            :disabled="isDisabled"
            :checked="isSelected"
            :data-vv-as="'&quot;' + attribute.label + '&quot;'"
            @change="selectOption"
        />

        <span
            v-if="swatchType === 'color'"
            :style="{ background: option.swatch_value }"
        />

        <img
            v-if="swatchType === 'image'"
            :src="option.swatch_value"
        />

        <span
            v-if="swatchType === 'text'"
            :class="{ disabled: isDisabled }"
        >
            {{ option.label }}
        </span>

    </label>
</template>

<script>
import OptionBase from './option-base'
import slugify from '../../utils/slugify'

export default {
    extends: OptionBase,
    props: {
        attribute: {
            type: Object,
            required: true,
        },
        swatchType: {
            type: String,
            required: true,
        },
    },
    computed: {
        attributeSlug() {
            return slugify(this.attribute.label)
        },
        optionSlug() {
            return slugify(this.option.label)
        },
    },
    methods: {
        selectOption(e) {
            this.$emit('change', e);
        },
    },
}
</script>

<style lang="scss" scoped>
    span.disabled {
        color: grey;
    }
</style>