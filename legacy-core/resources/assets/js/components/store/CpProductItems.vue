<template lang="html">
  <div class="wholesale-product-wrapper">
    <section class="description-and-carausel">
      <section class="col pointer" v-if="selectedVariant.images && selectedVariant.images.length > 0">
        <img
          :src="selectedVariant.images[0].url | imageSize('url_lg')"
          style="width: 100%;"
          @click="mergeAllVariantImages(), showVariantImageModel = true">
          <cp-dialog :open="showVariantImageModel" @close="showVariantImageModel = false">
            <h2 slot="header">{{ product.name }}</h2>
            <template slot="content">
              <cp-carousel
              v-if="!loading"
              :default-image="variantImagesForModel[0]"
              :variant-label="product.variant_label"
              :images="variantImagesForModel"></cp-carousel>
            </template>
            </cp-dialog>
      </section>
      <section class="col">
          <h4>{{ selectedVariant.name }}</h4>
          <p v-if="product.long_description">
            {{ product.long_description }}
          </p>
          <p v-if="selectedVariant.description">
            {{ selectedVariant.description }}
          </p>
      </section>
    </section>
    <!-- <section> -->
      <cp-variant-multi-carousel
        v-if="!loading && variants.length > 1"
       :variants="variants"
       :select-variant="selectVariant"
       :active-variant="activeVariant"></cp-variant-multi-carousel>
    <!-- </section> -->
    </section>
    <table v-if="!loading" class="item-table">
      <th>{{ selectedVariant.option_label }}</th>
      <th>Price</th>
      <th>Quantity</th>
      <th>Total</th>
      <tr v-for="(item, index) in items">
        <td>{{ item.option }}</td>
        <td>
          <span v-if="item.wholesale_price && orderType !== 'custom'">{{ item.wholesale_price | currency }}</span>
          <span v-if="item.retail_price && orderType === 'custom'">{{ item.retail_price | currency }}</span>
          <span v-if="!item.retail_price && !item.wholesale_price">n/a</span>
        </td>
        <td  id="quantity">
          <cp-input
            type="number"
            v-model="item.quantity"
            :error="itemValidationError[item.id]"></cp-input>
            <span id='low_invetory' v-if="checkLowInventory(item.quantity_available)">Low Inventory</span>
        </td>
        <td v-if="orderType !== 'custom'">
            <span v-if="item.wholesale_price">{{ (item.wholesale_price * item.quantity) | currency }}</span>
            <span v-else>$0.00</span>
        </td>
        <td v-if="orderType === 'custom'">
            <span v-if="item.retail_price">{{ (item.retail_price * item.quantity) | currency }}</span>
            <span v-else>$0.00</span>
        </td>
      </tr>
      <tr>
        <td></td>
        <td></td>
        <td></td>
        <td><span v-if="total">{{ total | currency }}</span></td>
      </tr>
    </table>
    <img v-if="loading" class="loading" :src="$getGlobal('loading_icon').value">
    <p v-if="(productMax || productMin) && !loading && orderType !== 'custom'">
      <span>Please Note:</span>
      <span v-if="productMin > 0">Minimum purchase quantity per item is {{ productMin }}.</span>
      <span v-if="productMax > 0">Maximum purchase quantity per item is {{ productMax }}.</span>
    </p>
    <button v-if="!loading && !addingToCart" class="cp-button-standard add-to-cart" @click="addToCart(items)">Add To Cart</button>
    <button v-if="!loading && addingToCart" class="cp-button-standard add-to-cart" disabled>Adding To Cart...</button>
  </div>
</template>

<script>
const Inventory = require('../../resources/InventoryAPIv0.js')
const Checkout = require('../../resources/CheckoutAPIv0.js')
const CartUtility = require('../../libraries/CartUtility.js')
const _ = require('lodash')

