<template lang="html">
    <div class="sales-index-wrapper">
        <div class="action-btn-wrapper">
            <a class="cp-button-link" @click="showDownloadModal()">Download</a>
        </div>
        <cp-tabs v-if="tabs.length > 1" :items="tabs" :callback="changeTab"></cp-tabs>
        <cp-data-table
            :table-data="sales"
            :table-columns="tableColumns"
            :pagination="pagination"
            :recall-data="getSales"
            :request-params="indexRequest"
            :options="{
                tableControls: true,
                datePicker: true,
                dateRange: dates,
            }">
            <a slot="pdf" slot-scope="{row}" v-if="row.type_id !== 6 && row.type_id !== 7 || Auth.hasAnyRole('Superadmin','Admin')" @click="printOrder(row.id)"><i class='mdi mdi-file'></i></a>
            <template slot="receipt_id" slot-scope="{row}">
                <span v-if="row.type_id !== 6 && row.type_id !== 7 || Auth.hasAnyRole('Superadmin','Admin')">
                    <a :href="'/orders/'+row.receipt_id">{{row.receipt_id}}</a>
                </span>
                <span v-else>{{row.receipt_id}}</span>
            </template>
                <span slot="last_name" slot-scope="{row}">{{ row.buyer_first_name }} {{ row.buyer_last_name }}</span>
            <template slot="subtotal_price" slot-scope="{row}">
                <span v-if="row.type_id !== 6  && row.type_id !== 7 || Auth.hasAnyRole('Superadmin','Admin')">{{ row.subtotal_price | currency }}</span>
                <span v-else>{{fulfilledAmount(row.lines) | currency}}</span>
            </template>
            <template slot="cash" slot-scope="{row}">
                <span>{{ row.cash ? 'Yes' : 'No' }}</span>
            </template>
            <span slot="type_id" slot-scope="{row}">{{row.type_description}}</span>
        </cp-data-table>
      <cp-order-download-modal v-if="orderDownloadModal" @close="orderDownloadModal = false"
        :title="downloadTitle" :start-date="dates.start_date"
        :end-date="dates.end_date" :type="downloadType"
        :sort-column="indexRequest.column" :sort-order="indexRequest.order"></cp-order-download-modal>
    </div>
</template>

<script>
const Orders = require('../../resources/OrdersAPIv0.js')
const moment = require('moment')
const Auth = require('auth')
const CpOrdersFile = require('../../libraries/CpOrdersFile.js')

