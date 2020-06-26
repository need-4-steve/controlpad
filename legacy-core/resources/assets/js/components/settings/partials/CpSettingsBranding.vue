<template lang="html">
    <div class="branding-wrapper">
        <div class="cp-accordion">
            <div class="cp-accordion-head black-header">
                <h5>Branding</h5>
            </div>
        <div class="cp-accordion-body">
            <div class="cp-accordion-body-wrapper">
                <div class="cp-left-col">
                    <div>
                        <h5>Backoffice Logo for Header</h5>
                        <div id="dropzoneImport">
                            <form action="/api/v1/media/process" method="POST" class="dropzone" id="dropzoneOne">
                                <input type="hidden" name="userId" :value="settings.id" :selected="settings">
                                <input type="hidden" name="replace" v-model="replace">
                                <input type="hidden" id="token" name="_token">
                                <input type="hidden" name="image_type" value="company_logo">
                            </form>
                        </div>
                    </div>
                    <div>
                        <h5>Backoffice Logo for Header Inverse</h5>
                        <div id="dropzoneImport">
                            <form action="/api/v1/media/process" method="POST" class="dropzone dropzone-inverse" id="dropzoneFour">
                                <input type="hidden" name="userId" :value="settings.id" :selected="settings">
                                <input type="hidden" name="replace" v-model="replace">
                                <input type="hidden" id="token" name="_token">
                                <input type="hidden" name="image_type" value="back_office_logo_inverse">
                            </form>
                        </div>
                    </div>
                </div>
                <div class="cp-right-col">
                    <div class="space-between">
                        <div class="loading-gif">
                            <h5>Loading Gif</h5>
                            <div id="dropzoneImport">
                                <form action="/api/v1/media/process" method="POST" class="dropzone" id="dropzoneTwo">
                                    <input type="hidden" name="userId" :value="settings.id" :selected="settings">
                                    <input type="hidden" name="replace" v-model="replace">
                                    <input type="hidden" id="token" name="_token">
                                    <input type="hidden" name="image_type" value="loading_icon">
                                </form>
                            </div>
                        </div>
                        <div class="favicon">
                            <h5>Favicon</h5>
                            <div id="dropzoneImport">
                                <form action="/api/v1/media/process" method="POST" class="dropzone" id="dropzoneThree">
                                    <input type="hidden" name="userId" :value="settings.id" :selected="settings">
                                    <input type="hidden" name="replace" v-model="replace">
                                    <input type="hidden" id="token" name="_token">
                                    <input type="hidden" name="image_type" value="favicon">
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="save-settings-button">
                        <input class="cp-button-standard" type="button" @click="saveSettings()" value="Save">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</template>

<script>
const Settings = require('../../../resources/settings.js')
const Dropzone = require('dropzone')
const Auth = require('auth')

