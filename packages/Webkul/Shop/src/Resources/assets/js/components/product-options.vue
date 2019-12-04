<script>
export default {

  inject: ['$validator'],

  data() {
    return {
      selectedProductId: '',
      simpleProduct: null,
      childAttributes: [],
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
    configure: function(attribute, value) {
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

    getSelectedIndex: function(attribute, value) {
      var selectedIndex = 0;

      attribute.options.forEach(function(option, index) {
        if (option.id == value) {
          selectedIndex = index;
        }
      })

      return selectedIndex;
    },

    getSelectedProductId: function(attribute, value) {
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

    fillSelect: function(attribute) {
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

    resetChildren: function(attribute) {
      if (attribute.childAttributes) {
        attribute.childAttributes.forEach(function (set) {
          set.selectedIndex = 0;
          set.disabled = true;
        });
      }
    },

    clearSelect(attribute) {
      if (! attribute)
        return;

      if (! attribute.swatch_type || attribute.swatch_type == '' || attribute.swatch_type == 'dropdown') {
        let element = document.getElementById("attribute_" + attribute.id);

        if (element) {
          element.selectedIndex = "0";
        }
      } else {
        let elements = document.getElementsByName('super_attribute[' + attribute.id + ']');

        elements.map(element => (
          {
            ...element,
            checked: false,
          }
        ));
      }
    },

    getAttributeOptions(attributeId) {
      return this.config.attributes
        .find(attribute => attribute.id == attributeId)
        .options;
    },

    reloadPrice() {
      var selectedOptionCount = 0;

      this.childAttributes.forEach(function(attribute) {
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

    changeProductImages() {
      if (this.galleryImages.length <= 0) {
        return;
      }

      window.galleryImages.splice(0, window.galleryImages.length)

      this.galleryImages.forEach(function(image) {
        window.galleryImages.push(image)
      });

      if (this.simpleProduct) {
        this.config.variant_images[this.simpleProduct].forEach(function(image) {
          window.galleryImages.unshift(image)
        });
      }
    },

    changeStock(productId) {
      var inStockElement = document.getElementById('in-stock');
      if (!inStockElement) {
        return;
      }
      inStockElement.style.display = (productId) ? 'block' : 'none';
    },
  }
}

</script>
