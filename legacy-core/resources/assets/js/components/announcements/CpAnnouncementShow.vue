<template lang="html">
<div class="announcement-form-wrapper">
  <div>
    <h1>{{ announcement.title }}</h1>
    <p>{{ announcement.created_at | cpStandardDate }}</p>
    <p v-if="announcement.description"><em>{{ announcement.description }}</em></p>
    <p v-html="announcement.body"></p>
  </div>
  <div class="submit-button">
      <button class="cp-button-standard" @click="$emit('close')">Close</button>
  </div>
</div>
</template>

<script>

module.exports = {
  props: {
    announcement: {
      type: Object,
      default: function () {
        return {
          title: '',
          body: '',
          description: '',
          created_at: ''
        }
      }
    },
    modalShow: {
      default: false
    }
  }
}
</script>

<style lang="scss">
@import "resources/assets/sass/var.scss";

.announcement-form-wrapper {
}
.submit-button {
    float: right;
    margin-top: 15px;
}

</style>
