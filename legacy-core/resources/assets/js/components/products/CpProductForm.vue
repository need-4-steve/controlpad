<template lang="html">
  <div v-if="!loading" class="product-form-wrapper">
    <div class="cp-form-standard cp-box-standard">
        <div class="product-form-grid">
          <cp-input
            class="cp-cell"
            label="Name"
            type="text"
            :error="validationErrors['name']"
            v-model="product.name"></cp-input>
          <cp-input
            class="cp-cell"
            label="Variant Label"
            type="text"
            :error="validationErrors['variant_label']"
            v-model="product.variant_label"></cp-input>
          <cp-select
            class="cp-cell cp-select"
            label="Type"
            :options="productTypes"
            :key-value="{ name: 'name', value: 'id' }"
            :error="validationErrors['type_id']"
            @input="(value) => { product.resellable = (value == '1') }"
            v-model="product.type_id"></cp-select>
          <cp-select
            class="cp-cell cp-select"
            label="Resellable"
            :options="resellOptions"
            :error="validationErrors['resellable']"
            :disabled="product.type_id != 1"
            v-model="product.resellable"
            @input="(value) => { product.resellable = value == 'true' }">
          </cp-select>
        </div>
      <cp-photo-upload
        @new-media="function (val) { newImages = val }"
        :drop-zone-id="'productMedia'"
        :media="newImages"
        :current-media="product.images"
        :validation-errors="validationErrors"></cp-photo-upload>
      <cp-category-assignment
        v-if="product"
        :added-categories="product.categories"
        @categories="function (val){ product.categories = val }"
        ></cp-category-assignment>
        <cp-tax-class-assignment
        v-if="$getGlobal('tax_classes_required').show || product.tax_class"
        :tax-class="product.tax_class"
        v-model="product.tax_class"
        v-model.trim="product.tax_class"
        :validation-errors="validationErrors"></cp-tax-class-assignment>
      <cp-min-max-assignment
        @min="function (val) { product.min = Number(val) }"
        @max="function (val) { product.max = Number(val) }"
        :min="product.min"
        :max="product.max"
        :validation-errors="validationErrors"></cp-min-max-assignment>
      <!-- DESCRIPTIONS -->
          <div class="two-column-grid">
            <div class="col">
              <cp-description-assignment
                title="BRIEF DESCRIPTION"
                :description="product.short_description"
                :validation-errors="validationErrors"
                v-model="product.short_description"></cp-description-assignment>
            </div>
            <div class="col">
              <cp-description-assignment
                title="LONG DESCRIPTION"
                :description="product.long_description"
                :validation-errors="validationErrors"
                v-model="product.long_description"></cp-description-assignment>
            </div>
          </div>
        <cp-visibility-assignment-beta
         :selected-visibilities="product.visibilities"
         :validation-errors="validationErrors"
         @selected-visibilities="function (val) { product.visibilities = val } "></cp-visibility-assignment-beta>

         <div class="button-box">
           <button v-if="!update" class="cp-button-standard product-create-button" @click="createProduct()" :disabled="disableSubmit">Create</button>
           <button v-if="update" class="cp-button-standard product-create-button" @click="updateProduct()" :disabled="disableSubmit">Update</button>
         </div>
         <div v-if="productCreated">
            <cp-tabs
              :items="[
                { name: 'Add Variant', active: true },
                { name: 'Add Item', active: false }
              ]"
              :callback="changeTabs">
             </cp-tabs>
              <cp-variant-form
              :product-id="product.id"
              v-show="addTab === 'variant'"
              @added-variant='getProduct'></cp-variant-form>
              <cp-item-form-beta
              v-if="addTab === 'item'"
              :variants="product.variants"
              @added-variant='getProduct'>
              </cp-item-form-beta>
          </div>
          <div v-if="productCreated">
            <cp-variants
            v-if="product.variants"
            :added-items="product.variants"
            @update-product="getProduct">
            </cp-variants>
          </div>
    </div>
      <div class="align-center">
        <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
      </div>
  </div>
</template>

<script>
const Inventory = require('../../resources/InventoryAPIv0.js')
const Auth = require('auth')

