<template lang="html">
  <div class="create-wrapper">
      <div class="cp-form-standard">
          <cp-input
            placeholder="Create your coupons name"
            label="Coupon Name"
            :error="validationErrors['title']"
            type="text"
            v-model="coupon.title"></cp-input>
            <label>Coupon Description</label>
          <textarea
            class="description"
            :error="validationErrors['description']"
            placeholder="Write your coupon decription here..."
            v-model="coupon.description"></textarea>
          <cp-select
            placeholder="Select a discount type"
            label="Discount Type"
            :error="validationErrors['is_percent']"
            v-model="coupon.is_percent"
            :options="[
              { name: 'Flat Rate', value: 'false' },
              { name: 'Percentage', value: 'true' }
              ]">
              </cp-select>
                <cp-input
                v-if="coupon.is_percent === 'false'"
                label="Coupon Value"
                :error="validationErrors['amount']"
                placeholder="Example: $0.00"
                type="number"
                v-model.number="coupon.amount"></cp-input>
                <cp-input
                v-if="coupon.is_percent === 'true'"
                label="Coupon Value"
                type="number"
                :error="validationErrors['amount']"
                placeholder=" Example: 25%"
                v-model.number="coupon.amount"></cp-input>
          <cp-select
            v-if="Auth.hasAnyRole(['Admin', 'Superadmin'])"
            placeholder="Select a coupon type"
            label="Coupon Type"
            :error="validationErrors['is_percent']"
            v-model="coupon.type"
            :options="[
              { name: 'Wholesale', value: 'wholesale' },
              { name: 'Retail', value: 'retail' }
              ]">
              </cp-select>
          <cp-input
            label="Number of Uses"
            :error="validationErrors['max_uses']"
            type="number"
            placeholder="Number of times this coupon can be used"
            v-model="coupon.max_uses"></cp-input>
          <div class="coupon-expire">
            <label for="coupon">Coupon Can Expire</label>
            <input
              type="checkbox"
              name="checkbox"
              v-model="canExpire"
              @change="toggleExpire()">
              </div>
          <div v-if="canExpire" class="coupon-expire-time">
            <cp-input
              type="date"
              data-date-inline-picker="false"
              data-date-open-on-focus="true"
              v-model="coupon.expires_at"></cp-input>
              </div>
          <div class="generate-code">
            <cp-input
              :error="validationErrors['code']"
              type="text"
              placeholder="Create custom or click to generate"
              v-model="coupon.code">
              </cp-input>
            <button
              class="cp-button-standard"
              @click="generateCode()">Generate Code</button>
          </div>
      </div>
      <div class="action-buttons">
        <button class="cp-button-standard cancel" @click="$emit('closeModal', false)">Cancel</button>
        <button class="cp-button-standard" @click="saveCoupon()">Save Coupon</button>
      </div>
  </div>
</template>

<script>
const Checkout = require('../../resources/CheckoutAPIv0.js')
const moment = require('moment')
const Auth = require('auth')

module.exports = {
  data () {
    return {
      coupon: {
        title: '',
        type: '',
        amount: '',
        description: '',
        max_uses: '',
        code: '',
        expires_at: null,
        type: ''
      },
      canExpire: false,
      errorMessages: {},
      Auth: Auth,
      validationErrors: {}
    }
  },
  props: {
  },
  mounted () {
  },
  methods: {
    generateCode: function () {
      this.coupon.code = ''
      this.coupon.code = Math.random().toString(36).substr(2, 12)
    },
    toggleExpire () {
      console.log('toggleExpire ' + this.canExpire)
      if (!this.canExpire) {
        this.coupon.expires_at = null
      } else {
        this.coupon.expires_at = moment().format('YYYY-MM-DD')
      }
    },
    saveCoupon: function () {
      if (!Auth.hasAnyRole('Superadmin', 'Admin')) {
        this.coupon.type = 'retail'
      }
      var actualUserId = Auth.getClaims().actualUserId
      if (actualUserId) {
        return this.$toast('You are not Authorized')
      }
      this.coupon.is_percent = this.coupon.is_percent !== 'false'
      if (this.canExpire && this.coupon.expires_at <= moment().format('YYYY-MM-DD')) {
        this.$toast('Coupon expiration must be after today.', {error: true})
        return
      }
      if (this.canExpire && this.coupon.expires_at > '2037-12-31') {
        this.$toast('Coupon expiration must be before year 2038.', {error: true})
        return
      }
      Checkout.createCoupon(this.coupon)
        .then((response) => {
          if (response.error) {
            this.validationErrors = response.message
          } else {
            this.$emit('closeModal', false)
            this.errorMessages = {}
            this.$emit('newCoupon', this.coupon)
            this.coupon = {
              title: '',
              type: '',
              amount: '',
              description: '',
              max_uses: '',
              code: ''
            }
          }
        })
    }
  },
  components: {
    CpTextArea: require('../../cp-components-common/inputs/CpTextArea.vue')
  }
}
</script>

<style lang="scss">
@import "resources/assets/sass/var.scss";

.create-wrapper {
  .action-buttons {
    margin: 0px;
    padding: 5px 0px;
    display: flex;
    justify-content: space-between;
  }
  .cp-form-standard {
    textarea {
      background: $cp-lighterGrey;
      border-radius: 3px;
      padding: 10px;
      width: 98%;
      height: 80px;
      border: none;
    }
    .coupon-expire {
      display: inline-block;
      input {
        width: 50px;
        height: 15px;
      }
    }
    .generate-code {
      display:flex;
      -webkit-display: flex;
      -webkit-justify-content: space-between;
      justify-content: space-between;
      margin-bottom: 15px;
        span {
          width: 63%;
          margin: 0px;
            input {
              box-sizing: border-box;
              margin-top: 0px;
              margin-bottom: 0px;
            }
          }
        button {
            width: 37%;
        }
    }
    @media (max-width: 675px) {
      .generate-code {
        display:block;
        span {
          width: 100% !important;
        }
        button {
          margin-top:5px;
          width: 100% !important;
        }
      }
    }
  }
}
</style>
