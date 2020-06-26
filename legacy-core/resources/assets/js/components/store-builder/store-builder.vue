<template lang="html">
  <div class="store-builder-wrapper">
    <div class="builder-content" v-if="store.settings">
      <section class="builder-selected-page">
        <a v-if="Auth.hasAnyRole('Rep')" target="_blank" :href="`${repUrl}/store`" no-vue-route class="cp-button-link">View Your Page</a>
        <a v-else href="/store" no-vue-route class="cp-button-link">View Your Page</a>
      </section>
      <section class="store-builder-pages" v-if="!loading">
        <cp-storefront-builder :store="store" :bannerimages="bannerimages"></cp-storefront-builder>
      </section>
      <section v-if="Auth.hasAnyRole('Rep') && $getGlobal('about_rep').show" class="settings-wrapper">
        <cp-about-me-builder :storeSettingsProp="store"></cp-about-me-builder>
      </section>
    </div>
    <div class="align-center">
      <img class="loading" :src="$getGlobal('loading_icon').value" v-show="loading">
    </div>
  </div>
</template>

<script>
const Settings = require('../../resources/settings.js')
const Auth = require('auth')

module.exports = {
  data: function () {
    return {
      settings: {},
      store: {},
      bannerimages:[],
      app_url: window.location.host,
      Auth: Auth,
      loading: false,
      repUrl: ''
    }
  },
  mounted: function () {
    this.getStoreSettings()
  },
  methods: {
    getStoreSettings: function () {
      this.loading = true
      Settings.getStoreSettings()
        .then((response) => {
          this.loading = false
          if (response.error) {
            this.$toast(response.message, {error: true})
            return
          }
          this.store = response
          this.settings = response.settings
          if (this.store.settings.show_store_banner === '0') {
            this.store.settings.show_store_banner = false
          } else {
            this.store.settings.show_store_banner = true
          }
          if (this.store.settings.show_banner_image_1 === '0') {
            this.store.settings.show_banner_image_1 = false
          } else {
            this.bannerimages.push({value:this.store.settings.show_banner_image_1,possition: 1});
            this.store.settings.show_banner_image_1 = true
          }
          if (this.store.settings.show_banner_image_2 === '0') {
            this.store.settings.show_banner_image_2 = false
          } else {
            this.bannerimages.push({value:this.store.settings.show_banner_image_2,possition: 2});
            this.store.settings.show_banner_image_2 = true
          }
          if (this.store.settings.show_banner_image_3 === '0') {
            this.store.settings.show_banner_image_3 = false
          } else {
            this.bannerimages.push({value:this.store.settings.show_banner_image_3,possition: 3});
            this.store.settings.show_banner_image_3 = true
          }
          this.repUrl = 'http://' + response.rep.public_id + '.' + this.app_url
          this.repUrl = this.$getGlobal('rep_url').value.replace('%s', response.rep.public_id)
        })
    }
  },
  components: {
    'CpStorefrontBuilder': require('./CpStorefrontBuilder.vue'),
    'CpAboutMeBuilder': require('./CpAboutMeBuilder.vue')
  }
}
</script>

<style lang="scss">
@import "resources/assets/sass/var.scss";

.store-builder-wrapper {
    .store-builder-header {
        padding: 15px;
        background-color: $cp-lighterGrey;
        overflow: hidden;
        .builder-selecter {
          float: right;
          select {
            background-color: white;
          }
        }
    }
    .store-builder-pages {
        margin-top: 20px;
    }
    .builder-selected-page {
      overflow: hidden;
      margin-bottom: 15px;
        h1 {
          float: left;
          width: 50%;
        }
        a {
            float: right;
        }
    }
}

.store-input-image-text {
    background: transparent;
    border: 1.5px dashed $cp-lightGrey;
    text-align: center;
    width: 100%;
    text-shadow: 1px 1px 3px black;
}
.story-input-header {
    @extend .store-input-image-text;
    text-shadow: none;
    width: inherit;
    text-align: left;
}
.cp-modal-body {
    &.upload {
        padding: 0;
    }
}
</style>
