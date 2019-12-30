<template>
    <option
        :value="option.id"
        :disabled="isDisabled"
        :selected="isSelected"
    >{{ option.label }}</option>
</template>

<script>
import OptionBase from './option-base'
export default {
    extends: OptionBase,
}
</script>