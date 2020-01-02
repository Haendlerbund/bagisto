<script>
export default {
    props: {
        option: Object,
        allowedProductIds: Array,
        selectedOption: Object,
    },
    computed: {
        isDisabled() {
            return (!this.option.products.find(productId => this.allowedProductIds.includes(productId)))
        },
        isSelected(option) {
            return (
                this.selectedOption
                && this.selectedOption.id === this.option.id
            )
        },
    }
}
</script>