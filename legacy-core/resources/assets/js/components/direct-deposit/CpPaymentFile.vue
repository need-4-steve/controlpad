<template>
  <div v-if="paymentFile" class="payment-file-details">
      <div class="align-center success-message" v-if="paidSuccess">
        <p>Successfully Marked as Paid</p>
      </div>
      <div class="flex-end">
        <a class="nacha" @click="downloadNacha()">Download NACHA File</a>
        <input v-show="!paymentFile.submittedAt" class="cp-button-standard" type="button" value="Mark as Paid" @click="markPaid()">
      </div>
      <table class="cp-table-standard top-table">
        <thead>
          <th>File Name</th>
          <th>Description</th>
          <th>Amount</th>
          <th>Bank Name</th>
          <th>Deposit Count</th>
          <th>Transaction Count</th>
          <th>Submitted Date</th>
        </thead>
        <tbody>
          <tr>
            <td>{{paymentFile.fileName}}</td>
            <td>{{paymentFile.description}}</td>
            <td>{{paymentFile.credits | currency}}</td>
            <td>{{paymentFile.bankName || 'n/a'}}</td>
            <td>{{paymentFile.entryCount}}</td>
            <td>{{paymentFile.transactionCount}}</td>
            <td>{{paymentFile.submittedAt | cpStandardDate}}</td>
          </tr>
        </tbody>
      </table><br>
      <component :fileId="paymentFile.id.toString()" :is="listComponent"></component>
  </div>
</template>

<script>
const Payman = require('../../resources/PaymanAPI.js')

module.exports = {
  name: 'CpPaymentFile',
  routing: [
    {
      name: 'site.CpPaymentFile',
      path: 'payment-files/:id',
      meta: {
        title: 'Payment File Details'
      },
      props: true
    }
  ],
  props: {
    fileProp: {
      type: Object,
      required: false,
      default () {
        return null
      }
    },
    id: {
      type: String
    }
  },
  data: function () {
    return {
      paidSuccess: false,
      paymentFile: null,
      listComponent: null
    }
  },
  created: function () {
    if (this.fileProp) {
      this.paymentFile = this.fileProp
      this.selectListComponent()
    } else {
      this.getFile()
    }
  },
  methods: {
    getFile () {
      Payman.getPaymentFile(this.id)
        .then((response) => {
          if (response.error) {
            return this.$toast('errorMessage', response)
          }
          this.paymentFile = response
          this.selectListComponent()
        })
    },
    selectListComponent () {
      let filename = this.paymentFile.fileName.toLowerCase()
      if (filename.indexOf('payouts') > -1) {
        this.listComponent = 'CpPaymentFileEntries'
      } else if (filename.indexOf('validations') > -1) {
        this.listComponent = 'CpValidationFileEntries'
      }
    },
    downloadNacha: function () {
      Payman.downloadNacha(this.id)
        .then((response) => {
          if (response.error) {
            return this.$toast('errorMessage', response)
          }
          var uri = window.URL.createObjectURL(new window.Blob([response], {
            type: 'text/plain'
          }))
          var a = document.createElement('a')
          document.body.appendChild(a)
          a.style = 'display: none'
          a.href = uri
          a.download = this.paymentFile.fileName.replace('.tsv', '.txt')
          a.click()
          window.URL.revokeObjectURL(uri)
          a.parentNode.removeChild(a)
        })
    },
    markPaid: function () {
      if (confirm('Are you sure?')) {
        Payman.markSubmitted(this.id)
          .then((response) => {
            if (response.error) {
              return this.$toast(response, { error: true, dismiss: false })
            }
            this.paidSuccess = true
            setTimeout(function () {
              this.paidSuccess = false
              window.location.href = '/payment-files'
            }.bind(this), 1500)
          })
      } else {
        return false
      }
    }
  },
  components: {
    CpPaymentFileEntries: require('./CpPaymentFileEntries.vue'),
    CpValidationFileEntries: require('./CpValidationFileEntries.vue')
  }
}
</script>

<style lang="sass">
    .payment-file-details {
        .top-table {
            border: 1px solid black;
        }
        input[type='button'] {
            margin:7px;
            &.paid {
                color: white;
                background: #393939;
            }
        }
        .bottom-table {
            font-size: 13px;
        }
        .input-position {
            height: 34px;
            padding: 6px 12px;
            margin-top: 10px;
        }
        input[type="date"] {
            border: none;
        }
        .gray-background {
            background: rgb(237, 239, 239);
        }
        .icon {
            font-size: 18px;
        }
    }
</style>