module.exports = {
  data: function () {
    return {
      closed: true,
      settings: {
        background_text_1: {},
        background_text_2: {},
        background_text_3: {},
        company_name: {},
        company_email: {},
        title_rep: {},
        title_announcement: {},
        title_store: {},
        rep_welcome: {},
        google_store_url: {},
        ios_store_url: {},
        social_media_link: {},
        product_locator: {},
        address: {},
        phone: {},
        terms: {},
        return_policy: {},
        about_us: {},
        back_office_logo: {},
        use_built_in_store: {},
        loading_icon: {},
        favicon: {},
        back_office_logo_inverse: {},
        landing_page: {},
        hex_color:{}
      },
      replace: {},
      hostName: '',
      options: [
       { text: 'Home', value: '' },
       { text: 'Login', value: 'login' },
       { text: 'Store', value: 'store' }
      ]
    }
  },
  computed: {},
  mounted () {
    this.getBranding()
    Dropzone.autoDiscover = false
    this.buildDropzone()
    this.hostName = window.location.host
  },
  methods: {
    dropzoneConfig: function () {
      return {
        maxFileSize: 10,
        maxFiles: 1,
        acceptedFiles: '.jpg, .jpeg, .png, .gif',
        dictDefaultMessage: 'Click or drop your file here to upload',
        dictMaxFilesExceeded: 'You may only upload one file at a time.',
        addRemoveLinks: true,
        dictRemoveFile: 'Remove File',
        headers: Object.assign({}, {
          'X-CSRF-TOKEN': document.querySelector('#token').getAttribute('content')
        }, Auth.getAuthHeaders())
      }
    },
    buildDropzone: function () {
      var thisBranding = this
      var dropzoneConfig = this.dropzoneConfig
      var dropzoneOneConfig = new dropzoneConfig ()
      dropzoneOneConfig.success = function (file, response) {
        thisBranding.settings.back_office_logo.value = response.url_md
      }
      var dropzoneOne = new Dropzone('#dropzoneOne', dropzoneOneConfig)
      var dropzoneTwoConfig = new dropzoneConfig()
      dropzoneTwoConfig.success = function (file, response) {
        thisBranding.settings.loading_icon.value = response.url
      }
      var dropzoneTwo = new Dropzone('#dropzoneTwo', dropzoneTwoConfig)
      var dropzoneThreeConfig = new dropzoneConfig()
      dropzoneThreeConfig.success = function (file, response) {
        thisBranding.settings.favicon.value = response.url_xxs
      }
      var dropzoneThree = new Dropzone('#dropzoneThree', dropzoneThreeConfig)
      var dropzoneFourConfig = new dropzoneConfig()
      dropzoneFourConfig.success = function (file, response) {
        thisBranding.settings.back_office_logo_inverse.value = response.url_md
      }
      var dropzoneFour = new Dropzone('#dropzoneFour', dropzoneFourConfig)
    },
    getBranding () {
      Settings
        .getAllSettings()
        .then((response) => {
            this.settings = response
            this.getHeaderImage()
            this.getHeaderImageInverse()
            this.getLoadingGif()
            this.getFavicon()
        })
    },
    getHeaderImage () {
      var imageFile = {
        url: this.settings.back_office_logo.value,
        size: 12345
      }
      Dropzone.forElement('#dropzoneOne').emit('addedfile', imageFile)
      Dropzone.forElement('#dropzoneOne').emit('thumbnail', imageFile, imageFile.url)
      Dropzone.forElement('#dropzoneOne').emit('complete', imageFile)
    },
    getHeaderImageInverse () {
      var imageFile = {
        url: this.settings.back_office_logo_inverse.value,
        size: 12345
      }
      Dropzone.forElement('#dropzoneFour').emit('addedfile', imageFile)
      Dropzone.forElement('#dropzoneFour').emit('thumbnail', imageFile, imageFile.url)
      Dropzone.forElement('#dropzoneFour').emit('complete', imageFile)
    },
    getLoadingGif () {
      var imageFile = {
        url: this.settings.loading_icon.value,
        size: 12345
      }
      Dropzone.forElement('#dropzoneTwo').emit('addedfile', imageFile)
      Dropzone.forElement('#dropzoneTwo').emit('thumbnail', imageFile, imageFile.url)
      Dropzone.forElement('#dropzoneTwo').emit('complete', imageFile)
    },
    getFavicon () {
      var imageFile = {
        url: this.settings.favicon.value,
        size: 12345
      }
      Dropzone.forElement('#dropzoneThree').emit('addedfile', imageFile)
      Dropzone.forElement('#dropzoneThree').emit('thumbnail', imageFile, imageFile.url)
      Dropzone.forElement('#dropzoneThree').emit('complete', imageFile)
    },
    saveSettings () {
      Settings
        .update(this.settings)
        .then((response) => {
            this.$toast('Settings saved successfully.')
        })
    }
  },
  components: {}
}
</script>

<style lang="scss">
    @import "resources/assets/sass/var.scss";
    .branding-wrapper {
        padding-bottom: 20px;
        .cp-accordion-head {
            &.black-header {
                position: relative;
                padding: 10px;
                height: auto;
                h5 {
                    margin: 0;
                    display: inline-block;
                    margin-right: 0px;
                    font-weight: 300;
                    font-size: 1.2em;
                }
                background-color: $cp-main;
                color: $cp-main-inverse;
            }
        }
        .dropzone-wrapper {
            margin-bottom: 5px;
        }
        .cp-left-col {
            width: 48%;
        }
        .cp-right-col {
            width: 48%;
        }
        .line-wrapper {
            display: flex;
            -webkit-display: flex;
            justify-content: space-between;
            -webkit-justify-content: space-between;
            label {
                font-size: 14px;
                font-weight: 300;
                margin-bottom: 0;
            }
            .input-class {
                height: 100%;
                width: 50%;
                height: 30px;
                text-indent: 10px;
                margin: 5px 0;

            }
            &.toggle-switch {
                width: 40px;
            }
        }
        .dropzone {
            text-align: center;
            .dz-preview {
                .dz-image {
                    width: 100%;
                    img{
                      max-width: 100%;
                    }
                }
            }
        }
        .dropzone-inverse {
            img {
                background: $cp-main;
            }
        }
        .space-between {
            .loading-gif, .favicon {
                width: 49%;
                margin: 0;
            }
        }
    }
</style>
