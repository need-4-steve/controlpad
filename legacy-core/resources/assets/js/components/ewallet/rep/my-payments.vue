<template>
    <div class="ewallet-wrapper">
        <search-box :placeholder="'Search Payments'"></search-box>
        <div>
            <table class="cp-table-standard">
                <thead>
                    <th @click="sortColumn('status')">Status
                        <span v-show="indexRequest.column == 'status'">
                            <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                            <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                        </span>
                    </th>
                    <th @click="sortColumn('order.receipt_id')">Invoice ID
                        <span v-show="indexRequest.column == 'order.receipt_id'">
                            <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                            <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                        </span>
                    </th>
                    <th @click="sortColumn('createdAt')">Date of Sale
                        <span v-show="indexRequest.column == 'CreatedAt'">
                            <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                            <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                        </span>
                    </th>
                    <th @click="sortColumn('amount')">Amount
                        <span v-show="indexRequest.column == 'amount'">
                            <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                            <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                        </span>
                    </th>
                    <th @click="sortColumn('accountHolder')">Card Holder
                        <span v-show="indexRequest.accountHolder == 'cardHolderName'">
                            <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                            <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                        </span>
                    </th>
                    <th @click="sortColumn('fees')">Fees
                        <span v-show="indexRequest.column == 'fees'">
                            <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                            <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                        </span>
                    </th>
                    <th @click="sortColumn('salesTax')">Sales Tax
                        <span v-show="indexRequest.column == 'salesTax'">
                            <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                            <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                        </span>
                    </th>
                    <th @click="sortColumn('shipping')">Shipping
                        <span v-show="indexRequest.column == 'shipping'">
                            <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                            <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                        </span>
                    </th>
                    <th @click="sortColumn('order.paid_at')">Date Paid
                        <span v-show="indexRequest.column == 'order.paid_at'">
                            <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                            <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                        </span>
                    </th>
                    <th @click="sortColumn('netAmount')">Net Amount
                        <span v-show="indexRequest.column == 'netAmount'">
                            <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                            <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                        </span>
                    </th>
                    <th @click="sortColumn('cashSale')">Cash Sale
                        <span v-show="indexRequest.column == 'cashSale'">
                            <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                            <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                        </span>
                    </th>
                    <th @click="sortColumn('orderType.name')">Source of Sale
                        <span v-show="indexRequest.column == 'orderType.name'">
                            <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                            <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                        </span>
                    </th>
                </thead>
                <tbody>
                    <tr v-for="transaction in transactions">
                        <td>{{ transaction.status }} </td>
                        <td><a href="/ewallet/invoice-details/{{ transaction.order.receipt_id }}">{{ transaction.order.receipt_id }}</a></td>
                        <td>{{ transaction.createdAt | cpStandardDate}}</td>
                        <td>{{ transaction.amount | currency('floor') }}</td>
                        <td>{{ transaction.cardHolderName || transaction.order.customer.first_name}}</td>
                        <td>{{ transaction.fees | currency('floor') }}</td>
                        <td>{{ transaction.salesTax | currency('floor') }}</td>
                        <td>{{ transaction.order.total_shipping | currency('floor') }}</td>
                        <td>{{ transaction.datePaid | cpStandardDate}}</td>
                        <td>{{ transaction.netAmount | currency('floor') }}</td>
                        <td>{{ transaction.transactionCharges }}</td>
                        <td>{{ transaction.order.source || 'Web' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="align-center">
            <div class="no-results" v-if="transactions.length === 0">No payments to report yet</div>
            <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
        </div>
    </div>
</template>

<script>
const EWallet = require('../../../resources/ewallet.js')

module.exports = {
    data: function () {
        return {
            loading: false,
            transactions: [],
            pagination: {
                current_page: 1
            },
            asc: false,
            indexRequest: {
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
        Auth.hasAnyRole('Superadmin', 'Admin') ? this.indexRequest.per_page = '100' : this.indexRequest.per_page = '15'
        this.getTransactions();
    },
    methods: {
        getTransactions: function(){
            this.loading = true;
            this.indexRequest.page = this.pagination.current_page;
            EWallet.payments(this.indexRequest)
            .then((response) => {
                if (response.error) {
                    return this.$broadcast('errorMessage', response.message)
                }
                this.transactions = response.data;
                this.pagination = response.data;
                this.loading = false;
            })
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
            this.getTransactions();
        }
    },
    events: {
        'search_term': function (term) {
            this.indexRequest.search_term = term;
            this.pagination.current_page = 1;
            this.getTransactions();
        }
    },
    components: {
        'SearchBox': require('../../SearchBox.vue')
    }
}
</script>

<style lang="scss">

</style>
