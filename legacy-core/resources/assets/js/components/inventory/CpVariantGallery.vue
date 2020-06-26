<template>
  <div id="variant-gallery">
    <div v-if="!loading && variantsCurrentPage" class="cp-accordion" v-for="(variant, index) in variantsCurrentPage" :key="index">
      <div class="header cp-accordion-head" @click="setOpen(index)">
        <div v-if="variant.images[0]" class="col">
          <img :src="variant.images[0].url | imageSize('url_xxs')" alt="">
        </div>
        <div v-else class="col">
          <div class="image-holder"></div>
        </div>
        <div class="col">
          <h4>{{variant.name}}</h4>
        </div>
      </div>
      <div class="cp-accordion-body" v-if="showId === index">
        <cp-item-gallery 
        :variant-id="variant.id" 
        :option-label="variant.option_label" 
        :owner-id="ownerId" 
        :user-id="userId">
        </cp-item-gallery>
      </div>
    </div>
    <div class="align-center">
      <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
      <cp-array-pagination  v-if="!loading" :per-page="5" :data="variants" @current-page="function (val) { variantsCurrentPage = val; showId = null }"></cp-array-pagination>
    </div>
  </div>
</template>

<script>
const Inventory = require('../../resources/InventoryAPIv0.js')
const Auth = require('auth')

module.exports = {
  data: () => ({
    showId: null,
    loading: false,
    showVariant: {},
    variantsCurrentPage: [],
    variants: [],
    indexRequest: {
      expands: ['variants', 'variant_images'],
      per_page: 15,
      search_term: '',
      page: 1
    }
  }),
  mounted () {
    this.getVariants()
  },
  methods: {
    getVariants () {
      this.loading = true
      if (Auth.hasAnyRole('Superadmin', 'Admin')) {
        this.indexRequest.user_id = 1
      }
      if (this.userId !== undefined) {
        this.indexRequest.user_id = this.userId
      } else {
        this.indexRequest.user_id = Auth.getOwnerId()
      }
      this.indexRequest.page = this.pagination.current_page
      this.indexRequest.search_term = this.searchTerm
      Inventory.getProduct(this.indexRequest, this.productId)
        .then((response) => {
          this.loading = false
          if (response.error) {
            return this.$toast(response.message)
          }
          this.variants = response.variants
          this.checkVariants()
        })
    },
    checkVariants () {
      if (this.variants.length === 1) {
        this.showId = 0
      }
    },
    setOpen (index) {
      if (index === this.showId) {
        this.showId = null
      } else {
        this.showId = index
      }
    }
  },
  props: ['productId', 'ownerId', 'userId', 'pagination', 'searchTerm'],
  components: {
    CpItemGallery: require('./CpItemGallery.vue')
  }
}
</script>

<style lang="scss" scoped>
  @import "resources/assets/sass/var.scss";
  #variant-gallery {
    .page-button-wrapper {
      margin-top: 10px;
    }
    .header {
      display: flex;
      width: 100%;
      padding: 5px;
      .col {
        padding: 3px;
        flex: 1;
      }
    }
    .cp-accordion {
      border-bottom: 1px solid $cp-lightGrey;
      .cp-accordion-head {
        border-bottom: none;
      }
    }
    .cp-accordion:last-child {
      border-bottom: none;
    }
    .cp-accordion:nth-child(odd) {
      background: white;
    }
    .image-holder {
      width: 50px;
      height: 50px;
    }
  }
</style>
