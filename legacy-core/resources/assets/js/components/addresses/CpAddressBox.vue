<template lang="html">
    <div class="cp-box-standard">
        <div class="cp-box-heading">
            <h5>{{ headingTitle }}</h5>
            <button type="button" name="button" class="cp-box-heading-button" @click="editAddress = true" v-if="!editAddress"><i class="mdi mdi-pencil"></i></button>
            <button type="button" name="button" class="cp-box-heading-button save" @click="createAddress()" v-if="editAddress"><i class="mdi mdi-floppy"></i></button>
        </div>
        <div class="cp-box-body address-display" v-if="!editAddress">
            <div v-if="address.address_1">
                <h4>{{ address.name }}</h4>
                <span>{{ address.address_1 }}</span>
                <span>{{ address.address_2 }}</span>
                <span>{{ address.city }}, {{ address.state }}</span>
                <span>{{ address.zip }}</span>
                <span></span>
            </div>
            <span v-else>No address found.</span>
        </div>
        <div class="cp-box-body"  v-if="editAddress">
            <cp-address-form :address="address"></cp-address-form>
        </div>
    </div>
</template>

<script>
const Addresses = require('../../resources/addresses.js')

module.exports = {
  data: function () {
    return {
      address: {},
      editAddress: false
    }
  },
  props: {
    addressLabel: {
      type: String,
      required: true
    },
    addressableType: {
      type: String,
      required: true
    },
    headingTitle: {
      type: String
    },
    addressableId: {
      type: Number // if unused it defaults to auth user on backend
    },
    addressModel: {
      type: Object,
      default () {
        return {}
      }
    }
  },
  computed: {},
  mounted: function () {
    this.getAddress()
  },
  methods: {
    getAddress: function () {
      this.address.label = this.addressLabel
      this.address.addressable_type = this.addressableType
      if (this.addressableId !== undefined) {
        this.address.addressable_id = this.addressableId
      }
      Addresses.show(this.address)
          .then((response) => {
            if (response.error && response.code === 404) {
              this.editAddress = true
            }

            this.address = response
          })
    },
    createAddress: function () {
      this.address.label = this.addressLabel
      this.address.addressable_type = this.addressableType
      if (this.addressableId !== undefined) {
        this.address.addressable_id = this.addressableId
      }
      Addresses.create(this.address)
        .then((response) => {
          if (response.error) {
            return
          }
          this.getAddress()
          this.editAddress = false
        })
    }
  },
  components: {
    'CpAddressForm': require('./CpAddressForm.vue')
  }
}
</script>

<style lang="sass">
    .address-display {
        font-size: 1.2em;
        h4 {
            font-weight: 400;
        }
        span {
            display: block;
        }
    }
</style>
