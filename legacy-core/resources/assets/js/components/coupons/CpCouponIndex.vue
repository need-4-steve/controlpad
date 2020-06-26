<template lang="html">
    <div class="index-wrapper">
      <a class="cp-button-link" download :href="'/api/v1/coupons/csv?search_term=' + indexRequest.search_term + '&column=' + indexRequest.column  + '&order=' +indexRequest.order + '&status=' + indexRequest.status">Coupon CSV</a>
      <a class="cp-button-link" download :href="'/api/v1/coupons/applied/csv?search_term=' + indexRequest.search_term + '&column=' + indexRequest.column +'&order=' + indexRequest.order +'&status='+ indexRequest.status">Applied Coupon CSV</a>
    <cp-tabs
   :items="[
     { name: 'All', active: true },
     { name: 'Active', active: false },
     { name: 'Consumed', active: false },
     { name: 'Expired', active: false }
   ]"
   :callback="selectStatus"></cp-tabs>
      <cp-table-controls
        :date-picker="false"
        :index-request="indexRequest"
        :resource-info="pagination"
        :get-records="getIndex">
      </cp-table-controls>
          <table class="cp-table-standard desktop">
              <thead>
                <th @click="sortColumn('title')">Name
                    <span v-show="indexRequest.column == 'title'">
                        <span v-show="asc"><i class='mdi mdi-sort-ascending'></i></span>
                        <span v-show="!asc"><i class='mdi mdi-sort-descending'></i></span>
                    </span>
                </th>
                  <th>Description</th>
                  <th>Value
                      <span v-show="indexRequest.column == 'amount'">
                          <span v-show="asc"><i class='mdi mdi-sort-ascending'></i></span>
                          <span v-show="!asc"><i class='mdi mdi-sort-descending'></i></span>
                      </span>
                  </th>
                  <th>Uses Remaining</th>
                  <th @click="sortColumn('code')">Code
                      <span v-show="indexRequest.column == 'code'">
                          <span v-show="asc"><i class='mdi mdi-sort-ascending'></i></span>
                          <span v-show="!asc"><i class='mdi mdi-sort-descending'></i></span>
                      </span>
                  </th>
                  <th v-if="Auth.hasAnyRole('Superadmin', 'Admin')" @click="sortColumn('type')">Type
                      <span v-show="indexRequest.column == 'type'">
                          <span v-show="asc"><i class='mdi mdi-sort-ascending'></i></span>
                          <span v-show="!asc"><i class='mdi mdi-sort-descending'></i></span>
                      </span>
                  </th>
                  <th><!-- delete --></th>
              </thead>
              <tbody v-if="coupons.length > 0">
                  <tr v-for="coupon in coupons">
                      <td><a @click="showCouponOrders(coupon)">{{ coupon.title }}</a></td>
                      <td id="description">{{ coupon.description }}</td>
                      <td v-if="coupon.is_percent">{{ coupon.amount }}%</td>
                      <td v-else>{{ coupon.amount | currency }}</td>
                      <td id="uses">{{ coupon.max_uses - coupon.uses }} / {{coupon.max_uses}}</td>
                      <td>{{ coupon.code }}</td>
                      <td v-if="Auth.hasAnyRole('Superadmin', 'Admin')">{{coupon.type}}</td>
                      <td><i class="mdi mdi-close pointer" @click="deleteCoupon(coupon.id)"></i></td>
                  </tr>
              </tbody>
              <tbody v-else>
                  <tr class="row">
                      <td class="cell">
                          <span class="overflow">There are no coupons to display.</span>
                      </td>
                  </tr>
              </tbody>
          </table>
          <section  class="cp-table-mobile">
            <div v-for="coupon in coupons">
            <div><span>Name: </span><span><a @click="showCouponOrders(coupon)">{{ coupon.title }}</a></span></div>
            <div id="description"><span>Description: </span><span>{{ coupon.description }}</span></div>
            <div v-if="coupon.is_percent"><span>Value: </span><span>{{ coupon.amount }}%</span></div>
            <div v-else><span>Value: </span><span>{{ coupon.amount | currency }}</span></div>
            <div id="uses"><span>Used Remaining: </span><span>{{ coupon.max_uses - coupon.uses }} / {{coupon.max_uses}}</span></div>
            <div><span>Code: </span><span>{{ coupon.code }}</span></div>
            <div><span></span><span><i class="mdi mdi-close pointer" @click="deleteCoupon(coupon.id)"></i></span></div>
          </div>
          </section>
          <div class="align-center">
            <div class="no-results" v-if="noResults">
                <span>No results for this timeframe</span>
            </div>
            <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
            <cp-pagination :pagination="pagination" :callback="getIndex" :offset="2"></cp-pagination>
          </div>
        <!--  MODAL -->
        <transition name=modal>
          <section class="cp-modal-standard" v-if="couponModal" @click="couponModal = false, selectedCoupon = {}">
            <div class="cp-modal-body">
              <cp-used-coupons-index :coupon="selectedCoupon" :modal-show="couponModal"></cp-used-coupons-index>
            </div>
          </section>
      </transition>
    </div>
