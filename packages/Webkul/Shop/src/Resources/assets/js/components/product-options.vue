<script>
import slugify from 'slugify'

export default {

  inject: ['$validator'],

  data() {
    return {
      selectedProductId: '',
      simpleProduct: null,
      childAttributes: [],
      urlSearchParams: [],
    }
  },

  props: {
    config: {
      type: Object,
      required: true,
    },
  },

  computed: {
    galleryImages() {
      return window.galleryImages.slice(0) || [];
    },
  },

  created() {
    this.setInitChildAttributes();
    this.preselectBySearchParams();
  },

  watch: {
    urlSearchParams(params) {
      window.history.pushState(Object.entries(params), document.title ,`?${params.toString()}`)
    },
  },

  methods: {
    setInitChildAttributes() {
      let { attributes } = this.config;

      this.childAttributes = attributes.map((inputAttribute, index) => (
        {
          ...inputAttribute,
          childAttributes: this.childAttributes,
          options: [],
          disabled: (index > 0),
        }
      ));

      this.childAttributes = this.childAttributes.map((inputAttribute, index) => (
        Object.assign(inputAttribute, {
          prevAttribute: this.childAttributes[index - 1] || null,
          nextAttribute: this.childAttributes[index + 1] || null,
        })
      ));

      this.fillSelect(this.childAttributes[0]);
    },

    preselectBySearchParams() {
      const urlParams = new URLSearchParams(window.location.search)
      this.childAttributes
        .filter(attribute => urlParams.has(attribute.code))
        .forEach(attribute => {
          const option = attribute.options.find(option => urlParams.get(attribute.code) === this.slugify(option.label))
          if (option) {
            this.configure(attribute, option.id)
          }
      })
    },

    configure(attribute, optionId) {
      this.simpleProduct = this.getSelectedProductId(attribute, optionId);

      if (optionId) {
        attribute.selectedIndex = this.getSelectedIndex(attribute, optionId);

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
      this.changeUrlSearchParams();
    },

    changeUrlSearchParams() {
      this.urlSearchParams = new URLSearchParams()
      this.childAttributes
        .filter(attribute => attribute.selectedIndex)
        .forEach((attribute, index) => {
          this.urlSearchParams.set(
            attribute.code,
            this.slugify(attribute.selectedIndex.label)
          )
        })
    },

    slugify(value) {
      return slugify(value, {
        lower: true,
        replacement: '-',
      })
    },

    getSelectedIndex(attribute, value) {
      return attribute.options.find(option => option.id == value) || 0;
    },

    getSelectedProductId(attribute, value) {
      const { options } = attribute;
      const matchedOptions = options.filter(option => option.id == value);

      if (matchedOptions[0] == undefined || matchedOptions[0].allowedProducts == undefined) {
        return undefined;
      }

      return matchedOptions[0].allowedProducts[0];
    },

    fillSelect(attribute) {
      let options = this.getAttributeOptions(attribute.id);

      this.clearSelect(attribute);

      const emptyOption = {
        id: '',
        label: this.config.chooseText,
        products: [],
      };

      if (!options) {
        return [emptyOption];
      }

      const prevOption = (attribute.prevAttribute)
        ? attribute.prevAttribute.options[attribute.prevAttribute.selectedIndex]
        : null;

      options = options.map(option => {
        let allowedProducts = option.products;

        if (prevOption && prevOption.allowedProducts) {
          allowedProducts = allowedProducts.filter(productId => prevOption.allowedProducts.includes(productId))
        }

        return {...option, allowedProducts};
      });

      attribute.options = [
        emptyOption,
        ...options,
      ];
    },

    resetChildren(attribute) {
      if (!attribute.childAttributes) {
        return;
      }

      attribute.childAttributes = attribute.childAttributes.map(childAttribute => (
        {
          ...childAttribute,
          selectedIndex: 0,
          disabled: true,
        }
      ));
    },

    clearSelect(attribute) {
      if (!attribute) {
        return;
      }

      if (!attribute.swatch_type || attribute.swatch_type == '' || attribute.swatch_type == 'dropdown') {
        let element = document.getElementById("attribute_" + attribute.id);
        if (element) {
          element.selectedIndex = "0";
        }
        return;
      }

      let elements = document.getElementsByName('super_attribute[' + attribute.id + ']');
      elements.map(element => (
        {
          ...element,
          checked: false,
        }
      ));
    },

    getAttributeOptions(attributeId) {
      return this.config.attributes
        .find(attribute => attribute.id == attributeId)
        .options;
    },

    reloadPrice() {
      let selectedOptionCount = 0;

      this.childAttributes
        .filter(attribute => attribute.selectedIndex > 0)
        .forEach(attribute => selectOptionCount++);

      const priceLabelElement = document.querySelector('.price-label');
      const priceElement = document.querySelector('.final-price');

      if (this.childAttributes.length == selectedOptionCount > 0) {
        priceLabelElement.style.display = 'none';
        priceElement.innerHTML = this.config.variant_prices[this.simpleProduct].final_price.formated_price;
        eventBus.$emit('configurable-variant-selected-event', this.simpleProduct)
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

      if (this.simpleProduct) {
        this.config.variant_images[this.simpleProduct].forEach(image => {
          window.galleryImages.unshift(image)
        });
      }
    },

    changeStock(productId) {
      let inStockElement = document.getElementById('in-stock');
      if (!inStockElement) {
        return;
      }
      inStockElement.style.display = (productId) ? 'block' : 'none';
    },
  }
}

</script>
