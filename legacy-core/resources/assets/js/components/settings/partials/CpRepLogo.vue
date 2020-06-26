<template lang="html">
  <div class="logo-wrapper">
    <div class="cp-box-standard">
      <div class="cp-accordion">
        <div class="cp-accordion-body">
          <div class="cp-accordion-body-wrapper">
            <div class="cp-box-heading">
              <h5>Logo</h5>
            </div>
            <div id="dropzoneImport">
              <form action="/api/v1/media/process" method="POST" class="dropzone" id="dropzoneLogo">
                <input type="hidden" id="token" name="_token">
                <input type="hidden" name="image_type" value="logo">
              </form>
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
      companyInfo: false,
      replace: {}
    }
  },
  computed: {},
  mounted () {
    this.getSettings()
    Dropzone.autoDiscover = false
    this.buildDropzone()
    this.hostName = window.location.host
  },
  props: {},
  methods: {
    dropzoneConfig: function () {
      return {
        maxFileSize: 10,
        maxFiles: 1,
        acceptedFiles: '.jpg, .jpeg, .png, .gif',
        dictDefaultMessage: 'Click or drop your logo file here to upload',
        dictMaxFilesExceeded: 'You may only upload one file at a time.',
        addRemoveLinks: true,
        dictRemoveFile: 'Remove File',
        headers: Object.assign({}, {
          'X-CSRF-TOKEN': document.querySelector('#token').getAttribute('content')
        }, Auth.getAuthHeaders())
      }
    },
    buildDropzone: function () {
      var thisLogo = this
      var dropzoneConfig = this.dropzoneConfig
      var dropzoneLogoConfig = new dropzoneConfig ()
      dropzoneLogoConfig.success = function (file, response) {
        thisLogo.logo = response.url_md
        thisLogo.saveLogo(response.url_md);
        this.removeFile(file)
        thisLogo.getLogoImage()
      }
      var dropzoneLogo = new Dropzone('#dropzoneLogo', dropzoneLogoConfig)
    },
    getSettings: function () {
      Settings.getStoreSettings()
        .then((response) => {
          this.logo = response.settings.logo
          this.getLogoImage()
        })
    },
    getLogoImage: function () {
      if (this.logo != "") {
        var imageFile = {
          url: this.logo,
        }
        Dropzone.forElement('#dropzoneLogo').emit('addedfile', imageFile)
        Dropzone.forElement('#dropzoneLogo').emit('thumbnail', imageFile, imageFile.url)
        Dropzone.forElement('#dropzoneLogo').emit('complete', imageFile)
      }
    },
    saveLogo: function (url) {
      Settings.saveStoreSetting({key: 'logo', value: url})
        .then((response) => {
          this.$toast('Logo saved successfully.')
        })
    }
  },
  components: {}
}
</script>

<style lang="sass">
    @import "resources/assets/sass/var.scss";
    .logo-wrapper {
        padding-bottom: 20px;
        .dropzone-wrapper {
            margin-bottom: 5px;
        }
        .dropzone {
            text-align: center;
            .dz-preview {
                .dz-image {
                    width: 100%;
                }
            }
        }
    }
</style>
