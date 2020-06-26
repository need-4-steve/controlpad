<template lang="html">
  <div class="subscription-reports-wrapper">
    <h3>{{user.first_name}} {{user.last_name}}</h3>
    <table class="cp-table-standard desktop">
        <thead>
          <th>Title</th>
          <th>Date</th>
          <th>Subtotal Price</th>
          <th>Total Tax</th>
          <th>Total Price</th>
          <th>Number of Months</th>
        </thead>
        <tbody v-if="!noResults">
            <tr v-for="receipt in receipts">
                <td>{{receipt.title}}</td>
                <td>{{receipt.created_at | cpStandardDate}}</td>
                <td>{{receipt.subtotal_price | currency}}</td>
                <td>{{receipt.total_tax | currency}}</td>
                <td>{{receipt.total_price | currency}}</td>
                <td>{{receipt.duration}}</td>
            </tr>
        </tbody>
        <tbody v-else>
            <tr class="row">
                <td class="cell">
                    <span class="overflow">There are no receipts to display.</span>
                </td>
            </tr>
        </tbody>
    </table>
  </div>
</template>

<script>
const moment = require('moment')
const Subscription = require('../../resources/subscription.js')

module.exports = {
  data: function () {
    return {
      pagination: {
        per_page: 15
      },
      reverseSort: false,
      noResults: false,
      loading: false,
      receipts: {}
    }
  },
  props: {
    user: {
      type: Object,
      required: true
    },
    indexRequest: {
      type: Object
    }
  },
  mounted: function () {
    this.getUserReceipt()
  },
  methods: {
    getUserReceipt: function () {
      Subscription.getSubReceipt(this.user.user_id, this.indexRequest)
        .then((response) => {
          if (response.error) {
            return this.$toast(response.message)
          }
          if (response.total === 0) {
            this.noResults = true
          } else {
            this.receipts = response.data
            this.paginate = response
          }
        })
    }
  },
  events: {
  },
  components: {
    CpTableControls: require('../../cp-components-common/tables/CpTableControls.vue'),
    CpTotalsBanner: require('../subscription/CpTotalsBanner.vue'),
    CpTabs: require('../../cp-components-common/navigation/CpTabs.vue')
  }
}
</script>

<style lang="sass">
  .subscription-reports-wrapper {
  }
</style>
