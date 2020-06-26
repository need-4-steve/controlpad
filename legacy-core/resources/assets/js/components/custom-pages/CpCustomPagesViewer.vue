<template>
  <div class="terms-scoped">
    <section>
        <h2>{{page.title}}</h2>
      <div class="cp-panel-standard" v-html="page.content">
      </div>
    </section>
  </div>
</template>

<script id="CpCustomPagesViewer">
const Settings = require('../../resources/settings.js')

module.exports = {
  routing: [
    { name: 'public.terms-conditions/company', path: '/terms-conditions/company', meta: { title: 'Terms', noauth: true }, props: true },
    { name: 'public.terms-conditions/rep', path: '/terms-conditions/rep', meta: { title: 'Terms', noauth: true }, props: true },
    { name: 'public.return-policy', path: '/return-policy', meta: { title: 'Terms', noauth: true }, props: true }
  ],
  data () {
    return {
      page: {} }
  },
  props: {
    pagePath: {
      default () {
        return this.$pathParameterName()
      }
    }
  },
  mounted () {
    if (this.pagePath === 'rep') {
      this.getRepTerms()
    } else if (this.pagePath === 'company') {
      this.getCompanyTerms()
    } else {
      this.getReturnPolicy()
    }
  },
  methods: {
    getReturnPolicy () {
      Settings.getReturnPolicy().then(response => {
        this.page = response
      })
    },
    getCompanyTerms () {
      Settings.getCompanyTerms().then(response => { this.page = response })
    },
    getRepTerms () {
      Settings.getRepTerms().then(response => { this.page = response })
    }
  }
}
</script>

<style lang="scss">
.terms-scoped {
  display: flex;
  justify-content: center;
  section {
    max-width: 675px;
  }
}
</style>