const slugRegex = /(\ba\b|\ban\b|\bit\b|\bthe\b|\band\b|[\s,.&?!@#$%^*()+=~`:;'"[{\]}\\/|<>-_])+/g
const slugTrimRegex = /(^-+|-+$)/g

module.exports = {
  name: 'CpProductForm',
  routing: [
    {
      name: 'site.CpProductFormEdit',
      path: 'products/:id/edit',
      meta: {
        title: 'Edit Product'
      },
      props: true
    },
    {
      name: 'site.CpProductFormCreate',
      path: 'products/create',
      meta: {
        title: 'Create Product'
      },
      props: true
    }
  ],
  data () {
    return {
      productCreated: false,
      loading: false,
      update: false,
      addTab: 'variant',
      productTypes: [
        {
          id: 1,
          name: 'Product'
        },
        {
          id: 6,
          name: 'Business Tools'
        }
      ],
      resellOptions: [
        { name: 'Yes', value: true},
        { name: 'No', value: false}
      ],
      validationErrors: {},
      disableSubmit: false,
      loadingTypes: true,
      newImages: [],
      product: {
        name: '',
        type_id: null,
        min: null,
        max: null,
        items: [],
        images: [],
        expands: ['variants', 'categories', 'product_images', 'variant_images', 'visibilities'],
        visibilities: [],
        categories: [],
        slug: '',
        tax_class: '',
        long_description: '',
        short_description: '',
        id: null,
        variants: []
      }
    }
  },
  props: {
    productId: {
      default () {
        return this.$pathParameter()
      }
    }
  },
  mounted () {
    this.checkIfUpdate()
    if (Auth.hasAnyRole('Admin', 'Superadmin')) {
      this.product.corp = 1
    }
  },
  computed: {
    slug () {
      return this.product.name
        .toLowerCase()
        .replace(slugRegex, '-')
        .replace(slugTrimRegex, '')
    }
  },
  methods: {
    changeTabs (tab) {
      if (tab === 'Add Variant') {
        this.addTab = 'variant'
      } else if (tab === 'Add Item') {
        this.addTab = 'item'
      }
    },
    checkIfUpdate () {
      if (this.productId !== undefined) {
        this.loading = true
        this.productCreated = true
        this.update = true
        return this.getProduct(parseInt(this.productId))
      }
      this.loading = false
    },
    createProduct () {
      if (this.$getGlobal('tax_classes_required').show && this.product.tax_class === '') {
        this.validationErrors = {
          tax_class: ['the tax class field is required']
        }
        return this.$toast('Tax class is required')
      }
      this.validationErrors = {}
      this.product.images = this.newImages
      this.disableSubmit = true
      this.product.slug = this.slug
      this.setMinAndMaxToNull()
      Inventory.createProduct(this.product)
        .then((response) => {
          this.disableSubmit = false
          if (response.error) {
            this.validationErrors = response.message
            return
          }
          this.product = response
          this.$toast('Product successfully created.')
          this.disableSubmit = false
          this.update = true
          this.productCreated = true
          // window.location.href = '/inventory'
        })
    },
    // set min and max on a product to null if set to 0
    setMinAndMaxToNull () {
      if (this.product.min === 0) {
        this.product.min = null
      }
      if (this.product.max === 0) {
        this.product.max = null
      }
    },
    getProduct (id) {
      this.loading = true
      if (id === null || id === undefined) {
        id = this.product.id
      }
      let params = {
        expands: ['variants', 'categories', 'product_images', 'variant_images', 'visibilities']
      }
      Inventory.getProduct(params, id)
        .then((response) => {
          if (response.error) {
            this.validationErrors = response.message
            return
          }
          this.product = response
          this.loading = false
        })
    },
    updateProduct () {
      this.validationErrors = {}
      if (this.$getGlobal('tax_classes_required').show && this.product.tax_class === '') {
        this.validationErrors = {
          tax_class: ['the tax class field is required']
        }
        return this.$toast('Tax class is required', { error: true })
      }
      this.disableSubmit = true
      this.product.images = this.newImages
      this.product.slug = this.slug
      this.setMinAndMaxToNull()
      Inventory.updateProduct(this.product, this.product.id)
        .then((response) => {
          this.disableSubmit = false
          if (response.error) {
            this.validationErrors = response.message

            return
          }
          this.$toast('Product successfully updated.')
        })
    }
  }
}
</script>

<style lang="scss">
@import "resources/assets/sass/var.scss";

.product-form-wrapper {
  hr {
    margin-top: 5px;
  }
  .product-form-grid {
      box-sizing: border-box;
      max-width: $maxPageWidth;
      margin: 0 auto;
      display: flex;
      flex-wrap: wrap;
      .cp-select {
        .cp-select-standard {
          &:after {
            top: 14px;
          }
          select {
            height: 100%;
          }
        height: 36px;
        margin-top: 3px;
        border-radius: 5px;
        }
        label {
          margin-top: 0px;
        }
      }
      .cp-cell {
          box-sizing: border-box;
          width: calc(100% / 4);
          text-align: left;
          padding: 5px;
          padding-bottom: 10px;
      }
  }
  .button-box{
    width: 100%;
    height: 80px;
  }
  .product-create-button {
    float: right;
    margin: 5px;
  }
  .two-column-grid {
    display: flex;
      .col {
        flex: 1;
        padding-right: 5px;
        &:first-child {
          padding-right: 5px;
        }
        &:last-child {
          padding-left: 5px;
        }
      }
      .right {
        float: right;
        select {
          margin: 5px 0px;
          width: 100%;
        }
      }
  }
}
</style>
