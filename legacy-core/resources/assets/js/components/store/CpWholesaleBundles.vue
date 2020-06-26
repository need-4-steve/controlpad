<template lang="html">
  <div class="wholesale-bundles-wrapper">
    <cp-table-controls
      :date-picker="false"
      :search-box="false"
      :index-request="bundleParams"
      :resource-info="pagination"
      :get-records="getBundles"></cp-table-controls>
    <table class="cp-table-standard">
      <thead>
        <th>Image</th>
        <th>Product</th>
        <th>Price</th>
      </thead>
      <tbody v-for="(bundle, index) in bundles" v-if="!loading">
        <tr @click="showHideBundle(bundle.id)">
          <td>
            <cp-product-gallery
              :alt="bundle.name"
              :default-image="bundle.images.length > 0 ? bundle.images[0].url : ''"
              :images="bundle.media"></cp-product-gallery>
          </td>
          <td>{{ bundle.name }}</td>
          <td>${{ parseFloat(bundle.wholesale_price).toFixed(2) }}</td>
        </tr>
        <tr v-if="showBundle[bundle.id]">
          <td colspan="4">
            <cp-bundle-items v-if="bundle.id" :bundle="bundle" @added-to-cart="function (val) { showBundle[bundle.id] = false }" v-bind="{cartType}" :key="cartType"></cp-bundle-items>
          </td>
        </tr>
      </tbody>
    </table>
    <div class="align-center">
        <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
        <cp-pagination :pagination="pagination" :callback="getBundles" :offset="2"></cp-pagination>
    </div>
  </div>
</template>

<script>
const Inventory = require('../../resources/InventoryAPIv0.js')

module.exports = {
  data () {
    return {
      bundles: [],
      showBundle: {},
      pagination: {},
      loading: true,
      bundleParams: {
        available: 1,
        search_term: '',
        column: 'name',
        order: 'asc',
        visibilities: [5],
        expands: ['bundle_images', 'items'],
        page: 1,
        per_page: 15
      }
    }
  },
  props: ['cartType'],
  created () {
    this.getBundles()
    console.log('Bundle component cart type is ', this.cartType)
  },
  methods: {
    showHideBundle (id) {
      this.showBundle[id] = !this.showBundle[id]
    },
    getBundles () {
      this.loading = true
      this.bundleParams.page = this.pagination.current_page
      this.bundleParams.user_id = 1
      Inventory.getBundles(this.bundleParams)
        .then((response) => {
          this.loading = false
          if (!response.error) {
            this.bundles = response.data
            this.pagination = response
            for (var i = 0; i < this.bundles.length; i++) {
              window.Vue.set(this.showBundle, this.bundles[i].id, false)
            }
          }
        })
    },
  },
  components: {
    CpBundleItems: require('../store/CpBundleItems.vue'),
    CpProductGallery: require('../store/CpProductGallery.vue'),
    CpTableControls: require('../../cp-components-common/tables/CpTableControls.vue')
  }
}
</script>

<style lang="scss">
</style>
