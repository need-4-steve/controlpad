<template lang="html">
  <div class="plan-wrapper">
    <div class="cp-form-standard">
      <div>
          <cp-input
          label="Plan Name"
          type="text"
          :placeholder="'Name Here'"
          :error="validationErrors['name']"
          v-model="plan.title"></cp-input>
          <cp-text-area
            label="Description"
            type="text"
            :placeholder="'Description Here'"
            :error="validationErrors['description']"
            v-model="plan.description"></cp-text-area>
          <div class="discounts-wrapper">
            <cp-select
              label="Duration"
              placeholder="Select a Duration"
              type="text"
              :options="durations"
              :key-value="{ name: 'name' , value: 'name' }"
              :error="validationErrors.duration"
              v-model="plan.duration"></cp-select>
            <cp-input
              label="Frequency"
              type="number"
              :placeholder="1"
              :error="validationErrors['frequency']"
              v-model="plan.frequency"></cp-input>
          </div>
        <cp-toggle label="Enable Plan" class="enable-switch" v-model="plan.enabled"></cp-toggle>
        <cp-visibility-assignment-beta
        :selected-visibilities="plan.visibilities"
        :validation-errors="validationErrors"
        @selected-visibilities="function (val) { plan.visibilities = val } "></cp-visibility-assignment-beta>
          <div class="discounts-wrapper">
            <cp-input
              label="Discount Amount (%)"
              type="text"
              :placeholder="3.5"
              :error="validationErrors['discount_percent']"
              v-model="discount.percent"></cp-input>
            <cp-input
              label="Item Count Needed for Discount"
              type="number"
              :placeholder="1"
              :error="validationErrors['discount_quantity']"
              v-model="discount.min_quantity"></cp-input>
          </div>
              <button class="cp-button-standard" @click="addDiscount()">Add Discount</button>
          <div>
            <h1 v-if="plan.discounts.length > 0">Discounts</h1>
            <span class="discounts-loop" v-for="(discount, index) in plan.discounts" :key="index">
              <cp-input label="Discount Percent" v-model="discount.percent"></cp-input>
              <cp-input label="Discount Minimum Quantity" v-model="discount.min_quantity"></cp-input>
              <i class="mdi mdi-close x" @click="deleteDiscount(discount, index)" role="button"></i>
            </span>
          </div>

      </div>
    </div>
    <div class="submit-button">
      <button class="cp-button-standard" @click="$emit('close')">Cancel</button>
      <button class="cp-button-standard" @click="createOrUpdate()" :disabled="creatingPlan">Submit</button>
    </div>
  </div>
</template>

<script>
const Autoship = require('../../resources/AutoshipAPIv0.js')
const Moment = require('moment-timezone')

module.exports = {
  data () {
    return {
      validationErrors: {},
      creatingPlan: false,
      discount: {
      },
      durations: [
        { name: 'Days' },
        { name: 'Weeks' },
        { name: 'Months' },
        { name: 'Quarters' },
        { name: 'Years' }
      ]
    }
  },
  props: {
    plan: {
      default () {
        return {}
      }
    },
    edit: {
      type: Boolean
    }
  },
  mounted () {
    this.fixVisibility()
  },
  methods: {
    fixVisibility () {
      if (this.plan.title) {
        for (let k = 0; k < this.plan.visibilities.length; k++) {
          delete this.plan.visibilities[k].deleted_at
        }
      }
    },
    addDiscount () {
      if (this.discount.percent !== undefined && this.discount.min_quantity !== undefined && this.discount) {
        if (this.plan.discounts) {
          this.plan.discounts.push(this.discount)
        } else {
          this.plan.discounts = this.discount
        }
        this.discount = {}
      } else {
        this.$toast('Please Enter a percent and Quantity', { dismiss: false })
      }
    },
    deleteDiscount (discount, index) {
      this.plan.discounts.splice(index, 1)
    },
    createOrUpdate () {
      if (this.edit) {
        this.updatePlan()
      } else {
        this.createPlan()
      }
    },
    createPlan () {
      this.creatingPlan = true
      Autoship.createPlan(this.plan)
        .then((response) => {
          if (response.error) {
            this.validationErrors = response
            this.$toast(response.message, { dismiss: false })
          } else {
            this.$toast('Plan Created', { dismiss: false })
            this.plan = response
          }
          this.creatingPlan = false
          this.$emit('add-plan', response)
        })
    },
    updatePlan () {
      Autoship.updatePlan(this.plan, this.plan.id)
        .then((response) => {
          if (response.error) {
            this.validationErrors = response
            this.$toast(response.message, { dismiss: false })
          } else {
            this.$toast('Plan Updated', { dismiss: false })
            this.plan = response
          }
        })
    }
  }

}
</script>

<style lang="scss">
@import "resources/assets/sass/var.scss";
.plan-wrapper {
  .free-shipping {
    padding-bottom: 15px;
  }
  .cp-box-heading {
    color: #777;
    background-color: white;
    padding: 0;
    font-weight: 100;
  }
  .cp-box-body {
    border: 0;
  }
  .discounts-loop {
    display: flex;
    justify-content: space-around;
    align-items: center;
  }
  .discounts-wrapper {
    display: flex;
    justify-content: space-between;
    span {
      width: 49%;
    }
  }
  .enable-switch {
    width: 300px;
  }
  .textarea-wrapper{
    textarea {
      background-color: $cp-lighterGrey;
    }
  }
}
</style>