module.exports = {
  name: 'CpSalesIndex',
  routing: [
    {
      name: 'site.CpSalesIndex',
      path: 'sales',
      meta: {
        title: 'Sales'
      },
      props: true
    }
  ],
  data: function () {
    return {
      downloadTitle: 'Sales Download',
      downloadType: 'sales',
      orderDownloadModal: false,
      noResults: false,
      loading: false,
      sales: [],
      tabs: [{
        name: 'SALES',
        active: true
      }],
      tableColumns: [
        { header: 'PDF', field: 'pdf', sortable: false },
        { header: 'Receipt ID', field: 'receipt_id', sortable: true },
        { header: 'Date of Sale', field: 'created_at', filter: 'cpStandardDate|true' },
        { header: 'Customer Name', field: 'last_name', sortable: true },
        { header: 'Amount', field: 'subtotal_price', sortable: true, filter: 'currency' },
        { header: 'Total Tax', field: 'total_tax', sortable: true, filter: 'currency' },
        { header: 'Shipping', field: 'total_shipping', sortable: true, filter: 'currency' },
        { header: 'Total Amount', field: 'total_price', sortable: true, filter: 'currency' },
        { header: 'Cash Sale', field: 'cash', sortable: true },
        { header: 'Order Type', field: 'type_id', sortable: true },
        { header: 'Status', field: 'status', sortable: true, htmlclass: 'capitalize'}
      ],
      pagination: {
        per_page: 15
      },
      dates: {
        start_date: moment().subtract(10, 'days').format('YYYY-MM-DD'),
        end_date: moment().format('YYYY-MM-DD')
      },
      indexRequest: {
        start_date: moment().subtract(10, 'days').format('YYYY-MM-DD'),
        end_date: moment().format('YYYY-MM-DD'),
        order: 'DESC',
        column: 'created_at',
        per_page: 15,
        search_term: '',
        page: 1,
        type_id: [3,4,9]
      },
      Auth: Auth
    }
  },
  created () {
    if (this.$getGlobal('rep_transfer').show) {
      this.tabs.push({name: 'TRANSFERS', active: false})
    }
  },
  mounted () {
    Auth.hasAnyRole('Superadmin', 'Admin') ? this.indexRequest.per_page = '100' : this.indexRequest.per_page = '15'
    this.getSales()
  },
  methods: {
    getSales: function () {
      this.loading = true
      let request = JSON.parse(JSON.stringify(this.indexRequest))
      request.page = this.pagination.current_page
      request.sort_by = this.indexRequest.column.toLowerCase()
      request.in_order = this.indexRequest.order.toLowerCase()
      request.start_date = moment(this.dates.start_date, 'YYYY-MM-DD').startOf('day').utc().format('YYYY-MM-DD HH:mm:ss.SSS')
      request.end_date = moment(this.dates.end_date, 'YYYY-MM-DD').endOf('day').utc().format('YYYY-MM-DD HH:mm:ss.SSS')

      this.sales = []
      Orders.getOrders(request)
        .then((response) => {
          if (response.error) {
            return this.$toast(response.message, { error: true, dismiss: true })
          }
          if (response.total === 0) {
            this.noResults = true
            setTimeout(() => {
              this.noResults = false
            }, 3000)
          }
          this.loading = false
          this.sales = response.data
          response.per_page = parseInt(response.per_page)
          this.pagination = response
        })
    },
    changeTab (name) {
      this.resetIndexRequest()
      console.log(name + ' selected')
      switch(name) {
        case 'SALES':
          this.downloadType = 'sales'
          this.indexRequest.type_id = [3,4,9]
          break
        case 'TRANSFERS':
          this.downloadType = 'transfer'
          this.indexRequest.type_id = [11]
          break
      }
      this.getSales()
    },
    resetIndexRequest () {
      this.indexRequest = {
        start_date: moment().subtract(10, 'days').format('YYYY-MM-DD'),
        end_date: moment().format('YYYY-MM-DD'),
        order: 'DESC',
        column: 'created_at',
        per_page: 15,
        search_term: '',
        page: 1
      }
      this.pagination.current_page = 1
    },
    fulfilledAmount: function (lines) {
      var amount = 0
      lines.forEach(function (line) {
        amount = amount + line.price
      })
      this.saleAmount = amount
      return this.saleAmount
    },
    showDownloadModal () {
      this.orderDownloadModal = true
    },
    printOrder: function (orderId) {
      new CpOrdersFile(null, ['pdf'], 'order', {orderId: orderId}).run()
    }
  },
  components: {
    CpOrderDownloadModal: require('../orders/CpOrderDownloadModal.vue'),
    CpTotalsBanner: require('../reports/CpTotalsBanner.vue'),
    CpTableControls: require('../../cp-components-common/tables/CpTableControls.vue')
  }
}
</script>

<style lang="scss">
    @import "resources/assets/sass/var.scss";
    .sales-index-wrapper {
        .space-between {
            margin-bottom: 10px;
        }
        .cp-button-standard {
          &.download {
            background: #fff;
            p {
               color: #337ab7;
            }
            &:hover {
              background: $cp-main;
              a {
                color: #fff;
                text-decoration: none;
              }
            }
          }
        }
        .action-btn-wrapper {
            margin-bottom: 10px;
        }
    }
</style>
