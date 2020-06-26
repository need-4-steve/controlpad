window.sms.addModule('auth', (require, exports, module) => {
  const docs = require('assets/js/resources/docs.js')
  const doc = docs.ServiceDocumenterFactory('authentication')
  const Pid = require('assets/js/resources/pid.js')
  const env = require('env')

  const ADMIN_OWNER_ID = 1

  let claims
  const setToken = (token) => {
    if (!token) throw Error(`NO_TOKEN`)
    window.localStorage.setItem('jwt_token', token)
    const [, p2 = ''] = token.split('.')
    if (!p2) throw Error(`INVALID_TOKEN`)
    try {
      claims = JSON.parse(window.atob(p2))
      window.localStorage.setItem('auth-id', claims.sub)
      window.localStorage.setItem('auth-role', claims.role)
      if (window.Vue.prototype.$events) window.Vue.prototype.$events.$emit('set-token', claims)
    } catch (err) {
      claims = null
      throw Error(`INVALID_TOKEN`)
    }
  }

  const getToken = () => {
    return window.localStorage.getItem('jwt_token')
  }

  const tmpToken = window.localStorage.getItem('jwt_token')
  if (tmpToken && tmpToken !== 'undefined') {
    setToken(tmpToken)
  }

  const getHeaders = (includeAuth = false) => {
    let headers = {
      'X-Cp-Request-Id': Pid.create()
    }
    if (includeAuth) {
      let orgId = (claims || {}).orgId
      let token = getToken()
      if (orgId) {
        headers['X-Cp-Org-Id'] = orgId
      }
      if (token) {
        headers['Authorization'] = 'Bearer ' + token
      }
    }
    return headers
  }

  module.exports = {
    // LOGIN STUFF
    redirect ({ url }) {
      return window.Vue.http({
        method: 'POST',
        url: env.apis.external + `assert-authorized-domain`,
        headers: getHeaders(),
        body: { url }
      }).then(res => {
        url = decodeURIComponent(url)
        url += url.includes('?') ? '&' : '?'
        url += `cp_token=${getToken()}`
        window.location.href = url
      }).catch(reason => {
        console.dir(reason)
      })
    },
    login ({
      email,
      password,
      redirect = null
    }) {
      this.removeFromStorage('Cart.')
      return window.Vue.http({
        method: 'POST',
        url: env.apis.external + 'authenticate',
        headers: getHeaders(),
        body: {
          email,
          password,
          redirect
        }
      }).then(res => {
        var token = res.body.cp_token
        setToken(token)
        if (redirect) {
          redirect = decodeURIComponent(redirect)
          redirect += redirect.includes('?') ? '&' : '?'
          redirect += `cp_token=${token}`
          window.location.href = redirect
        } else {
          return res.body
        }
      })
    },
    setJwtToken (token) {
      setToken(token)
    },
    refreshToken () {
      return window.Vue.http({
        method: 'GET',
        url: env.apis.external + `refresh-token`,
        headers: getHeaders(true)
      }).then(res => {
        setToken(res.body.cp_token)
        return res.body
      })
    },
    loginAs (id) {
      this.removeFromStorage('Cart.')
      return window.Vue.http({
        method: 'POST',
        url: env.apis.external + 'login-as/' + id,
        headers: getHeaders(true)
      }).then(res => {
        setToken(res.body.cp_token)
        return res.body
      })
    },
    revertLoginAs () {
      this.removeFromStorage('Cart.')
      return window.Vue.http({
        method: 'POST',
        url: env.apis.external + 'revert-login-as',
        headers: getHeaders(true)
      }).then(res => {
        setToken(res.body.cp_token)
        return res.body
      })
    },
    logout () {
      window.localStorage.removeItem('jwt_token')
      this.removeFromStorage('Cart.')
      claims = null
      return window.Vue.http({
        method: 'POST',
        url: env.apis.external + 'logout',
        headers: getHeaders()
      }).then(res => res.body)
    },
    sendResetLink: function (body) {
      return window.Vue.http({
        method: 'POST',
        url: env.apis.external + 'password/email',
        headers: getHeaders(true),
        body
      }).then(res => res.body)
    },
    resetPassword: function (body) {
      return window.Vue.http({
        method: 'POST',
        url: env.apis.external + 'password/reset',
        headers: getHeaders(true),
        body
      }).then(res => res.body)
    },
    getAuthHeaders () {
      let authHeaders = {}
      let orgId = this.getClaim('orgId')
      let token = getToken()
      if (orgId) {
        authHeaders['X-Cp-Org-Id'] = orgId
      }
      if (token) {
        authHeaders['Authorization'] = 'Bearer ' + token
      }
      return authHeaders
    },
    getClaims () {
      return claims || {}
    },
    getClaim (key) {
      return (claims || {})[key]
    },
    isLoggedIn () {
      return !!claims // && claims['exp'] > ((new Date()).getTime() / 1000)) // <--- this fails when homestead's vm's time gets out of sync with your host os
    },
    isImpersonating () {
      return !!(claims || {}).actualUserId
    },
    hasAnyRole (...roles) {
      if (!claims) {
        return false
      }
      if (Array.isArray(roles[0])) roles = roles[0]
      const userRole = (claims.role || '').toLowerCase()
      roles = roles.map(x => x.toLowerCase())
      for (let i = 0; i < roles.length; i++) {
        if (userRole === roles[i]) {
          return true
        }
      }
      return false
    },
    getAuthPid () {
      return claims.userPid
    },
    getAuthId () {
      return claims.sub
    },
    getOwnerId () {
      return this.hasAnyRole('Superadmin', 'Admin')
        ? ADMIN_OWNER_ID
        : this.getAuthId()
    },
    getOwnerPid () {
      return this.hasAnyRole('Superadmin', 'Admin')
        ? window.Vue.prototype.$getGlobal('company_pid').value
        : this.getAuthPid()
    },
    hasActiveSubscription () {
      return (claims || {}).activeSubscription || this.hasAnyRole('Superadmin', 'Admin')
    },
    hasAcceptedTerms () {
      return (claims || {}).acceptedTerms || this.hasAnyRole('Superadmin', 'Admin')
    },
    // DEPRECATED
    check (roles) {
      doc.deprecate('check').inFavorOf('hasAnyRole')
      return this.hasAnyRole(roles)
    },
    authId () {
      doc.deprecate('authId').inFavorOf('getAuthId')
      return this.getAuthId()
    },
    getAuthID () {
      doc.deprecate('getAuthID').inFavorOf('getAuthId')
      return new Promise((resolve, reject) => {
        return this.getAuthId()
      })
    },
    removeFromStorage (part) {
      const storageObject = window.localStorage
      for (let key in storageObject) {
        if (key.indexOf(part) > -1) {
          window.localStorage.removeItem(key)
        }
      }
    }
  }
})
