<template>
  <div layout="wrapper" :class="{'nav-open': navOpen}">
    <cp-nav-menu ref="nav"></cp-nav-menu>
    <div layout="content">
      <div></div>
      <header>
        <section>
          <span class="menu-trigger mdi-menu" ref="menuBtn" @click="navOpen = !navOpen"></span>
          <h2>{{ $route.meta.title }}</h2>
          <span class="logo"><img :src="$getGlobal('back_office_logo').value" /></span>
          <span class="spacer"></span>
          <menu ref="dropdownMenu">
            <span
              :class="{ 'active': dropDown }"
              class="mdi-account"
              @click="dropDown = !dropDown">
            </span>
            <div :class="{ 'active': dropDown }" @click="dropDown = false">
              <span>{{ repName }}</span>
              <a href="/dashboard" class="mdi-speedometer" v-if="dashboardVisible">Dashboard</a>
              <a href="/my-settings" class="mdi-settings">My Account</a>
              <a v-if="storeOwner && repUrl && this.$getGlobal('replicated_site').show" target="_blank" :href="repUrl" class="mdi-web">My Website</a>
              <a href="/auth?logout" class="mdi-logout">Sign Out</a>
            </div>
          </menu>
        </section>
      </header>
      <main>
        <div>
          <h2>{{ $route.meta.title }}</h2>
          <router-view :key="$route.fullPath"></router-view>
        </div>
        <footer>
          <div v-if="$getGlobal('about_us').show || $getGlobal('return_policy').show || $getGlobal('terms').show">
            <a v-if="$getGlobal('about_us').show" :href="$getGlobal('about_us').value" target="_blank">About Us</a>
            <a v-if="$getGlobal('return_policy').show" :href="$getGlobal('return_policy').value" target="_blank">Return Policy</a>
            <a v-if="$getGlobal('terms').show" href="/terms-conditions/company">Terms &amp; Conditions</a>
          </div>
          <div>&copy; {{ copyrightYear }} - {{ companyName }} </div>
          <div v-if="$getGlobal('address').show"> {{ companyAddress }} </div>
          <div v-if="$getGlobal('phone').show"> {{ companyPhone }} </div>
        </footer>
      </main>
    </div>
  </div>
</template>
<script id="CpLayout">
const Auth = require('auth')
const moment = require('moment')

