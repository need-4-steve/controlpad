<template lang="html">
    <div class="announcement-form-wrapper">
        <div class="cp-form-standard">
            <div>
                <label>Title</label>
                <input
                :class="{ error: errorMessages.title }"
                type="text"
                v-model="announcement.title">
                <span v-show="errorMessages.title" class="cp-warning-message">{{ errorMessages.title }}</span>
            </div>
            <div class="editor-wrapper" :class="{ error: errorMessages.body }">
                <label for="">Body</label>
                <cp-editor v-model="announcement.body"></cp-editor>
                <span v-show="errorMessages.body" class="cp-warning-message">{{ errorMessages.body }}</span>
            </div>
            <br />
            <label>Description</label>
            <small>(optional)</small>
            <textarea
            :class="{ error: errorMessages.description }"
            class="announcement-field smaller-field"
            v-model="announcement.description"></textarea>
            <span v-show="errorMessages.description" class="cp-warning-message">{{ errorMessages.description }}</span>
        </div>
        <div class="submit-button">
            <button class="cp-button-standard" @click="$emit('close')">Cancel</button>
            <button class="cp-button-standard" @click="createOrUpdate()">Submit</button>
        </div>
    </div>
</template>

<script>
const Announcements = require('../../resources/announcements.js')
const Auth = require('auth')

module.exports = {
  data () {
    return {
      errorMessages: {}
    }
  },
  props: {
    announcement: {
      type: Object,
      default: function () {
        return {
          title: '',
          body: '',
          description: '' }
      }
    },
    edit: {
      type: Boolean,
      default: false
    },
    modalShow: {
      default: false
    },
    callBack: {
      type: Function,
      required: false
    }
  },
  moundted: function () {
    Auth.hasAnyRole('Superadmin', 'Admin') ? this.indexRequest.per_page = '100' : this.indexRequest.per_page = '15'
  },
  methods: {
    createOrUpdate: function () {
      if (this.edit) {
        this.updateAnnouncement()
      } else {
        this.createAnnouncement()
      }
    },
    updateAnnouncement: function () {
      Announcements.update(this.announcement.id, this.announcement)
        .then((response) => {
          if (response.error) {
            this.errorMessages = response.message
            return
          }
          this.$toast('Successfully updated.', { dismiss: false })
          this.$emit('close')
        })
    },
    createAnnouncement: function () {
      Announcements.create(this.announcement)
        .then((response) => {
          if (response.error) {
            this.errorMessages = response.message
            return
          }
          this.callBack()
          this.$toast('Successfully created.', { dismiss: false })
          this.$emit('close')
        })
    }
  },
  components: {
    CpEditor: require('../../cp-components-common/inputs/CpEditor.vue')
  }
}
</script>

<style lang="scss">
@import "resources/assets/sass/var.scss";

.announcement-form-wrapper {
    .panel-heading {
        background: #f2f2f2 !important;
    }
    .note-palette-title {
        text-align: center;
        padding: 5px;
        color: black;
    }
    .note-color-reset {
        text-align: center;
        color: black;
    }
    .announcement-field {
        background: $cp-lighterGrey;
        border: none;
        width: 100%;
        height: 200px;
        &.smaller-field {
            height: 100px;
        }
    }
    .submit-button {
        float: right;
        margin-top: 15px;
    }
}

</style>
