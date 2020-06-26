<template>
    <div class="ewallet-wrapper">
        <search-box></search-box>
        <div v-show="salesTaxes.data.length == 0">No sales tax to report yet</div>
        <table class="cp-table-standard">
            <thead>
                <th @click="sortColumn('transaction_id')">Transaction ID
                    <span v-show="indexRequest.column == 'transaction_id'">
                        <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                        <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                    </span>
                </th>
                <th @click="sortColumn('batch_id')">Batch ID
                    <span v-show="indexRequest.column == 'batch_id'">
                        <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                        <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                    </span>
                </th>
                <th @click="sortColumn('date_collected')">Date Collected
                    <span v-show="indexRequest.column == 'date_collected'">
                        <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                        <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                    </span>
                </th>
                <th @click="sortColumn('zip_code')">Zip Code
                    <span v-show="indexRequest.column == 'zip_code'">
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
                <th>View Transaction</th>
            </thead>
            <tbody>
                <tr v-for="salesTax in salesTaxes">
                    <td>{{ salesTax.transactionId }}</td>
                    <td>{{ salesTax.batchId }}</td>
                    <td>{{ salesTax.dateCollected | cpStandardDate ['time']}}</td>
                    <td>{{ salesTax.order.billing_address.zip }}</td>
                    <td>{{ salesTax.amount | currency('floor') }}</td>
                    <td><a href="/orders/{{ salesTax.order.receipt_id }}">View</a></td>
                </tr>
            </tbody>
        </table>
        <div class="align-center">
            <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
            <cp-pagination :pagination="pagination" :callback="getSalesTax" :offset="2"></cp-pagination>
        </div>
    </div>
</template>

<script>
const Ewallet = require('../../../resources/ewallet.js');

module.exports = {
    data: function () {
        return {
            loading: false,
            salesTaxes: [],
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
        this.getSalesTax();
    },
    methods: {
        getSalesTax: function(){
            this.loading = true;
            this.salesTaxes = {};
            this.indexRequest.page = this.pagination.current_page;
            Ewallet.salesTax(this.indexRequest).then((response) => {
                if (response.error) {
                      return this.$broadcast('errorMessage', response.message)
                }
                this.salesTaxes = response.data;
                this.pagination = response;
                this.loading = false;
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
            this.getSalesTax();
        }
    },
    events: {
        'search_term': function (term) {
            this.indexRequest.search_term = term;
            this.pagination.current_page = 1;
            this.getSalesTax();
        }
    },
    components: {
        'SearchBox': require('../../SearchBox.vue')
    }
}
</script>

<style lang="scss">

</style>
