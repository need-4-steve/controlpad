<template>
  <div id="my-subscription-wrapper">
    <cp-tabs :items="tabs" :callback="changeTab"></cp-tabs>
    <div v-if="!loading">
      <cp-table-controls
        :date-picker="false"
        :search-box="false"
        :index-request="indexRequest"
        :search-place-holder="'Search Subscriptions'"
        :resource-info="pagination"
        :get-records="getSubscriptions">
      </cp-table-controls>
      <div class="item-list item-list-container">
        <div class="list-header item-container">
          <div class="cp-clickable" @click="updateSort('id')">
            <div class="column-name">Subscription ID<i v-show="indexRequest.sort_by.includes('id')" :class="['mdi', '', indexRequest.sort_by.indexOf('-') == 0 ? 'mdi-sort-descending' : 'mdi-sort-ascending']"></i></div>
          </div>
          <div class="schedule-container">
            <div class="cp-clickable" @click="updateSort('next_billing_at')">
              <div class="column-name">Next Order Date<i v-show="indexRequest.sort_by.includes('next_billing_at')" :class="['mdi', '', indexRequest.sort_by.indexOf('-') == 0 ? 'mdi-sort-descending' : 'mdi-sort-ascending']"></i></div>
            </div>
            <div class="column-name">Frequency</div>
          </div>
          <div class="column-name">Buyer Name</div>
          <div class="column-name">Items</div>
          <div class="column-name">Amount</div>
          <div class="column-name" v-if="displayType === 'self'">Credit Card</div>
        </div>
        <div v-for="(subscription, index) in subscriptions" v-if="!loading" class="item-container">
          <div>
            <a :href="detailsRoute + subscription.id"
              class="attribute"
              data-name="Subscription ID">{{ subscription.id }}</a>
          </div>
          <div class="schedule-container">
            <div class="attribute" data-name="Next Order Date">{{ subscription.next_billing_at | cpStandardDate }}</div>
            <div class="attribute" data-name="Frequency">{{ subscription | frequency }}</div>
          </div>
          <div class="attribute" data-name="Buyer Name">{{ subscription.buyer_first_name }} {{ subscription.buyer_last_name }}</div>
          <div class="attribute" data-name="Items">{{ subscription.item_count }}</div>
          <div class="attribute" data-name="Amount">{{ subscription.subtotal | currency }}</div>
          <div class="attribute" data-name="Credit Card" v-if="displayType === 'self'">{{ creditCard.card_digits }}</div>
        </div>
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
    { name: 'site.CpMySubscription', meta: {title: 'My Subscriptions', nosubscription: true, type: 'self'}, path: '/my-subscriptions'},
    { name: 'site.CpAutoshipCustomerSubscriptions', meta: {title: 'Customer Subscriptions', type: 'customers'}, path: '/customer-subscriptions' }
  ],
  data: function () {
    return {
      user_pid: null,
      creditCard: {},
      loading: false,
      showId: null,
      displayType: 'self',
      detailsRoute: '/my-subscriptions/',
      subscriptions: [],
      tabs: [
        { name: 'Active', active: true },
        { name: 'Cancelled' }
      ],
      pagination: {},
      indexRequest: {
        search_term: null,
        show_disabled: false,
        sort_by: 'next_billing_at',
        page: 1,
        per_page: 15
      }
    }
  },
  created () {
    this.displayType = this.$route.meta.type
    this.user_pid = Auth.getAuthPid()
    switch (this.displayType) {
      case 'self':
        this.indexRequest.buyer_pid = this.user_pid
        this.getCardToken()
        this.detailsRoute = '/my-subscriptions/'
        break;
      case 'customers':
        this.indexRequest.seller_pid = this.user_pid
        this.detailsRoute = '/customer-subscriptions/'
    }
    this.getSubscriptions()
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
    changeTab (value) {
      switch (value) {
        case 'All':
        default:
          delete this.indexRequest.filter
          this.indexRequest.show_disabled = true
        case 'Active':
          delete this.indexRequest.filter
          this.indexRequest.show_disabled = false
          break;
        case 'Cancelled':
          this.indexRequest.filter = 'disabled'
          this.indexRequest.show_disabled = true
      }
      this.getSubscriptions()
    },
    getSubscriptions () {
      this.loading = true
      Autoship.getSubscriptions(this.indexRequest)
        .then((response) => {
          this.loading = false
          this.pagination = response
          this.subscriptions = response.data
          this.calculateQuantities()
        })
    },
    calculateQuantities () {
      this.subscriptions.forEach((s) => {
        s.item_count = 0
        s.lines.forEach((l) => {
          if (l.bundle_id) {
            l.items.forEach((i) => { s.item_count += (i.quantity * l.quantity) })
          } else {
            s.item_count += l.quantity
          }
        })
      })
    }
  },
  filters: {
    frequency (subscription) {
      if (subscription.frequency === 1) {
        return '1 ' + subscription.duration.replace('s', '')
      }
      return subscription.frequency + ' ' + subscription.duration
    }
  }
}
</script>

<style lang="scss">
#my-subscription-wrapper {
  div {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  @media screen and (min-width: 751px) {
    .item-list-container {
      li:nth-child(even) {background: $cp-lighterGrey};
    }
    .item-list > li {
      margin-bottom: 10px
    }
    .list-header {
      background-color: $cp-main;
    }
    .column-name {
      font-weight: 400;
      color: $cp-main-inverse;
    }
    .item-container {
      display: grid;
      grid-template-columns: minmax(161px, 3fr) minmax(161px, 5fr) minmax(161px, 5fr) minmax(53px, 1fr) minmax(107px, 2fr) minmax(107px, 2fr);
      padding: 10px;
    }
    .schedule-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(161px, 1fr));
    }
    .attribute-center {
      display: inline-block;
      text-align: center;
    }
  }
  @media screen and (max-width:750px) {
    .item-list-container {
      display: block;
      padding: 20px;
      .item-container {
        border-radius: 2px;
        display: block;
        background: white;
        margin-bottom: 10px;
        box-shadow: 1px 1px 1px 1px #ccc;
        padding: 10px 20px;
        div {
          font-weight: bold;
        }
      }
    }
    /* Don't display the first item, since it is used to display the header for tabular layouts*/
    .item-list-container>div:first-child {
        display: none;
    }
    .attribute {
      display: grid;
      grid-template-columns: minmax(9em, 30%) 1fr;
      span {
        display: none;
      }
    }
    .attribute::before {
      content: attr(data-name);
      font-weight: normal;
    }
  }
}
</style>