module.exports = {
  routing: {
    name: 'site',
    path: '/'
  },
  data () {
    return {
      navOpen: false,
      storeOwner: !!Auth.getClaims().repSubdomain && Auth.hasAnyRole('Rep'),
      sellerType: Auth.getClaims().sellerType,
      copyrightYear: moment().format('YYYY'),
      repUrl: this.$getGlobal('rep_url').value.replace('%s', Auth.getClaims().repSubdomain),
      companyName: this.$getGlobal('company_name').value,
      firstName: Auth.getClaims().name,
      repName: Auth.getClaims().fullName,
      companyAddress: this.$getGlobal('address').value,
      companyPhone: this.$getGlobal('phone').value,
      dropDown: false
    }
  },
  mounted () {
    let code, parser, doc
    if (this.hasOlark()) {
      // Parsing script tag from settings
      code = '<html><body>' + this.$getGlobal('olark_chat_integration').value + '</body></html>'
      parser = new window.DOMParser()
      doc = parser.parseFromString(code, 'text/html')
      for (let i = 0; i < doc.body.childElementCount; i++) {
        const child = document.createElement('script')
        child.type = 'text/javascript'
        child.async = true
        child.innerText = doc.body.children[i].innerText
        document.body.appendChild(child)
      }
    } else if (this.hasTawk()) {
      // parsing script tag from settings
      code = '<html><body>' + this.$getGlobal('tawk_chat_integration').value + '</body></html>'
      parser = new window.DOMParser()
      doc = parser.parseFromString(code, 'text/html')
      for (let i = 0; i < doc.body.childElementCount; i++) {
        const child = document.createElement('script')
        child.type = 'text/javascript'
        child.async = true
        child.innerText = doc.body.children[i].innerText
        document.body.appendChild(child)
      }
    }
    document.addEventListener('click', this.documentClick)
    this.$events.$on('login-as-change', (e) => {
      this.repName = Auth.getClaims().fullName
    })

    document.addEventListener('click', (e) => {
      if (!this.$refs.nav) return
      const el = this.$refs.nav.$el
      const target = e.target
      const menuBtn = this.$refs.menuBtn
      if ((el !== target && !el.contains(target) && target !== menuBtn) ||
            (el.contains(target) && target.href)) {
        this.navOpen = false
      }
    })
    this.$events.$on('login-as-change', (e) => {
      this.storeOwner = !!Auth.getClaims().repSubdomain
    })
    this.$events.$on('set-token', (e) => {
      this.repUrl = this.$getGlobal('rep_url').value.replace('%s', Auth.getClaims().repSubdomain)
    })

    $("nav.main-menu-nav-scope").attr("style", "background-color: "+$('#hexvalue').text().replace(/\s/g, '')+" !important;");

  },
  methods: {
    documentClick (e) {
      let el = this.$refs.dropdownMenu
      let target = e.target
      if (el !== target && !el.contains(target)) {
        this.dropDown = false
      }
    },
    isAdmin () {
      Auth.hasAnyRole('Superadmin', 'Admin')
    },
    isRep (sellertype) {
      if (!Auth.hasAnyRole('Superadmin', 'Admin')) {
        return true
      }
    },
    hasOlark () {
      if (this.$getGlobal('olark_chat_integration').show && this.isRep()) {
        return true
      }
    },
    hasTawk () {
      if (this.$getGlobal('tawk_chat_integration').show && this.isRep()) {
        return true
      }
    }
  },
  destroyed () {
    document.removeEventListener('click', this.documentClick)
  },
  computed: {
    dashboardVisible() {
      if (Auth.hasAnyRole('Superadmin', 'Admin')) {
        return true
      } else if (Auth.hasAnyRole('Rep')) {
        // Workaround to make sure affiliates can make sales, this is a hack for myzoom live
        return (this.sellerType === 'Reseller' || this.$getGlobal('affiliate_custom_order').show)
      } else {
        return false
      }
    }
  }
}
</script>
<style lang="scss">
  html,body,#vue-app{
    height:100%;
    padding:0;
    margin:0;
    overflow:hidden;
    -webkit-overflow-scrolling: touch;
  }
  [layout="wrapper"]{
    display:flex;
    flex-direction:row;
    height:100%;
    &.nav-open {
      nav.main-menu-nav-scope{
        left: 0;
      }
      [layout="content"]{
        & > div:first-child{
          z-index:999;
          opacity:.7;
          background-color:#fff;
          position: absolute;
          top:0;left:0;bottom:0;right:0;
          @media(min-width: 1024px){
            display: none;
          }
        }
      }
    }
    [layout="content"]{
      flex:1;
      display:flex;
      flex-direction:column;
      margin-left: 0;
      max-width: 100%;
      transition: margin-left .1s ease-in-out, max-width .1s ease-in-out;
      @media(min-width: 1024px){
        margin-left: 250px;
        max-width: calc(100% - 250px);
      }

      header, footer {
        background:$cp-lighterGrey;
        border: 0 solid $cp-lightGrey;
      }
      header {
        box-shadow: none;
        border-bottom-width: 1px;
        height: 60px;
        width: 100%;
        margin-bottom: 15px;
        section {
          display: flex;
          align-items: center;
          .menu-trigger {
            cursor:pointer;
            padding: 10px 15px;
            @media(min-width: 1024px){
              display:none;
            }
          }
          h2 {
            flex: 1;
            margin: 0;
            padding: 0 15px;
            font-size: 24px;
            display: none;
            @media(min-width: 1024px){
              display: inline-block;
            }
          }
          .logo{
            flex: 1;
            display: flex;
            justify-content: center;
            @media(min-width: 1024px){
              display: none;
            }
            img{
              max-height: 35px;
              max-width: 250px;
            }
          }
          [flex]{ flex:1; }
          menu {
            margin: 0;
            padding: 0;
            height: 100%;
            position: relative;
            display: inline-block;
            cursor:pointer;
            & > span {
              display: block;
              height: 40px;
              line-height: 40px;
              padding: 10px 15px;
              text-align: center;
              &:hover {
                background-color: $cp-main;
                color: $cp-main-inverse;
              }
              &.active {
                background-color: $cp-main;
                color: $cp-main-inverse;
              }
            }
            div {
              display: none;
              position: absolute;
              right: 0;
              background-color: #f1f1f1;
              // width: 175px;
              box-shadow: 0px 1px 5px gray;
              z-index: 1;
              span,a{
                display: block;
                padding: 16px 36px;
                white-space:nowrap;
                color: black;
              }
              span{
                box-sizing: border-box;
                width: 100%;
                background: #fff;
                line-height: 50px;
                text-align: center;
              }
              a {
                text-decoration: none;
              }
              a:hover {
                background-color: $cp-main;
                color: $cp-main-inverse;
              }
              &.active {
                display: block;
              }
            }
          }
        }

      }
      main{
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        position: relative;
        & > div{
          flex: 1;
          padding: 10px 15px;
          margin: 0 auto;
          width: 100%;
          max-width: $maxPageWidth;
          box-sizing: border-box;
          & > h2{
            margin-top: 0;
            text-align: center;
            display: block;
            @media(min-width: 1024px){
              display: none;
            }
          }
        }
      }
      footer{
        margin-top: 15px;
        text-align:center;
        border-top-width: 1px;
        padding: 15px;
        position:relative;
        left:0;right:0;
        div{
          padding: 3px;
          a{
            display:inline-block;
            padding: 0 6px 0 0;
            &::after{
              content: ' ●';
              padding-left: 6px;
            }
            &:last-child::after{
              display:none;
            }
          }
        }
      }
    }
  }
</style>
