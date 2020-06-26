<template>
    <div v-show="!loading">
        <search-box></search-box>
        <div v-show="fees.length == 0">No fees to report yet</div>
        <table class="cp-table-standard">
            <thead>
                <th @click="sortColumn('transaction_id')">Transaction ID
                    <span v-show="salesRequest.column == 'transaction_id'">
                        <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                        <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                    </span>
                </th>
                <th @click="sortColumn('date')">Date
                    <span v-show="salesRequest.column == 'date'">
                        <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                        <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                    </span>
                </th>
                <th @click="sortColumn('gross_amount')">Gross Amount
                    <span v-show="salesRequest.column == 'gross_amount'">
                        <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                        <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                    </span>
                </th>
                <th @click="sortColumn('payment_batch_id')">Payment Batch ID
                    <span v-show="salesRequest.column == 'payment_batch_id'">
                        <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                        <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                    </span>
                </th>
                <th @click="sortColumn('rep_id')">{{$getGlobal('title_rep').value}} ID
                    <span v-show="salesRequest.column == 'rep_id'">
                        <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                        <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                    </span>
                </th>
                <th @click="sortColumn('rep_name')">{{$getGlobal('title_rep').value}} Name
                    <span v-show="salesRequest.column == 'rep_name'">
                        <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                        <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                    </span>
                </th>
                <th @click="sortColumn('sales_tax')">Sales Tax
                    <span v-show="salesRequest.column == 'sales_tax'">
                        <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                        <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                    </span>
                </th>
                <th @click="sortColumn('shipping')">Shipping
                    <span v-show="salesRequest.column == 'shipping'">
                        <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                        <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                    </span>
                </th>
                <th @click="sortColumn('processing')">Processing
                    <span v-show="salesRequest.column == 'processing'">
                        <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                        <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                    </span>
                </th>
                <th @click="sortColumn('transaction_fee')">Transaction Fee
                    <span v-show="salesRequest.column == 'transaction_fee'">
                        <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                        <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                    </span>
                </th>
                <th @click="sortColumn('other')">Other
                    <span v-show="salesRequest.column == 'other'">
                        <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                        <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                    </span>
                </th>
                <th @click="sortColumn('net_payment')">Net Payment
                    <span v-show="salesRequest.column == 'net_payment'">
                        <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                        <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                    </span>
                </th>
                <th>View Transaction</th>
            </thead>
            <tbody>
                <tr v-for="fee in fees">
                    <td>{{ fee.transactionId }}</td>
                    <td>{{ fee.date | cpStandardDate ['time']}}</td>
                    <td>{{ fee.amount | currency('floor')}}</td>
                    <td>{{ fee.payment_batch_id }}</td>
                    <td>{{ fee.user.repId }}</td>
                    <td>{{ fee.user.name }}</td>
                    <td>{{ fee.salesTax | currency('floor')}}</td>
                    <td>{{ fee.order.total_shipping | currency('floor')}}</td>
                    <td>{{ fee.processing | currency('floor')}}</td>
                    <td>{{ fee.transactionFee | currency('floor')}}</td>
                    <td>{{ fee.other | currency('floor')}}</td>
                    <td>{{ fee.netAmount | currency('floor') }}</td>
                    <td><a href="/orders/{{ fee.order.receipt_id }}">View</a></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="align-center">
        <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
        <cp-pagination :pagination="pagination" :callback="getFees" :offset="2"></cp-pagination>
    </div>
</template>

<script>
const Ewallet = require('../../../resources/ewallet.js');

module.exports = {
    data: function () {
        return {
            loading: false,
            fees: [],
            pagination: {
                current_page: 1
            },
            asc: false,
            salesRequest: {
                order: 'ASC',
                column: 'name',
                per_page: 15,
                search_term: '',
                page: 1
            },
            quantity: [],
            reverseSort: false,
        }
    },
    computed: {},
    ready: function () {
        this.getFees();
    },
    methods: {
        getFees: function(){
            this.loading = true;
            this.salesRequest.page = this.pagination.current_page;
            Ewallet.processingFees(this.salesRequest).then((response) => {
                if (response.error) {
                      return this.$broadcast('errorMessage', response.message)
                }
                this.fees = response.data;
                this.pagination = response;
                this.loading = false;
            });
        },
        sortColumn: function(column) {
            this.reverseSort = !this.reverseSort
            this.salesRequest.column = column;
            this.asc = !this.asc;
            if (this.asc === true) {
                this.salesRequest.order = 'asc';
            } else {
                this.salesRequest.order = 'desc';
            }
            this.getFees();
        }
    },
    events: {
        'search_term': function (term) {
            this.salesRequest.search_term = term;
            this.pagination.current_page = 1;
            this.getFees();
        }
    },
    components: {
        'SearchBox': require('../../SearchBox.vue')
    }
}
</script>

<style lang="scss">

</style>
