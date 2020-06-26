<template lang="html">
  <div class="wholesale-store-wrapper">
    <cp-tabs
      v-if="categories.length > 0"
     :items="categories"
     custom-class="cp-tabs-light"
     :callback="switchTables"></cp-tabs>
    <cp-table-controls
      :date-picker="false"
      :index-request="productParams"
      :resource-info="pagination"
      :get-records="getProducts"></cp-table-controls>
    <table class="cp-table-standard">
      <thead>
        <th>Image</th>
        <th>Product</th>
        <th>Price</th>
      </thead>
      <tbody v-for="(product, index) in products">
        <tr @click="showProduct[product.slug] = !showProduct[product.slug]">
          <td>
            <cp-product-gallery
              :alt="product.name"
              :default-image="getDefaultImage(product.images)"
              :images="product.images"></cp-product-gallery>
          </td>
          <td>{{ product.name }}</td>
          <td>
            <span v-if="product.price">{{ product.price | currency }}</span>
            <span v-else>n/a</span>
          </td>
        </tr>
        <tr v-if="showProduct[product.slug]">
          <td colspan="4">
            <cp-product-items
              :product-id="product.id"
              :product-min="product.min"
              :product-max="product.max"
              :inventoryUserPid="inventoryUserPid"
              :search-term="productParams.search_term"
              :cart-type="cartType"></cp-product-items>
          </td>
        </tr>
      </tbody>
    </table>
    <div class="align-center">
        <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
        <cp-pagination :pagination="pagination" :callback="getProducts"></cp-pagination>
    </div>
  </div>
</template>

<script>
const Inventory = require('../../resources/InventoryAPIv0.js')
const Auth = require('auth')

module.exports = {
  data () {
    return {
      products: [],
      my_products: [],
      loading: true,
      items: [],
      categories: [],
      pagination: { per_page: 15 },
      my_pagination: {per_page: 15},
      productParams: {
        available: 1,
        visibilities: [],
        categories: [],
        expands: ['product_images'],
        price: 'wholesale',
        search_term: '',
        sort_by: 'price',
        order: 'ASC',
        per_page: 15,
        page: 1
      },
      categoryChange: false,
      showProduct: {},
      showMyProduct: {}
    }
  },
  props: ['cartType', 'inventoryUserPid'],
  mounted () {
    this.getCategories()
    this.getProducts()
  },
  methods: {
    switchTables (tab) {
      this.categoryChange = true
      if (tab === 'All') {
        this.productParams.categories = []
      } else {
        for (var i = 0; i < this.categories.length; i++) {
          if (tab === this.categories[i].name) {
            this.productParams.categories = [this.categories[i].id]
            break
          }
        }
      }
      this.getProducts()
    },
    getCategories () {
      Inventory.getCategories()
        .then((response) => {
          if (!response.error) {
            var vm = this
            this.categories = response
            this.categories.unshift({ name: 'All', active: true })
            for (var i = 0; i < this.categories.length; i++) {
              window.Vue.set(this.categories[i], 'active', false)
            }
          }
        })
    },
    getProducts () {
      this.loading = true
      this.productParams.page = this.pagination.current_page
      if (this.categoryChange) {
        this.categoryChange = false
        this.productParams.page = 1
      }
      let request = JSON.parse(JSON.stringify(this.productParams))

      if (this.cartType === 'wholesale' || this.cartType === 'rep-transfer' || (this.cartType === 'custom-personal' && Auth.hasAnyRole('Rep'))) {
        request.price = 'wholesale'
      } else {
        request.price = 'retail'
      }

      switch(this.cartType) {
          case "wholesale":
              request.visibilities = [5]
              break
          case "affiliate":
          case "custom-affiliate":
              request.visibilities = [2]
              break
          case "custom-retail":
          case "custom-personal":
          case "custom-corp":
          case "rep-transfer":
              request.visibilities = []
              break
          default:
              request.visibilities = [0] // Show nothing when type unknown
      }

      request.user_pid = this.inventoryUserPid

      Inventory.getProducts(request)
        .then((response) => {
          this.pagination = response
          this.products = response.data
          for (var j = 0; j < this.products.length; j++) {
            window.Vue.set(this.showProduct, this.products[j].slug, false)
          }
          this.loading = false
        })
    },
    getDefaultImage (imgs) {
      let images = JSON.parse(JSON.stringify(imgs))
      let reg = /(?:\.([^.]+))?$/
      if (images.length > 0) {
        let image = images[0].url
        let ext = reg.exec(image)[1]
        image = image.replace(/\.[^/.]+$/, '')
        return image + '-url_sm.' + ext
      }
      return ''
    }
  },
  components: {
    CpTabs: require('../../cp-components-common/navigation/CpTabs.vue'),
    CpTableControls: require('../../cp-components-common/tables/CpTableControls.vue'),
    CpProductItems: require('../store/CpProductItems.vue'),
    CpProductGallery: require('../store/CpProductGallery.vue')
  }
}
</script>

<style lang="scss">
.wholesale-store-wrapper {

}
</style>
