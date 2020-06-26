<template lang="html">
    <div class="sales-index-wrapper">
        <br>

        <!-- totals banner -->
        <cp-totals-banner totals-title="All Rep Transfers"
                          :totals="[{title:'Total Rep Transfers', amount: salesTotals.rep_transfers}]">
        </cp-totals-banner>

        <cp-table-controls :date-picker="true"
                           :date-range="dates"
                           :index-request="indexRequest"
                           :resource-info="pagination"
                           :get-records="getSalesAndTotals"></cp-table-controls>

        <!-- data table  -->

        <table class="cp-table-standard desktop">
            <thead>
            <th>PDF</th>
            <th @click="sortColumn('receipt_id')">
                Receipt ID
                <span v-show="indexRequest.column == 'receipt_id'">
                    <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                    <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                </span>
            </th>
            <th @click="sortColumn('created_at')">
                Date of Sale
                <span v-show="indexRequest.column == 'created_at'">
                    <span v-show="!reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                    <span v-show="reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                </span>
            </th>
            <th @click="sortColumn('last_name')">
                Customer Name
                <span v-show="indexRequest.column == 'last_name'">
                    <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                    <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                </span>
            </th>
            <th @click="sortColumn('subtotal_price')">
                Subtotal
                <span v-show="indexRequest.column == 'subtotal_price'">
                    <span v-show="reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                    <span v-show="!reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                </span>
            </th>
            <th @click="sortColumn('total_tax')">
                Tax
                <span v-show="indexRequest.column == 'total_tax'">
                    <span v-show="reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                    <span v-show="!reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                </span>
            </th>
            <th @click="sortColumn('total_shipping')">
                Shipping
                <span v-show="indexRequest.column == 'total_shipping'">
                    <span v-show="reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                    <span v-show="!reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                </span>
            </th>
            <th @click="sortColumn('total_price')">
                Total
                <span v-show="indexRequest.column == 'total_price'">
                    <span v-show="reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                    <span v-show="!reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                </span>
            </th>
            <th @click="sortColumn('cash')">
                Cash Sale
                <span v-show="indexRequest.column == 'cash'">
                    <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                    <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                </span>
            </th>
            <th @click="sortColumn('type_id')">
                Order Type
                <span v-show="indexRequest.column == 'type_id'">
                    <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                    <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                </span>
            </th>
            </thead>
            <tbody>
                <tr v-for="sale in sales">
                    <td v-if="sale.type_id !== 6 && sale.type_id !== 7 || Auth.hasAnyRole('Superadmin','Admin')">
                        <a @click="printOrder(sale.id)"><i class='mdi mdi-file'></i></a>
                    </td>
                    <td v-else></td>
                    <td v-if="sale.type_id !== 6 && sale.type_id !== 7 || Auth.hasAnyRole('Superadmin','Admin')"><a v-bind:href="'/orders/' + sale.receipt_id">{{ sale.receipt_id }}</a></td>
                    <td v-else>{{ sale.receipt_id }}</td>
                    <td>{{ sale.created_at | cpStandardDate}}</td>
                    <td>{{ sale.customer_last_name }}, {{ sale.customer_first_name }}</td>
                    <td v-if="sale.type_id !== 6  && sale.type_id !== 7 || Auth.hasAnyRole('Superadmin','Admin')">{{ sale.subtotal_price | currency }}</td>
                    <td v-else>{{fulfilledAmount(sale.lines) | currency}}</td>
                    <td>{{ sale.total_tax | currency }}</td>
                    <td>{{ sale.total_shipping | currency }}</td>
                    <td>{{ sale.total_price | currency }}</td>
                    <td>
                        <span v-if="sale.cash === 1">Yes</span>
                        <span v-if="sale.cash === 0">No</span>
                    </td>
                    <td>{{ sale.order_type.name }}</td>
                </tr>
            </tbody>
        </table>
        <section class="cp-table-mobile">
            <div v-for="sale in sales">
                <div>
                    <span v-if="sale.type_id !== 6 && sale.type_id !== 7 || Auth.hasAnyRole('Superadmin','Admin')">
                        PDF:
                        <a @click="printOrder(sale.id)"><i class='mdi mdi-file-empty'></i></a>
                    </span>
                </div>
                <div>
                    <span v-if="sale.type_id !== 6 && sale.type_id !== 7 || Auth.hasAnyRole('Superadmin','Admin')">
                        Receipt Id:
                        <a v-bind:href="'/orders/' + sale.receipt_id">{{ sale.receipt_id }}</a>
                    </span><span v-else>{{ sale.receipt_id }}></span>
                </div>
                <div><span>Date of Sale: </span><span>{{ sale.created_at | cpStandardDate}}</span></div>
                <div><span>Customer Name: </span><span>{{ sale.customer_last_name }}, {{ sale.customer_first_name }}</span></div>
                <div>
                    <span> Subtotal: </span><span v-if="sale.type_id !== 6  && sale.type_id !== 7 || Auth.hasAnyRole('Superadmin','Admin')">{{ sale.subtotal_price | currency }}</span>
                    <span v-else>{{fulfilledAmount(sale.lines) | currency}}></span>
                </div>
                <div><span>Tax: </span><span>{{ sale.total_tax | currency }}</span></div>
                <div><span>Shipping: </span><span>{{ sale.total_shipping | currency }}</span></div>
                <div><span>Total: </span><span>{{ sale.total_price | currency }}</span></div>
                <div>
                    <span>Cash Sale: </span>
                    <span v-if="sale.cash === 1">Yes</span>
                    <span v-if="sale.cash === 0">No</span>
                </div>
                <div><span>Order Type: </span><span>{{ sale.order_type.name }}</span></div>
            </div>
        </section>
        <div class="align-center">
            <div class="no-results" v-if="noResults">
                <span>No results for this timeframe</span>
            </div>
            <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
            <cp-pagination :pagination="pagination"
                           :callback="getSales"
                           :offset="2"></cp-pagination>
        </div>
    </div>
