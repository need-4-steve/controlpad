<template lang="html">
  <div class="branding-wrapper">
    <div class="cp-accordion">
      <div class="cp-accordion-head black-header">
        <h5>About Me</h5>
      </div>
      <div class="cp-accordion-body">
        <div class="cp-accordion-body-wrapper">
          <div class="cp-left-col">
            <div>
              <cp-image-upload
                title="Image"
                tooltip="Please select your desired image and upload it below. You can drag a file directly into the area or click to allow you to find the image on your device. For best results, we recommend an image size of at least 670 x 450 pixels."
                :current-image="aboutMe.image_url"
                api_src="/api/v1/media/process"
                file_type=".jpg, .jpeg, .png, .gif"
                drop-zone-id="about_me_image"
                @new-media="saveImage"></cp-image-upload>
            </div>
          </div>
          <div class="cp-right-col">
            <div class="line-wrapper">
              <label>Title</label>
              <input class="input-class" type="text" v-model="aboutMe.title">
            </div>
            <div class="line-wrapper">
              <label>Body<cp-tooltip :options="{ content: 'Tell your customers about yourself'}"></cp-tooltip></label>
            </div>
            <textarea class="textarea" rows="5" cols="40" v-model="aboutMe.body"></textarea>
            <div class="line-wrapper">
              <label>Facebook Link</label>
              <input class="input-class" type="url" v-model="aboutMe.facebook_url">
            </div>
            <div class="line-wrapper">
              <label>Instagram Link</label>
              <input class="input-class" type="url" v-model="aboutMe.instagram_url">
            </div>
          </div>
          <div class="save-settings-button">
            <input class="cp-button-standard" type="button" :disabled="loading" @click="save()" value="Save">
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
const Settings = require('../../resources/settings.js')

module.exports = {
  props: {
    storeSettingsProp: {
      type: Object,
      required: true
    }
  },
  data () {
    return {
      loading: false,
      aboutMe: null,
    }
  },
  created () {
    this.aboutMe = JSON.parse(this.storeSettingsProp.settings.about_me)
  },
  methods: {
    save () {
      this.loading = true
      var request = { key: 'about_me', value: JSON.stringify(this.aboutMe) }
      Settings.saveStoreSetting(request)
          .then((response) => {
            this.loading = false
            if (response.error) {
              return this.$toast(response.message, {error: true})
            }
            this.aboutMe = JSON.parse(response.value)
          })
    },
    saveImage (value, key) {
      this.aboutMe.image_url = value
    },
  },
}
</script>
