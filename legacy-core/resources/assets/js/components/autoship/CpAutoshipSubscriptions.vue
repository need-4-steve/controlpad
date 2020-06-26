<template>
    <div class="autoship-subscriptions-wrapper">
      <div v-if="!loading">
            <cp-table-controls
            :date-picker="false"
            :search-box="false"
            :index-request="indexRequest"
            :search-place-holder="'Search Subscriptions'"
            :resource-info="pagination"
            :get-records="getSubscriptions"></cp-table-controls>
      <span class="accordion-headers">
        <h3>Frequency</h3>
        <h3>Next Order Date</h3>
        <h3>Canceled</h3>
      </span>
        <div class="cp-accordion" v-for="(subscription, index) in subscriptions" :key="index">
            <div class="cp-accordion-head" @click="setOpen(index)">
                <div class="col"> {{subscription | frequency}}</div>
                <div class="col"> {{subscription.next_billing_at | cpStandardDate}}</div>
                <div class="col">
                    <span v-if="subscription.disabled_at">{{subscription.disabled_at | cpStandardDate}}</span><span v-else></span>
                    <span class="arrow" v-if="showId !== index"><i class="mdi mdi-chevron-down"></i></span>
                    <span class="arrow" v-if="showId === index"><i class="mdi mdi-chevron-up"></i></span>
                </div>
            </div>
          <cp-autoship-subscription-details v-if="showId === index" @disabled="getSubscriptions()" :subscription="subscription" :card-token="creditCard"></cp-autoship-subscription-details>
        </div>
      </div>
            <div class="align-center">
                <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
                <cp-pagination :pagination="pagination" :callback="getSubscriptions" :offset="2"></cp-pagination>
            </div>
    </div>
</template>
<script>
const Autoship = require('../../resources/AutoshipAPIv0.js')
const Auth = require('auth')
const moment = require('moment')
const Users = require('../../resources/UsersAPIv0.js')

module.exports = {
  routing: [
    { name: 'site.CpAutoshipSubscriptions', meta: {title: ''}, path: '/inventory/autoship' }
  ],
  data: function () {
    return {
      user_pid: null,
      creditCard: {},
      loading: false,
      showId: null,
      subscriptions: [],
      pagination: {},
      indexRequest: {
        show_disabled: true,
        search_term: null,
        sort_by: 'next_billing_at',
        page: 1,
        per_page: 15
      }
    }
  },
  mounted () {
    this.user_pid = Auth.getAuthPid()
    this.indexRequest.buyer_pid = this.user_pid
    this.getSubscriptions()
    this.getCardToken()
  },
  filters: {
    frequency (subscription) {
      if (subscription.frequency == 1) {
        if (subscription.duration === 'Days') {
          return 'Daily'
        }
        return subscription.duration.replace('s', 'ly')
      }
      return subscription.frequency + ' ' + subscription.duration
    }
  },
  methods: {
    getCardToken () {
      Users.getCardToken(this.user_pid)
        .then((response) => {
          if (!response.error) {
            this.creditCard = response
          }
        })
    },
    setOpen (index) {
      if (index === this.showId) {
        this.showId = null
      } else {
        this.showId = index
      }
    },
    getSubscriptions () {
      this.loading = true
      Autoship.getSubscriptions(this.indexRequest)
        .then((response) => {
          this.loading = false
          this.pagination = response
          this.subscriptions = response.data
        })
    }
  }
}
</script>

<style lang="scss">
.autoship-subscriptions-wrapper {
  .accordion-headers {
     display: flex;
    width: 100%;
    padding: 5px;
    padding-top: 0px;
    padding-bottom: 0px;
    background: #273238;
    color: white;
    h3 {
      padding: 3px;
      flex: 1;
    }
  }
  .cp-accordion-head {
    display: flex;
    width: 100%;
    padding: 5px;
    border-top-style: outset;
    border-top-width: 1px;
    .col {
      padding: 3px;
      flex: 1;
    }
  }
}
</style>
