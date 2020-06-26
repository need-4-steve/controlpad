<template lang="html">
  <div class="bundle-form-wrapper" v-if="!loadingBundle">
    <div class="cp-form-standard">
      <div class="cp-box-standard two-column-grid">
        <div class="col">
          <cp-input
            label="Pack Name"
            type="text"
            :error="validationErrors['name']"
            v-model="bundle.name"></cp-input>
        </div>
        <div class="col right">
          <!-- needs be empty? -->
        </div>
      </div>
      <cp-photos
        @new-media="function (val) { newImages = val }"
        :media="newImages"
        :current-media="bundle.images"
        :validation-errors="validationErrors"></cp-photos>
      <cp-category-assignment
        :added-categories="bundle.categories"
        @category-ids="(c) => categoryIds = c"
        ></cp-category-assignment>
      <cp-tax-class-assignment
        v-if="$getGlobal('tax_classes_required').show"
        :tax-class="bundle.tax_class"
        v-model="bundle.tax_class"
        :validation-errors="validationErrors"></cp-tax-class-assignment>
      <cp-variant-assignment
        :bundle="bundle"
        :validation-errors="validationErrors"></cp-variant-assignment>
      <!-- DESCRIPTIONS -->
      <div class="two-column-grid">
        <div class="col">
          <cp-description-assignment
            title="BRIEF DESCRIPTION"
            :description="bundle.short_description"
            :validation-errors="validationErrors"
            v-model="bundle.short_description"></cp-description-assignment>
        </div>
        <div class="col">
          <cp-description-assignment
            title="LONG DESCRIPTION"
            :description="bundle.long_description"
            :validation-errors="validationErrors"
            v-model="bundle.long_description"></cp-description-assignment>
        </div>
      </div>
      <cp-search-terms
        :tags="bundle.tags"
        :validation-errors="validationErrors"
        @new-tags="function (val) { updatedTags = val }"></cp-search-terms>
      <cp-visibility-assignment
       :selected-visibilities="bundle.visibilities"
       :validation-errors="validationErrors"
       :starter-kit="starterKit"
       @selected-visibilities="(v) => bundle.visibilities = v"></cp-visibility-assignment>
      <button v-if="!update" class="cp-button-standard product-create-button" type="button" @click="createBundle()" :disabled="disableSubmit">Create</button>
      <button v-if="update" class="cp-button-standard product-create-button" type="button" @click="updateBundle()" :disabled="disableSubmit">Update</button>
    </div>
  </div>
</template>

<script>
const Auth = require('auth')
const Inventory = require('../../resources/InventoryAPIv0.js')

