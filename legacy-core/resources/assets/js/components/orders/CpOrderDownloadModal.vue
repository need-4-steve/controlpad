<template>
  <div class="cp-order-download-wrapper">
    <transition name="modal">
        <section class="cp-modal-standard">
            <div class="cp-modal-body">


            <h2>{{ title }}</h2>

            <div class="modal-body">
                <div v-if="!processing">
                    <cp-input custom-class="input-width" label="File Name:  " type="text" v-model="fileName"></cp-input>
                    <div>
                        <label>PDF:</label>
                        <input type="checkbox" value="pdf" v-model="checkedNames">
                    </div>
                    <div>
                        <label>CSV:</label>
                        <input type="checkbox"  value="csv" v-model="checkedNames">
                    </div>
                    <span v-if="errorMessage !== ''" class="error-message"><h4>{{errorMessage}}</h4></span>
                </div>
                <div class="align-center" v-if="processing">
                    <img class="loading" :src="$getGlobal('loading_icon').value">
                </div>

                <div class="buttton-wrapper">
                    <button class="cp-button-standard" @click="close">
                      Cancel
                    </button>
                    <button class="cp-button-standard" @click="start" :disabled="processing">
                      Create
                    </button>
                </div>
              </div>
            </div>
        </section>
    </transition>
  </div>
</template>

<script>
  const CpOrdersFile = require('../../libraries/CpOrdersFile.js')
  const Auth = require('auth')
  const Moment = require('moment-timezone')
  module.exports = {
    fileGen: null,
    props: {
      title: {type: String, required: true},
      type: {type: String, required: true},
      orderId: [String, Number],
      status: String,
      startDate: String,
      endDate: String,
      searchTerm: String,
      sortColumn: {
        Type: String,
        default: function () {
          return 'created_at'
        }
      },
      sortOrder: {
        Type: String,
        default: function () {
          return 'asc'
        }
      }
    },
    data: function () {
      return {
        errorMessage: '',
        checkedNames: [],
        processing: false,
        fileName: null
      }
    },
    components: {
      CpInput: require('../../cp-components-common/inputs/CpInput.vue')
    },
    created: function () {
      var filePrefix = 'Orders-'
      if (['invoice', 'invoice-list'].includes(this.type)) {
        filePrefix = 'Invoices-'
      }
      this.fileName = filePrefix + Moment().format('YYYYMMDD')
    },
    methods: {
      start () {
        this.processing = true
        this.createFiles()
      },
      createFiles () {
        if (!this.processing) {
          return
        }

        var params = null
        if (['invoice', 'order', 'picklist-single'].includes(this.type)) {
          // Single order
          params = {orderId: this.orderId}
        } else {
          params = {
            start_date: this.startDate,
            end_date: this.endDate,
            sort_by: this.sortColumn,
            search_term: this.searchTerm,
            in_order: (this.sortOrder ? this.sortOrder.toLowerCase() : 'asc')
          }
          if (Auth.hasAnyRole('Admin', 'Superadmin')) {
            params.type_id = [1, 2, 5, 6, 7, 9]
          } else {
            switch (this.type) {
              case 'sales':
                params.type_id = [3, 4, 9]
                params.seller_id = Auth.getAuthId()
                break
              case 'purchase':
                params.buyer_id = Auth.getAuthId()
                params.type_id = [1]
                break
              case 'transfer':
                params.type_id = [11]
                params.seller_id = Auth.getAuthId()
                break
              default:
                params.seller_id = Auth.getAuthId()
                params.type_id = [3, 4, 10] // TODO should affiliate be included? it shows up on the backoffice
                break
            }
          }
          if (this.status && this.status !== 'all') {
            params.status = this.status
          }
        }
        this.ordersFile = new CpOrdersFile(this.$data.fileName, this.checkedNames, this.type, params)
        this.ordersFile.run((response) => {
          if (response.error) {
            this.processing = false
            // TODO show error
            this.errorMessage = response.message
          } else {
            this.close()
          }
        })
      },
      close () {
        this.processing = false
        if (this.ordersFile) {
          this.ordersFile.cancel()
          this.ordersFile = null
        }
        this.$emit('close')
      }
    }
  }
</script>

<style lang="scss">
.cp-order-download-wrapper{
  .error-message{
    color: tomato;
  }
  .input-width{
    margin-left: 5px;
    width: 200px;
  }
  .buttton-wrapper{
    text-align: right;
    padding-top: 10px;
  }
}
</style>
