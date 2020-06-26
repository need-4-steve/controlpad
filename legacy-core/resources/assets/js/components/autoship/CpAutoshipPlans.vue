<template lang="html">
  <section class="autoship-plans-wrapper">
  <br/>
  <div class="plan-wrapper">
  <h2 v-if="plans.length < 0">No plans are available.</h2>
  <ul v-else>
    <li v-for="plan in plans" :key="plan.id" @click="selectPlan(plan)">
      <div class="frequency">
        {{plan | frequency}}
      </div>
      <div v-if="plan.current_discount > 0">
        {{plan.current_discount}}% discount
      </div>
      <div class="title" v-if="plan.title !== null">
        {{plan.title}}
      </div>
      <p v-if="plan.description !== null">
        {{plan.description}}
      </p>
      <div class="select-plan">
        <div class="qualify" v-if="plan.next_to_qualify > 0">
          Add {{plan.next_to_qualify}} more item<span v-if="plan.next_to_qualify > 1">s</span> to your cart to get a {{plan.next_discount}}% discount
        </div>
        <button class="cp-button-standard" @click="selectPlan()">Select</button>
      </div>
    </li>
  </ul>
</div>
  </section>
</template>

<script>
const Autoship = require('../../resources/AutoshipAPIv0.js')

module.exports = {
  mounted () {
    this.getPlans()
  },
  data () {
    return {
      plans: [],
      selectedPlan: {
        percent_discount: 0
      }
    }
  },
  props: {
    itemQuantity: {
      type: Number,
      default: 0
    },
    steps: {
    }
  },
  watch: {

  },
  methods: {
    getPlans () {
      Autoship.getPlans({visibilities: ['Wholesale']})// TODO make this check the cart or a prop?
        .then((response) => {
          this.plans = response.data
          this.calculateDiscounts(this.itemQuantity)
        })
    },
    calculateDiscounts (itemQuantity) {
        /*
          * Go through each plan's discounts to figure out what the current discount will be
          * and figure what the next discount will be with how many items to qualify for it.
          */
        for (planIndex = 0; planIndex < this.plans.length; planIndex++) {
          for (discountIndex = 0; discountIndex < this.plans[planIndex].discounts.length; discountIndex++) {
            if (itemQuantity >= this.plans[planIndex].discounts[discountIndex].min_quantity) {
              this.plans[planIndex].current_discount = this.plans[planIndex].discounts[discountIndex].percent
              if (this.plans[planIndex].discounts[discountIndex + 1] !== undefined) {
                this.plans[planIndex].next_discount = this.plans[planIndex].discounts[discountIndex + 1].percent
                this.plans[planIndex].next_to_qualify = this.plans[planIndex].discounts[discountIndex + 1].min_quantity - this.itemQuantity
              } else {
                this.plans[planIndex].next_discount = 0
                this.plans[planIndex].next_to_qualify = 0
              }
            } else if (discountIndex === 0) {
                this.plans[planIndex].next_discount = this.plans[planIndex].discounts[discountIndex].percent
                this.plans[planIndex].next_to_qualify = this.plans[planIndex].discounts[discountIndex].min_quantity - this.itemQuantity
            }
          }
        }
        return this.plans
    },
    selectPlan (plan) {
      this.selectedPlan = plan
      this.steps.skipTo('info')
    }
  },
  filters: {
    frequency (plan) {
      if (plan.frequency === 1) {
        if (plan.duration === 'Days') {
          return 'Daily'
        }
        return plan.duration.replace('s', 'ly')
      }
      return 'Every ' + plan.frequency + ' ' + plan.duration
    }
  }
}
</script>

<style lang="scss">
.autoship-plans-wrapper {
    background: $cp-grey;
  .plan-wrapper {
    text-align: center;
    .frequency {
      font-size: 28px;
    }
    .title {
      font-weight: bold;
      padding-top: 15px;
      font-size: 18px;
    }
    .qualify {
      padding-bottom: 10px;
    }
    .select-plan {
      padding-bottom: 10px;
      margin-top: auto;
      padding-top: 10px;
    }
    ul {
      display: flex;
      flex-flow: row wrap;
      justify-content: flex-start;
      background-color: $cp-grey;
    }
    li {
      border-radius: 25px;
      border: 2px solid darkgray;
      cursor: pointer;
      background-color: white;
      min-width: 315px;
      width: 45%;
      display: flex;
      flex-flow: column nowrap;
      margin-bottom: 20px;
      margin-left: 0px;
      margin-right: 20px;
      padding: 7px;
      /*Mobile Views*/
      @media (max-width: 768px) {
        min-width: 0px;
        width: 85%;
        margin-left: 0px;
        margin-right: 0px;
      }
    }
  }
}
</style>
