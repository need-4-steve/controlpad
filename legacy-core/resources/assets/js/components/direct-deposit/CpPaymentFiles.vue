<template>
    <div class="open-index-wrapper">
    <cp-tabs :items="tabs" :callback="changeTab"></cp-tabs>
    <cp-table-controls
    :date-picker="true"
    :date-range="indexRequest"
    searchBox="false"
    :index-request="indexRequest"
    :resource-info="pagination"
    :get-records="getBatches"></cp-table-controls><br>
    <table class="cp-table-standard desktop">
      <thead>
        <th>File Name</th>
        <th>Description</th>
        <th>Date Created</th>
        <th>Date Paid</th>
        <th>Amount</th>
        <th>Bank Name</th>
        <th>Deposit Count</th>
        <th>Transaction Count</th>
      </thead>
      <tbody>
        <tr v-for="batch in batches">
          <td>
            <a @click="showFile(batch)"><span v-text="batch.fileName"></span></a>
          </td>
          <td>{{batch.description}}</td>
          <td>{{batch.createdAt | cpStandardDate}}</td>
          <td>{{batch.submittedAt | cpStandardDate }}</td>
          <td>{{batch.credits | currency }}</td>
          <td>{{batch.bankName || 'n/a'}}</td>
          <td>{{batch.batchCount}}</td>
          <td>{{batch.transactionCount}}</td>
        </tr>
      </tbody>
    </table>
    <section class="cp-table-mobile">
      <div v-for="batch in batches">
        <div><span>File Name: </span><span><a @click="showFile(batch)"><span>{{batch.fileName}}</span></a></span></div>
        <div><span>Description: </span><span>{{batch.description}}</span></div>
        <div><span>Date Created: </span><span>{{batch.createdAt | cpStandardDate}}</span></div>
        <div><span>Date Paid: </span><span>{{batch.submittedAt || batch.createdAt | cpStandardDate}}</span></div>
        <div><span>Amount:  </span><span>{{batch.credits | currency}}</span></div>
        <div><span>Bank Name: </span><span>{{batch.bankName || 'n/a'}}</span></div>
        <div><span>Deposit Count: </span><span>{{batch.batchCount}}</span></div>
        <div><span>Transaction Count: </span><span>{{batch.transactionCount}}</span></div>
      </div>
    </section>
    <br>
    <div class="align-center">
      <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
      <cp-pagination :pagination="pagination" :callback="getBatches" :offset="2"></cp-pagination>
    </div>
  </div>
</template>

<script>
const Payman = require('../../resources/PaymanAPI.js')

module.exports = {
  name: 'CpPaymentFiles',
  routing: [
    {
      name: 'site.CpPaymentFiles',
      path: 'payment-files',
      meta: {
        title: 'Payment Files'
      },
      props: true
    }
  ],
  data: function () {
    return {
      tabs: [
        {
          name: 'OPEN',
          active: true
        },
        {
          name: 'CLOSED',
          active: false
        },
        {
          name: 'ALL',
          active: false
        }
      ],
      batches: {},
      selectedType: 'OPEN',
      loading: true,
      pagination: {
        current_page: 1,
        last_page: 0
      },
      asc: false,
      indexRequest: {
        order: 'ASC',
        column: 'name',
        per_page: 25,
        search_term: '',
        start_date: moment().subtract(10, 'days').format("YYYY-MM-DD"),
        end_date: moment().format('YYYY-MM-DD'),
            submitted: false
        },
        reverseSort: false,
    }
  },
  computed: {},
  mounted: function () {
    this.getBatches()
  },
  methods: {
      getBatches: function(){
        this.loading = true;
        this.batches = {};

        let submitted = (this.selectedType == 'OPEN' ? false : (this.selectedType == 'CLOSED' ? true : null))

        let request = {
          sortBy: '-created_at',
          page: this.pagination.current_page,
          count: this.indexRequest.per_page,
          startDate: moment(this.indexRequest.start_date).startOf('day').utc().format("YYYY-MM-DD HH:mm:ss"),
          endDate: moment(this.indexRequest.end_date).endOf('day').utc().format("YYYY-MM-DD HH:mm:ss"),
          submitted: submitted
        }

        Payman.getPaymentFiles(request)
          .then((response) => {
            if (response.error) {
                return this.$toast(response.message, { error: true, dismiss: false })
            }
            this.loading = false;
            this.batches = response.data;
            this.pagination.last_page = response.totalPage
          });
      },
      showFile: function (batch) {
        this.$router.push({ name: 'CpPaymentFile', params: { fileProp: batch, id: batch.id.toString() }})
      },
      changeTab (name) {
        this.selectedType = name
        this.getBatches()
      }
  },
  events: {
      'search_term': function (term) {
          this.indexRequest.search_term = term;
          this.pagination.current_page = 1;
          this.getBatches();
      }
  },
  components: {
    CpTableControls: require('../../cp-components-common/tables/CpTableControls.vue')
  }
}
</script>

<style lang="sass">
.open-index-wrapper {
    .space-between {
        display:flex;
        -webkit-display: flex;
        -webkit-justify-content: space-between;
        justify-content: space-between;
    }
        .input-position {
            height: 34px;
            padding: 6px 12px;
            margin-top: 10px;
        }
        input[type="date"] {
            border: none;
        }
            .icon {
                font-size: 18px;
            }
}
</style>