</template>

<script>
    const Sales = require('../../resources/sales.js')
    const moment = require('moment')
    const Auth = require('auth')
    const CpOrdersFile = require('../../libraries/CpOrdersFile.js')

    module.exports = {
        data: function () {
            return {
                noResults: false,
                loading: false,
                sales: [],
                pagination: {
                    per_page: 15
                },
                asc: false,
                indexRequest: {
                    start_date: moment().subtract(10, 'days').format('YYYY-MM-DD'),
                    end_date: moment().format('YYYY-MM-DD'),
                    order: 'DESC',
                    column: 'created_at',
                    per_page: 15,
                    search_term: '',
                    page: 1,
                    name: 'customer'
                },
                reverseSort: true,
                Auth: Auth,
                salesTotals: {}
            }
        },
        props: {
            dates: {}
        },
        computed: {},
        mounted: function () {
            Auth.hasAnyRole('Superadmin', 'Admin') ? this.indexRequest.per_page = '100' : this.indexRequest.per_page = '15'
            this.getSalesAndTotals()
        },
        methods: {
            downloadcsvreport(search_term, column, order, start_date, end_date) {
                const regex = /attachment;\s*filename=(['"])([^\1]*)\1/i
                const url = '/api/v1/report/csv/corp?search_term=' + search_term + '&:column=' + column + '&order=' + order + '&start_date=' + start_date + '&end_date=' + end_date;
                const download = '/api/v1/report/csv/corp?search_term=' + search_term + '&:column=' + column + '&order=' + order + '&start_date=' + start_date + '&end_date=' + end_date;
                const anchor = document.createElement('a')
                const token = (window.localStorage.getItem('jwt_token') || '')
                const headers = new window.Headers()
                const options = { headers }
                options.credentials = 'include'
                headers.append('Authorization', `Bearer ${token}`)
                let filename = 'filename.ext'
                this.loading = true
                window.fetch(url, options)
                    .then(res => {
                        filename = ((res.headers.get('Content-Disposition') || '').match(regex) || [])[2]
                        return res.blob()
                    })
                    .then((blobby) => {
                        const blob = new Blob([blobby], {
                            type: 'text/csv'
                        });
                        var iOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
                        if (iOS) {
                            let url = window.URL.createObjectURL(blob);
                            window.open(url, filename, "_blank");
                        }
                        else {
                            const blobUrl = window.URL.createObjectURL(blob)
                            anchor.href = blobUrl
                            anchor.download = filename
                            anchor.target = '_self'
                            anchor.style.display = 'none'
                            window.document.body.append(anchor)
                            anchor.click()
                            window.URL.revokeObjectURL(blobUrl)
                            anchor.remove()
                        }
                        this.loading = false
                    })
            },
            sendemailcsvreport(search_term, column, order, start_date, end_date) {
                const regex = /attachment;\s*filename=(['"])([^\1]*)\1/i
                const url = '/api/v1/report/csv/corpsendmail?search_term=' + search_term + '&:column=' + column + '&order=' + order + '&start_date=' + start_date + '&end_date=' + end_date;
                const download = '/api/v1/report/csv/corpsendmail?search_term=' + search_term + '&:column=' + column + '&order=' + order + '&start_date=' + start_date + '&end_date=' + end_date;
                const anchor = document.createElement('a')
                const token = (window.localStorage.getItem('jwt_token') || '')
                const headers = new window.Headers()
                const options = { headers }
                options.credentials = 'include'
                headers.append('Authorization', `Bearer ${token}`)
                let filename = 'filename.ext'
                this.loading = true
                window.fetch(url, options)
                    .then(res => {
                        filename = ((res.headers.get('Content-Disposition') || '').match(regex) || [])[2]
                        return res.blob()
                    })
                    .then((blobby) => {
                        alert("mail sent successfully");
                        this.loading = false
                    })
            },
            getRepTransferTotal() {
                Sales.getRepTransferTotals(this.indexRequest)
                    .then((response) => {
                        this.salesTotals = response
                    })
            },
            getSalesAndTotals() {
                this.getSales()
                this.getRepTransferTotal()
            },
            getSales: function () {
                this.loading = true
                this.indexRequest.start_date = moment(this.dates.start_date).format('YYYY-MM-DD')
                this.indexRequest.end_date = moment(this.dates.end_date).format('YYYY-MM-DD')
                this.indexRequest.page = this.pagination.current_page
                this.sales = {}
                Sales.getRepTransferIndex(this.indexRequest)
                    .then((response) => {
                        if (response.error) {
                            return this.$toast(response.message, { error: true })
                        }
                        if (response.total === 0) {
                            this.noResults = true
                            setTimeout(function () {
                                this.noResults = false
                            }.bind(this), 3000)
                        }
                        this.loading = false
                        this.sales = response.data
                        response.per_page = parseInt(response.per_page)
                        this.pagination = response
                    })
            },
            fulfilledAmount: function (lines) {
                var amount = 0
                lines.forEach(function (line) {
                    amount = amount + line.price
                })
                this.saleAmount = amount
                return this.saleAmount
            },
            sortColumn: function (column) {
                this.reverseSort = !this.reverseSort
                this.indexRequest.column = column
                this.asc = !this.asc
                if (this.asc === true) {
                    this.indexRequest.order = 'asc'
                } else {
                    this.indexRequest.order = 'desc'
                }
                this.getSales()
            },
            printOrder: function (orderId) {
                new CpOrdersFile(null, ['pdf'], 'order', { orderId: orderId }).run()
            }
        },
        components: {
            CpTotalsBanner: require('../reports/CpTotalsBanner.vue'),
            CpTableControls: require('../../cp-components-common/tables/CpTableControls.vue')
        }
    }
</script>
