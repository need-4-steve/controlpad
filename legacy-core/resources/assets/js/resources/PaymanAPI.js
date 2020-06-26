const Request = require('../resources/requestHandler.js')
// apiUrl will need to point to live eventually
const config = require('env').apis
const apiUrl = config.payman

module.exports = {
  getEWalletBalance (userId) {
    return Request.get(apiUrl + 'e-wallets/' + userId)
  },
  getBankAccount (userId) {
    return Request.get(apiUrl + 'user-accounts/' + userId)
  },
  updateBankAccount (params, userId) {
    return Request.put(apiUrl + 'user-accounts/' + userId, params)
  },
  getEWalletLedger (params) {
    return Request.get(apiUrl + 'reports/balance-ledger', params)
  },
  getPaymentFiles (params) {
    return Request.get(apiUrl + 'payment-files', params)
  },
  getPaymentFile (id) {
    return Request.get(apiUrl + 'payment-files/' + id)
  },
  markSubmitted (id) {
    return Request.get(apiUrl + 'payment-files/' + id + '/mark-submitted')
  },
  downloadNacha (id) {
    return Request.get(apiUrl + 'payment-files/' + id + '/file')
  },
  getValidations (params) {
    // Params: userId, paymentFileId, sortBy
    return Request.get(apiUrl + 'user-account-validations', params)
  },
  isRoutingValid (routing) {
    let multipliers = [3,7,1,3,7,1,3,7,1]
    let sum = 0
    for (let i = 0; i < 9; i++) {
      sum += (routing[i] * multipliers[i])
    }
    return sum % 10 == 0
  }
}
