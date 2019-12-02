<script>
export default {

  inject: ['$validator'],

  data: function() {
    return {
      childAttributes: [],

      selectedProductId: '',

      simpleProduct: null,

      galleryImages: []
    }
  },

  props: {
    variants: {
      type: Object,
      required: true,
    },
  },

  created: function() {

    this.galleryImages = galleryImages.slice(0)

    var childAttributes = this.childAttributes,
      attributes = this.variants.attributes.slice(),
      index = attributes.length,
      attribute;

    while (index--) {
      attribute = attributes[index];

      attribute.options = [];

      if (index) {
        attribute.disabled = true;
      } else {
        this.fillSelect(attribute);
      }

      attribute = Object.assign(attribute, {
        childAttributes: childAttributes.slice(),
        prevAttribute: attributes[index - 1],
        nextAttribute: attributes[index + 1]
      });

      childAttributes.unshift(attribute);
    }
  },

  methods: {
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

      attribute.options = [{'id': '', 'label': this.variants.chooseText, 'products': []}];

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

    clearSelect: function (attribute) {
      if (! attribute)
        return;

      if (! attribute.swatch_type || attribute.swatch_type == '' || attribute.swatch_type == 'dropdown') {
        var element = document.getElementById("attribute_" + attribute.id);

        if (element) {
          element.selectedIndex = "0";
        }
      } else {
        var elements = document.getElementsByName('super_attribute[' + attribute.id + ']');

        var this_this = this;

        elements.forEach(function(element) {
          element.checked = false;
        })
      }
    },

    getAttributeOptions: function (attributeId) {
      var this_this = this,
        options;

      this.variants.attributes.forEach(function(attribute, index) {
        if (attribute.id == attributeId) {
          options = attribute.options;
        }
      })

      return options;
    },

    reloadPrice: function () {
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

        priceElement.innerHTML = this.variants.variant_prices[this.simpleProduct].final_price.formated_price;

        eventBus.$emit('configurable-variant-selected-event', this.simpleProduct)
      } else {
        priceLabelElement.style.display = 'inline-block';

        priceElement.innerHTML = this.variants.regular_price.formated_price;

        eventBus.$emit('configurable-variant-selected-event', 0)
      }
    },

    changeProductImages: function () {
      galleryImages.splice(0, galleryImages.length)

      this.galleryImages.forEach(function(image) {
        galleryImages.push(image)
      });

      if (this.simpleProduct) {
        this.variants.variant_images[this.simpleProduct].forEach(function(image) {
          galleryImages.unshift(image)
        });
      }
    },

    changeStock: function (productId) {
      var inStockElement = document.getElementById('in-stock');

      if (productId) {
        inStockElement.style.display= "block";
      } else {
        inStockElement.style.display= "none";
      }
    },
  }
}

</script>
