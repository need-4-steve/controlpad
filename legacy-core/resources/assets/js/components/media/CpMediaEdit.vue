<template lang="html">
    <div class="media-edit-wrapper">
        <h4>{{ media.title }}</h4>
        <section class="media-file">
            <div v-if="media.type === 'Image'">
                <img :src="media.url_md || ''">
            </div>
            <div v-if="media.type === 'Video'">
                <div v-if="media.extension === 'swf'">
                    <embed :src="media.url" width="600" />
                </div>
                <div else>
                    <video width="600" controls>
                        <source :src="media.url" :type="'video/' + media.extension">
                        Your browser does not support HTML5 video. Please use or update to the latest version of Chrome or Firefox browsers for best results.
                    </video>
                </div>
            </div>
        </section>
        <form class="cp-form-standard media-details">
            <label>Title</label>
            <input type="text" v-model="media.title">
            <label>Description</label>
            <input type="text" v-model="media.description">
            <div class="media-checkbox" v-if="Auth.hasAnyRole('Superadmin', 'Admin')">
                <input type="checkbox" v-model="media.is_public"><label>Make available for {{$getGlobal('title_rep').value}}s</label>
            </div>
        </form>
    </div>
</template>

<script>
const Auth = require('auth')

module.exports = {
  data: function () {
    return {
      Auth: Auth
    }
  },
  props: {
    media: {
      type: Object,
      required: true
    }
  }
}
</script>

<style lang="scss">
.media-edit-wrapper {
    .media-details {
        input {
            margin-top: 5px;
            margin-bottom: 10px;
            border: 0;
        }

        .media-checkbox {
            overflow: hidden;
            width: 50%;
            input {
                margin: 5px;
                width: auto;
                display: inline-block;
            }
            label {
                margin: 5px;
                display: inline-block;
            }
        }
    }
    .media-file {
        margin-bottom: 15px;
        text-align: center;
    }
}
</style>
