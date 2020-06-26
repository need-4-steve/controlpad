const Checkout = require('../resources/CheckoutAPIv0.js')
const Auth = require('auth')
let creatingCart = false

module.exports = {
  getCartPid (cartType) {
    return new Promise((resolve, reject) => {
      if (creatingCart) {
        reject(new Error('Already Creating Cart'))
      }
      const cartSetup = this.getCartSetup(cartType)
      const cartPid = this.getCartFromStorage(cartType, cartSetup.sellerPid)

      if (cartPid && cartPid.length > 0) {
        resolve(cartPid)
      } else {
        creatingCart = true
        this.createCart(cartSetup.buyerPid, cartSetup.sellerPid, cartSetup.inventoryUserPid, cartType)
          .then(response => {
            if (response.error) {
              reject(response)
            } else {
              this.setCartInStorage(response.pid, cartType, cartSetup.sellerPid)
              resolve(response.pid)
            }
            creatingCart = false
          })
          .catch(error => {
            reject(error)
          })
      }
    })
  },
  // TODO Update for all cases
  getCartSetup (cartType) {
    let buyerPid = null
    let sellerPid = null
    let inventoryUserPid = null

    const userRole = this.getUserRole()
    const userPid = this.getUserPid()
    const corpPid = this.getCorporatePid()

    console.log('cart type is ', cartType)
    console.log('User role is ', userRole)

    switch (cartType) {
      case 'wholesale':
        buyerPid = userPid
        sellerPid = corpPid
        inventoryUserPid = corpPid
        break
      case 'custom-retail':
        buyerPid = userPid
        sellerPid = userPid
        inventoryUserPid = userPid
        break
      case 'custom-personal':
        if (userRole === 'Admin' || userRole === 'Superadmin') {
          buyerPid = corpPid
          sellerPid = corpPid
          inventoryUserPid = corpPid
        } else {
          buyerPid = userPid
          sellerPid = userPid
          inventoryUserPid = userPid
        }
        break
      case 'custom-corp':
        buyerPid = corpPid
        sellerPid = corpPid
        inventoryUserPid = corpPid
        break
      case 'custom-affiliate':
        buyerPid = userPid
        sellerPid = userPid
        inventoryUserPid = corpPid
        break
      case 'rep-transfer':
        buyerPid = userPid
        sellerPid = userPid
        inventoryUserPid = userPid
        break
    }

    return {
      'buyerPid': buyerPid,
      'sellerPid': sellerPid,
      'inventoryUserPid': inventoryUserPid
    }
  },
  getUserRole () {
    return Auth.getClaims().role
  },
  getUserPid () {
    return Auth.getClaims().userPid
  },
  getCorporatePid () {
    return window.Vue.prototype.$getGlobal('company_pid').value
  },
  // Local Storage key for the cart PID is Cart.CartType.SellerPid
  getCartFromStorage (cartType, sellerPid) {
    console.log('got pid from local storage')
    const cartPidKey = ['Cart', cartType, sellerPid].join('.')
    let cartPid = window.localStorage.getItem(cartPidKey)
    return cartPid
  },
  createCart (buyerPid, sellerPid, inventoryUserPid, cartType) {
    console.log('got pid from api')
    return Checkout.postCart({
      'buyer_pid': buyerPid,
      'seller_pid': sellerPid,
      'inventory_user_pid': inventoryUserPid,
      'type': cartType
    })
  },
  setCartInStorage (pid, cartType, sellerPid) {
    const cartPidKey = ['Cart', cartType, sellerPid].join('.')
    window.localStorage.setItem(cartPidKey, pid)
  },
  getCreatingCart () {
    return creatingCart
  }
}
