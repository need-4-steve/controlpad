<template lang="html">
    <div class="subscription-form-wrapper">
        <div class="cp-form-standard">
            <div>
                <div>
                    <label>Subscription Plan Title</label>
                    <cp-input
                    type="text"
                    placeholder="Title"
                    :error="errorMessages.title"
                    v-model="subscription.title"></cp-input>
                </div>
                <div>
                    <label>Price</label>
                    <cp-input
                    type="number"
                    placeholder="14.95"
                    :error="errorMessages.price"
                    v-model="subscription.price.price"></cp-input>
                </div>
                <div  v-if="$getGlobal('tax_subscription').show">
                    <label>Tax Class</label>
                    <cp-input
                    type="text"
                    placeholder="00000000"
                    :error="errorMessages.tax_class"
                    v-model="subscription.tax_class"></cp-input>
                </div>
                <cp-select
                  label="Frequency"
                  type="text"
                  :options="[
                    { name: 'Monthly', value: 1 },
                    { name: 'Quarterly', value: 3 },
                    { name: 'Yearly', value: 12 },
                    { name: 'One-Time', value: '0' }
                  ]"
                  @input="durationSelected"
                  :error="errorMessages['duration']"
                  v-model="subscription.duration"></cp-select>
                <div>
                    <label>Free Trial Period (days)</label>
                    <cp-input
                    type="text"
                    placeholder="How many days?"
                    @input="autorenewSelected"
                    :error="errorMessages.free_trial_time"
                    v-model="subscription.free_trial_time"></cp-input>
                </div>
                <div :class="{ error: errorMessages.on_sign_up }" class="radio-box">
                    <label>Show on Sign up</label>
                    <!-- <br> -->
                    <label>Yes <input name="subscription" type="radio" v-model="subscription.on_sign_up" value=1></label>
                    <label>No <input name="subscription" type="radio" v-model="subscription.on_sign_up" value=0></label>
                    <span v-show="errorMessages.on_sign_up" class="cp-warning-message">{{ errorMessages.on_sign_up }}</span>
                </div>
                <cp-select
                  label="Seller Type"
                  type="text"
                  :options="[
                    { name: 'Affiliate', value: 1 },
                    { name: 'Reseller', value: 2 }
                  ]"
                  :error="errorMessages['seller_type']"
                  v-model="subscription.seller_type_id"></cp-select>
                <div class="editor-wrapper">
                    <label for="">Create Your Subscription Plan Here.</label>
                    <cp-editor v-if="loaded" v-model="subscription.description"></cp-editor>
                </div>
                <div>
                    <button class="cp-button-standard save-button" @click="savePlan()">Save</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
  const Subscription = require('../../resources/subscription.js')

  module.exports = {
    name: 'CpSubscriptionForm',
    routing: [
      {
        name: 'site.CpSubscriptionFormEdit',
        path: 'subscription-plan/edit/:id',
        meta: {
          title: 'Edit Subscription Plan'
        },
        props: true
      },
      {
        name: 'site.CpSubscriptionFormCreate',
        path: 'subscriptions/create',
        meta: {
          title: 'Create Subscription Plan'
        },
        props: true
      }
    ],
    data: function () {
      return {
        loaded: false,
        isEditMode: false,
        subscription: {
          type: Object,
          require: false,
          default () {
            return {
              description: null,
              on_sign_up: 0,
              duration: 1,
              renewable: 1,
              seller_type_id: null,
              price: {},
              tax_class: ''
            }
          }
        },
        errorMessages: {}
      }
    },
    props: {
      edit: {
        type: Boolean,
        default: false
      },
      validationErrors: {
        default () {
          return {}
        }
      }
    },
    created: function () {
      if (!this.subscription.price) {
        this.subscription.price = {}
      }
      if (!this.subscription.seller_type_id) {
        this.subscription.seller_type_id = null
      }
    },
    mounted: function () {
      // Workaround to get subscription when using form for edit
      // Once we move to normal routing we can pass the subscription as a prop again
      this.isEditMode = this.edit
      var planId = this.$pathParameter()
      if (planId) {
        this.getPlan(planId)
        this.isEditMode = true
      }
      if (this.isEditMode === false) {
        this.loaded = true
      }
    },
    methods: {
      savePlan: function () {
        if (this.isEditMode === false) {
          Subscription.subscriptionPlanCreate(this.subscription)
            .then((response) => {
              if (response.error) {
                this.errorMessages = response.message
                return
              }
              this.errorMessages = {}
              this.$toast(response.message, { dismiss: false })
              window.location.href = '/subscription-plans/all'
            })
        } else {
          Subscription.updateSubscription(this.subscription)
              .then((response) => {
                if (response.error) {
                  this.errorMessages = response.message
                  return
                }
                this.errorMessages = {}
                return this.$toast(response.message, { dismiss: false })
              })
        }
      },
      getPlan: function (id) {
        Subscription.planPrice(id)
          .then((response) => {
            if (!response || response.error) {
              this.$toast('Plan not found')
              return
            }
            this.loaded = true
            this.subscription = response
          })
      },
      autorenewSelected: function (freeTrialTime) {
        this.subscription.renewable = this.subscription.duration != '0' || freeTrialTime > 0
      },
      durationSelected: function (duration) {
        this.subscription.renewable = duration != '0' || this.subscription.free_trial_time > 0
      }
    },
    computed: {
      disableAutoRenew () {
        return this.subscription.duration == 0
      }
    },
    components: {
      CpSelect: require('../../cp-components-common/inputs/CpSelect.vue'),
      CpInput: require('../../cp-components-common/inputs/CpInput.vue'),
      CpEditor: require('../../cp-components-common/inputs/CpEditor.vue')
    }
  }
</script>

<style lang="scss">
@import "resources/assets/sass/var.scss";

.subscription-form-wrapper {
  .radio-box{
    padding: 5px 0 10px;
    label:first-child{
      display: inline-block;
      margin-right: 10px;
    }
    label:not(:first-child){
      input[type="radio"]{
        display: inline-block;
        height: auto !important;
        width: auto;
      }
    }
  }
  .panel-heading {
      background: #f2f2f2 !important;
  }
  .note-palette-title {
    text-align: center;
    padding: 5px;
    color: black;
  }
  .save-button {
    float: right;
    margin-top: 15px;
  }
  .input-wrapper {
    width: 100%;
    max-width: 70%;
  }
  .note-color-reset {
    text-align: center;
    color: black;
  }
}
</style>