module.exports = {
  data () {
    return {
      activeVariant: [],
      selectedVariant: { images: [] }, // needs a default variant?
      addingToCart: false,
      items: [],
      loading: true,
      itemValidationError: {},
      product: {},
      variants: [],
      showVariantImageModel: false,
      variantImagesForModel: [],
      variantImages: [],
      loadingImages: true,
      orderType: null
    }
  },
  props: ['productId', 'productMin', 'productMax', 'inventoryUserPid', 'cartType', 'searchTerm'],
  mounted () {
    this.setOrderType()
    this.getProductWithVariants()
  },
  computed: {
    total () {
      let total = 0
      for (var i = 0; i < this.items.length; i++) {
        if (this.orderType === 'custom') {
          total = total + this.items[i].retail_price * this.items[i].quantity
        } else {
          total = total + this.items[i].wholesale_price * this.items[i].quantity
        }
      }
      return total
    }
  },
  methods: {
    setOrderType () {
      switch (this.cartType) {
        case 'wholesale':
          this.orderType = 'purchase'
          break
        case 'rep-transfer':
          this.orderType = 'rep-transfer'
          break
        default:
          this.orderType = 'custom'
      }
    },
    // this loops through variants to merge all images into one array
    // it also attaches the variant name to the image object
    mergeAllVariantImages () {
      this.variantImagesForModel = []
      for (var i = 0; i < this.variants.length; i++) {
        if (this.variants[i].images && this.variants[i].images.length > 0) {
          for (var j = 0; j < this.variants[i].images.length; j++) {
            this.variants[i].images[j].name = this.variants[i].name
            this.variants[i].images[j].available = this.variants[i].items
            this.variants[i].images[j].price = this.variants[i].price
            this.variants[i].images[j].description = this.variants[i].description
          }
        }
        this.variantImagesForModel = this.variantImagesForModel.concat(this.variants[i].images)
      }
    },
    selectVariant (variant, index) {
      for (let i = 0; i < this.activeVariant.length; i++) {
        this.activeVariant[i] = false
        this.$set(this.activeVariant, i, false)
      }
      this.$set(this.activeVariant, index, true)
      this.selectedVariant = variant
      this.items = variant.items
      for (let i = 0; i < this.items.length; i++) {
        this.$set(this.items[i], 'quantity', 0)
      }
    },
    getProductWithVariants () {
      var vm = this
      vm.loading = true
      let productParams = {
        expands: ['variants', 'variant_images', 'product_images'],
        available: 1,
        user_pid: this.inventoryUserPid,
        price: 'retail',
        search_term: this.searchTerm,
        visibilities: [0]
      }
      if (this.orderType === 'purchase' || this.cartType === 'rep-transfer') {
        productParams.price = 'wholesale'
      }
      if (this.cartType === 'custom_corp') {
        productParams.price = 'retail'
      }
      switch(this.cartType) {
          case "wholesale":
              productParams.visibilities = [5]
              break
          case "affiliate":
          case "custom-affiliate":
              productParams.visibilities = [2]
              break
          case "custom-retail":
          case "custom-personal":
          case "custom-corp":
          case "rep-transfer":
              productParams.visibilities = []
              break
          default:
              productParams.visibilities = [0] // Nothing visible when type unknown
      }
      Inventory.getProduct(productParams, vm.productId)
        .then((response) => {
          if (!response.error) {
            vm.product = response
            if (vm.product.variants.length > 0 && vm.product.variants[0].images) {
              vm.selectedVariant = vm.product.variants[0]
              vm.variants = vm.product.variants
              // if no images for a variant show product images
              if (!vm.selectedVariant.images) {
                vm.selectedVariant.images = vm.product.images
              }
              // set items and make sure they all have a quantity of zero
              vm.items = vm.variants[0].items
              for (var i = 0; i < vm.items.length; i++) {
                vm.$set(vm.items[i], 'quantity', 0)
              }
            }
          }
          vm.loading = false
        })
    },
    checkQuantity (items) {
      this.itemValidationError = {}
      for (var i = 0; i < items.length; i++) {
        if (items[i].quantity !== 0 && items[i].wholesale_price === null) {
          this.itemValidationError[items[i].id] = ['Price not available.']
        }
        if (items[i].quantity !== 0 && items[i].quantity !== '0' && !parseInt(items[i].quantity)) {
          this.itemValidationError[items[i].id] = ['Not a valid number.']
        }
        if (items[i].quantity > items[i].quantity_available) {
          this.itemValidationError[items[i].id] = ['Only ' + items[i].quantity_available + ' available of this item.']
        }
      }
      if (Object.keys(this.itemValidationError).length == 0) {
        return true
      } else {
        return false
      }
    },
    addToCart (items) {
      let checkQuantity = this.checkQuantity(items)
      let pid = null
      if (!checkQuantity) {
        return
      }

      let addItems = []
      for (var i = 0; i < items.length; i++) {
        if (items[i].quantity > 0) {
          addItems.push({
            quantity: parseInt(items[i].quantity),
            item_id: items[i].id,
            cart_type: this.cartType
          })
        }
      }
      this.addingToCart = true
      console.log('Add Items: ', addItems)

      CartUtility.getCartPid(this.cartType).then(res => {
        pid = res

        Checkout.patchCartLines(addItems, pid).then(response => {
          this.addingToCart = false
          if (response.error && response.code === 422) {
            this.itemValidationError = response.message
          }
          if (!response.error) {
            this.$emit('added-to-cart', true)
            this.$toast('Successfully added items to cart', {dismiss: true})
          }
        }).catch(err => {
          console.error(err)
          this.addingToCart = false
        })
      }).catch(err => {
        console.error(new Error(err.message))
        this.addingToCart = false
      })
    },
    checkLowInventory (quantity) {
      if (
        this.cartType === 'wholesale'
        && this.$getGlobal('wholesale_low_inventory').show
        && this.$getGlobal('wholesale_low_inventory').value > quantity
      ) {
        return true
      } else if (
        (this.cartType === 'custom-affiliate' || this.cartType === 'custom-corp')
        && this.$getGlobal('retail_low_inventory').show
        && this.$getGlobal('retail_low_inventory').value > quantity
      ) {
        // Corp inventory only for retail check, not rep sales (2018-12-03 per Jordan)
        return true
      }
      return false
    }
  },
  components: {
    CpVariantMultiCarousel: require('./CpVariantMultiCarousel.vue'),
    CpCarousel: require('../../cp-components-common/images/CpCarousel.vue'),
    CpInput: require('../../cp-components-common/inputs/CpInput.vue')
  }
}
</script>

<style lang="scss">
.wholesale-product-wrapper {
  .cp-modal-standard {
    .cp-modal-body-full {
      overflow-y: auto;
    }
  }
  .loading {
    display: block;
    margin: 0 auto;
  }
  .cp-validation-errors {
    text-align: center !important;
  }
  .description-and-carausel {
    display: flex;
    .col {
      flex: 1;
      padding: 15px;
    }
    .col:first-child {
      max-width: 400px;
    }
  }
  .variant-selection-section {
    .active {
      border: 1px solid grey;
    }
    .variant-image {
      cursor: pointer;
      &:hover {
        border: 1px solid grey;
      }
    }
  }
  td {
  text-align: left !important;
}
th {
  text-align: left !important;
}
#low_invetory {
  padding-left: 3px;
  color: tomato;
}
#quantity {
  width: 33%;
}

}
</style>
