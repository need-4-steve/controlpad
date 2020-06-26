<template lang="html">
    <div class="subscription-form-wrapper">
        <div class="navigation-wrapper">
            <a class="cp-button-standard" href="/subscriptions/create">Add New Plan</a>
              <cp-table-controls
              :date-picker="false"
              :index-request="indexRequest"
              :resource-info="pagination"
              :get-records="planIndex"></cp-table-controls>
        </div>
        <table class="cp-table-standard desktop">
            <thead>
              <th @click="sortColumn('id')">Subscription ID
                <span v-show="indexRequest.column == 'id'">
                    <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                    <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                </span>
              </th>
              <th @click="sortColumn('title')">Title
                <span v-show="indexRequest.column == 'title'">
                    <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                    <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                </span>
              </th>
              <th @click="sortColumn('price')">Price
                <span v-show="indexRequest.column == 'price'">
                    <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                    <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                </span>
              </th>
              <th @click="sortColumn('duration')">Frequency
                <span v-show="indexRequest.column == 'duration'">
                    <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                    <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                </span>
              </th>
              <th @click="sortColumn('renewable')">Auto Renewable
                <span v-show="indexRequest.column == 'renewable'">
                    <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                    <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                </span>
              </th>
              <th @click="sortColumn('renewable')">Show for sign up
                <span v-show="indexRequest.column == 'renewable'">
                    <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                    <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                </span>
              </th>
              <th @click="sortColumn('free_trial_time')">Free Trial Period in Days
                <span v-show="indexRequest.column == 'free_trial_time'">
                  <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                  <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                </span>
              </th>
              <th><!-- Delete --></th>
            </thead>
            <tbody>
              <tr v-for="sub in subscription">
                <td><a :href="'/subscription-plan/edit/' + sub.id">{{ sub.id }}</a></td>
                <td>{{ sub.title }}</td>
                <td>{{ sub.price.price | currency }}</td>
                <td>{{ sub | frequency }}</td>
                <td>
                  <span v-if="sub.renewable === 1">Yes</span>
                  <span v-if="sub.renewable === 0">No</span>
                </td>
                <td>
                  <span v-if="sub.on_sign_up === 1">Yes</span>
                  <span v-if="sub.on_sign_up === 0">No</span>
                </td>
                <td>{{ sub.free_trial_time }}</td>
                <td><i class="mdi mdi-close pointer" @click="showConfirm = true, newSub = sub "></i>
                </td>
              </tr>
            </tbody>
        </table>
        <cp-confirm
        message="'Are you sure you want to delete this plan?'"
        :show="showConfirm"
        v-model="showConfirm"
        :callback="deleteSubscriptions"
        :params="newSub"></cp-confirm>
        <section v-for="sub in subscription" class="cp-table-mobile">
          <div><span>Subscription ID: </span><span><a :href="'/subscription-plan/edit/' + sub.id">{{ sub.id }}</a></span></div>
          <div><span>Title: </span><span>{{ sub.title }}</span></div>
          <div><span>Price: </span><span>{{ sub.price.price | currency }}</span></div>
          <div><span>Frequency: </span><span>{{ sub | frequency }}</span></div>
          <div><span>Auto Renewable: </span><span></span></div>
          <span v-if="sub.renewable === 1">Yes</span>
          <span v-if="sub.renewable === 0">No</span></span>
          <div><span>Show for sign up: </span><span>
          <span v-if="sub.on_sign_up === 1">Yes</span>
          <span v-if="sub.on_sign_up === 0">No</span></span></div>
          <div><span>Free Trial Period in Days: </span><span>{{ sub.free_trial_time }}</span></div>
          <div><span> </span><span><i class="mdi mdi-close pointer" @click="showConfirm = true, newSub = sub "></i></span></div>
        </section>
        <div class="align-center">
          <div class="no-results" v-if="noResults">
              <span>No results for this timeframe</span>
          </div>
          <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
          <cp-pagination :pagination="pagination" :callback="planIndex" :offset="2"></cp-pagination>
        </div>
    </div>
    </div>
</template>

<script>
const marked = require('marked')
const Subscription = require('../../resources/subscription.js')
const Auth = require('auth')

module.exports = {
  name: 'CpSubscriptionPlanIndex',
  routing: [
    {
      name: 'site.CpSubscriptionPlanIndex',
      path: 'subscription-plans/all',
      meta: {
        title: 'Subscription Plans'
      },
      props: true
    }
  ],
  data: function () {
    return {
      showConfirm: false,
      newSub: {},
      noResults: false,
      loading: false,
      subscription: {},
      pagination: {
        per_page: 15
      },
      asc: false,
      indexRequest: {
        order: 'ASC',
        column: 'id',
        per_page: 15,
        search_term: '',
        page: 1
      },
      reverseSort: true
    }
  },
  filters: {
    marked: marked
  },
  computed: {},
  mounted: function () {
    Auth.hasAnyRole('Superadmin', 'Admin') ? this.indexRequest.per_page = '100' : this.indexRequest.per_page = '15'
    this.planIndex()
  },
    methods: {
        planIndex: function(){
            this.loading = true;
            this.indexRequest.page = this.pagination.current_page;
            this.sales = {};
            Subscription.planIndex()
                .then((response) => {
                    if (response.error) {
                        return this.$toast('errorMessage', response.message)
                    }
                    if (response.total === 0) {
                        this.noResults = true;
                    }
                    this.loading = false
                    this.subscription = response.data
                    response.per_page = parseInt(response.per_page)
                    this.pagination = response
                });
        },
        deleteSubscriptions: function (subscription) {
            Subscription.deleteSubscriptions(subscription.id)
                .then((response) => {
                    if (response.error) {
                        return this.$toast(response.message), { error: true };
                    }
                    this.planIndex()
                    return this.$toast('Subscription Deleted', { dismiss: false });

                });
        },
        sortColumn: function(column) {
            this.reverseSort = !this.reverseSort
            this.indexRequest.column = column;
            this.asc = !this.asc;
            if (this.asc === true) {
                this.indexRequest.order = 'asc';
            } else {
                this.indexRequest.order = 'desc';
            }
            this.planIndex()
        }
    },
    filters: {
      'frequency': function (subscription) {
        switch (subscription.duration) {
          case 0:
            return 'One-Time'
          case 1:
            return 'Monthly'
          case 3:
            return 'Quarterly'
          case 12:
            return 'Yearly'
          default:
            return subscription.duration.toString() + ' Months'
        }
      }
    },
    events: {
        'search_term': function (term) {
            this.indexRequest.search_term = term;
            this.pagination.current_page = 1;
            this.planIndex();
        }
    },
    components: {
      CpTableControls: require('../../cp-components-common/tables/CpTableControls.vue'),
      'CpConfirm': require('../../cp-components-common/CpConfirm.vue')
    }
}
</script>

<style lang="scss">
    .subscription-form-wrapper {
      .navigation-wrapper {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        margin: 5px 0px;
        a {
          align-self: center
        }
      }
        .cp-button-standard {
            padding: 5px 10px;
            &:hover {
                color: white;
            }
            &:visited {
                color: white;
            }
            &:focus {
                text-decoration: none;
            }
        }
    }
</style>