</template>

<script>
const Checkout = require('../../resources/CheckoutAPIv0.js')
const Orders = require('../../resources/OrdersAPIv0.js')
const Auth = require('auth')

module.exports = {
  data () {
    return {
      Auth: Auth,
      pagination: {
        current_page: 1,
        per_page: 15
      },
      asc: false,
      noResults: false,
      loading: false,
      coupons: [],
      couponModal: false,
      selectedCoupon: {
        title: '',
        amount: '',
        is_percent: '',
        uses: '',
        created_at: '',
        expires_at: '',
        orders: {}
      },
      indexRequest: {
        order: 'desc',
        column: 'created_at',
        per_page: 15,
        search_term: '',
        status: 'all',
        page: 1
      }
    }
  },
  props: ['refreshPage'],
  mounted () {
    Auth.hasAnyRole('Superadmin', 'Admin') ? this.indexRequest.per_page = '100' : this.indexRequest.per_page = '15'
    this.getIndex()
  },
  methods: {
    selectStatus (name) {
      switch (name) {
        case 'All':
          this.couponStatus('all')
          break
        case 'Consumed':
          this.couponStatus('used')
          break
        case 'Active':
          this.couponStatus('active')
          break
        case 'Expired':
          this.couponStatus('expired')
          break
        default:
}
    },
    showCouponOrders: function (coupon) {
      let params = {
        coupon_id: coupon.id
      }
      Orders.getOrders(params)
        .then((response) => {
          if (response.error) {
            return this.$toast(response.message)
          }
          this.selectedCoupon = coupon
          this.selectedCoupon.orders = response.data
          this.couponModal = true
        })
    },
    deleteCoupon: function (id) {
      Checkout.deleteCoupon(id)
        .then((response) => {
          if (response.error) {
            return this.$toast(response.message, {error: true})
          }
          this.getIndex()
          this.$toast('The coupon was successfully removed from the system.', { dismiss: false })
        })
    },
    getIndex: function () {
      this.loading = true
      this.coupons = []
      let params = {
        owner_pid: Auth.getOwnerPid(),
        sort_by: (this.asc ? '' : '-') + this.indexRequest.column,
        page: this.pagination.current_page,
        per_page: this.indexRequest.per_page,
        search_term: this.indexRequest.search_term
      }
      if (this.indexRequest.status != 'all') {
        params.status = this.indexRequest.status
      }
      Checkout.getCoupons(params)
        .then((response) => {
          if (response.error) {
            return this.$toast(response.message)
          }
          this.pagination = response
          this.coupons = response.data
          this.loading = false
        })
    },
    couponStatus: function (status) {
      this.indexRequest.status = status
      this.pagination.current_page = 1
      this.getIndex()
    },
    sortColumn: function (column) {
      this.indexRequest.column = column
      this.asc = !this.asc
      if (this.asc === true) {
        this.indexRequest.order = 'asc'
      } else {
        this.indexRequest.order = 'desc'
      }
      this.getIndex()
    }
  },
  events: {
    'newCoupon': function () {
      this.getIndex()
    }
  },
  watch: {
    refreshPage: function (value) {
      this.getIndex()
    }
  },
  components: {
    CpUsedCouponsIndex: require('./CpUsedCouponsIndex.vue'),
    CpTableControls: require('../../cp-components-common/tables/CpTableControls.vue'),
    CpTabs: require('../../cp-components-common/navigation/CpTabs.vue')
  }
}
</script>

<style lang="scss">
@import "resources/assets/sass/var.scss";
.index-wrapper {
    display: inline;
    .coupon-body {
      border: solid 1px #ddd;
      padding: 5px;
        table {
            width: 100%;
        }
        padding: 0;
        margin: 0;
        text-align: right;
    }
    .coupon-delete {
        margin: 5px;
    }
    .row {
        overflow:visible;
    }
    .cell {
        overflow:visible;
    }
}
  .cp-button-standard {
  &.download {
    color: #fff;
    margin-top: 12px;
    &:hover {
      background: $cp-main;
      a {
        color: #fff;
        text-decoration: none;
      }
    }
  }
  }
</style>