module.exports = {
  name: 'CpBundleForm',
  routing: [
    {
      name: 'site.CpBundleForm',
      path: 'bundles/create',
      meta: {
        title: 'Create Pack'
      },
      props: true
    },
    {
      name: 'site.CpBundleEdit',
      path: 'bundles/:id/edit',
      meta: {
        title: 'Edit Pack'
      },
      props: true
    }
  ],
  data () {
    return {
      validationErrors: {},
      disableSubmit: false,
      newImages: [],
      categoryIds: [],
      update: false,
      updatedTags: [],
      updatedVisibilities: [],
      loadingBundle: true,
      loadingTypes: true,
      bundle: {
        name: '',
        type_id: null,
        min: null,
        max: null,
        items: [],
        images: [],
        roles: [],
        categories: [],
        slug: '',
        tags: [],
        tax_class: '',
        long_description: '',
        short_description: '',
        duration: '',
        wholesale_price: 0.00,
        visibilities: []
      },
      itemMap: {},
      starterKit: { show: true, value: false }
    }
  },
  mounted () {
    var bundleId = this.$pathParameter()
    if (bundleId) {
      this.disableSubmit = true
      this.update = true
      this.getBundle(bundleId)
    } else {
      this.loadingBundle = false
    }
  },
  computed: {
    slug () {
      if (!this.bundle.name) {
        return null
      }
      let text = this.bundle.name
      text = text.toLowerCase()
      text = text.replace(/ a /g, '-')
      text = text.replace(/ an /g, '-')
      text = text.replace(/ it /g, '-')
      text = text.replace(/ the /g, '-')
      text = text.replace(/\ and /g, '-')
      text = text.replace(/\ /g, '-')
      text = text.replace(/\,/g, '-')
      text = text.replace(/\./g, '-')
      text = text.replace(/\&/g, '-')
      text = text.replace(/\?/g, '-')
      text = text.replace(/\!/g, '-')
      text = text.replace(/\@/g, '-')
      text = text.replace(/\#/g, '-')
      text = text.replace(/\$/g, '-')
      text = text.replace(/\%/g, '-')
      text = text.replace(/\^/g, '-')
      text = text.replace(/\*/g, '-')
      text = text.replace(/\(/g, '-')
      text = text.replace(/\)/g, '-')
      text = text.replace(/\+/g, '-')
      text = text.replace(/\=/g, '-')
      text = text.replace(/\~/g, '-')
      text = text.replace(/\`/g, '-')
      text = text.replace(/\:/g, '-')
      text = text.replace(/\;/g, '-')
      text = text.replace(/\'/g, '-')
      text = text.replace(/\'/g, '-')
      text = text.replace(/\[/g, '-')
      text = text.replace(/\{/g, '-')
      text = text.replace(/\]/g, '-')
      text = text.replace(/\}/g, '-')
      text = text.replace(/\\/g, '-')
      text = text.replace(/\|/g, '-')
      text = text.replace(/\</g, '-')
      text = text.replace(/\>/g, '-')
      text = text.replace(/\--/g, '')
      text = text.replace(/\__/g, '')
      text = text.replace(/\_-/g, '')
      text = text.replace(/\-_/g, '')
      return text
    }
  },
  methods: {
    prepareRequest () {
      this.disableSubmit = true
      let request = JSON.parse(JSON.stringify(this.bundle))
      request.slug = this.slug
      request.images = []
      this.newImages.forEach(i => request.images.push({id: i}))
      request.categories = []
      this.categoryIds.forEach(c => request.categories.push({id: c}))
      request.tags = this.updatedTags
      if (this.starterKit.show) {
        request.starter_kit = 0
        this.bundle.visibilities.forEach(v => {
          if (v.id == 4) {
            request.starter_kit = 1
          }
        })
      }
      request.type_id = 1
      return request
    },
    getBundle (id) {
      Inventory.getBundle({expands: ['items', 'visibilities', 'categories', 'bundle_images']}, id)
        .then((response) => {
          if (!response || response.error) {
            this.$toast('Bundle not found')
            return
          }
          this.bundle = response
          this.starterKit.value = !!this.bundle.starter_kit
          // Load variants for bundle
          this.disableSubmit = false
          this.loadingBundle = false
        })
    },
    createBundle () {
      let request = this.prepareRequest()
      request.user_id = Auth.getOwnerId()
      Inventory.createBundle(request)
        .then((response) => {
          this.disableSubmit = false
          if (response.error) {
            this.validationErrors = response.message
            return
          }
          this.$toast('Pack successfully created.')
          window.location.href = '/inventory'
        })
    },
    updateBundle () {
      let request = this.prepareRequest()
      Inventory.updateBundle(request, request.id)
        .then((response) => {
          this.disableSubmit = false
          if (response.error) {
            this.validationErrors = response.message
            return
          }
          this.$toast('Pack successfully updated.')
        })
    }
  },
  components: {
    CpInput: require('../../cp-components-common/inputs/CpInput.vue'),
    CpSelect: require('../../cp-components-common/inputs/CpSelect.vue'),
    CpPhotos: require('../../cp-components-common/temp-dependencies/CpPhotos.vue'),
    CpCategoryAssignment: require('../products/CpCategoryAssignment.vue'),
    CpTaxClassAssignment: require('../products/CpTaxClassAssignment.vue'),
    CpVariantAssignment: require('../products/CpVariantAssignment.vue'),
    CpDescriptionAssignment: require('../products/CpDescriptionAssignment.vue'),
    CpSearchTerms: require('../products/CpSearchTerms.vue'),
    CpVisibilityAssignment: require('../products/CpVisibilityAssignment.vue')
  }
}
</script>

<style lang="scss">
.bundle-form-wrapper {
  hr {
    margin-top: 5px;
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
