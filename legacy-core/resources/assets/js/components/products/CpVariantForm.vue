<template>
  <div id="cp-variant-form">
    <div class="variant-form-wrapper">
      <div class="cp-form-standard">
            <cp-input
              label="Name"
              type="text"
              :error="validationErrors['name']"
              :value="variant.name"
              v-model="variant.name"></cp-input>
            <div class="min-max-wrapper">
              <div class="min-max-col padding-right">
                <cp-input
                label="Min"
                type="number"
                :error="validationErrors['min']"
                v-model="variant.min"></cp-input>
              </div>
              <div class="min-max-col">
                <cp-input
                label="Max"
                type="number"
                :error="validationErrors['max']"
                v-model="variant.max"></cp-input>
              </div>
            </div>
          <cp-input
            label="Option Label"
            type="text"
            :error="validationErrors['option_label']"
            :value="variant.option_label"
            v-model="variant.option_label"></cp-input>
        </div>
          <div>
            <cp-photo-upload
              @new-media="function (val) { newImages = val }"
              :drop-zone-id="'variantImages' + itemIndex"
              :media="newImages"
              :current-media="variant.images"
              :validation-errors="validationErrors"></cp-photo-upload>
          </div>
          <cp-description-assignment
            title="DESCRIPTION"
            :description="variant.description"
            :validation-errors="validationErrors"
            v-model="variant.description"></cp-description-assignment>
        </div>
        <div class="variant-form-submit-buttons">
          <button v-if="!variant.id" class="cp-button-standard" @click="createVariant()" :disabled="disableSubmit">Add Variant</button>
          <button v-if="variant.id" class="cp-button-standard" @click="updateVariant()" :disabled="disableSubmit">Update Variant</button>
        </div>
  </div>
</template>
<script>
const Inventory = require('../../resources/InventoryAPIv0.js')

module.exports = {
  data () {
    return {
      newImages: [],
      disableSubmit: false,
      validationErrors: {}
    }
  },
  props: {
    itemIndex: '',
    productId: {
      type: Number
    },
    variant: {
      type: Object,
      default () {
        return {
        }
      }
    }
  },
  methods: {
    // set min and max on a variant to null if set to 0
    setMinAndMaxToNull () {
      if (this.variant.min === 0 || this.variant.min === '0' || this.variant.max === '') {
        this.variant.min = null
      }
      if (this.variant.max === 0 || this.variant.max === '0' || this.variant.max === '') {
        this.variant.max = null
      }
    },
    createVariant () {
      this.variant.product_id = this.productId
      if (!this.variant.product_id) {
        return this.$toast('You must create a product first')
      }
      this.disableSubmit = true
      this.variant.images = this.newImages
      this.setMinAndMaxToNull()
      Inventory.createVariant(this.variant)
      .then((response) => {
        this.disableSubmit = false
        if (response.error) {
          this.validationErrors = response.message
          return
        }
        this.validationErrors = {}
        this.$emit('added-variant')
        this.$toast('Variant successfully created.')
      })
    },
    updateVariant () {
      this.variant.images = this.newImages
      this.setMinAndMaxToNull()
      Inventory.updateVariant(this.variant, this.variant.id)
      .then((response) => {
        if (response.error) {
          this.validationErrors = response.message
          return
        }
        this.validationErrors = {}
        this.$emit('added-variant')
        this.$toast('Variant successfully updated.')
      })
    }
  },
  components: {
    CpInput: require('../../cp-components-common/inputs/CpInput.vue'),
    CpTooltip: require('../../custom-plugins/CpTooltip.vue'),
    CpPhotoUpload: require('../products/CpPhotoUpload.vue'),
    CpDescriptionAssignment: require('../products/CpDescriptionAssignment.vue'),
  }
}
</script>
<style lang="scss" scoped>
#cp-variant-form {
  .variant-form-submit-buttons {
    text-align: right;
    padding: 5px;
    padding-bottom: 10px;
  }
  .padding-right {
    padding-right: 10px;
  }
  .min-max-wrapper {
    display: flex;
    .min-max-col {
      flex: 1;
    }
  }
}
</style>
