window.sms.vFilter('ceLedgerType', (require) => {
  return function (typeId) {
    switch (typeId) {
      case '1':
        return 'Nacha Payment'
      case '2':
        return 'Reward'
      case '3':
        return 'Commission'
      case '4':
        return 'Transferred'
      case '7':
        return 'Bonus'
      case '8':
        return 'Custom Payout'
      case '9':
        return 'Repair'
      case '10':
        return 'Signup Bonus'
      default:
        return typeId
    }
  }
})
