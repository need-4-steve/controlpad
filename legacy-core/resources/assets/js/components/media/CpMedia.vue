<template lang="html">
    <div class="media-show-wrapper">
        <h3>{{ media.title }}</h3>
        <section class="media-display">
            <div v-if="media.type === 'Image'">
                <img :src="media.url_sm || ''">
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
        <table class="cp-table-standard media-table">
            <tr>
                <th>Title:</th>
                <td>{{ media.title }}</td>
            </tr>
            <tr>
                <th>Description:</th>
                <td>{{ media.description }}</td>
            </tr>
            <tr>
                <th>Type:</th>
                <td>{{ media.type }}</td>
            </tr>
            <tr>
                <th>Owner:</th>
                <td><a :href="'/users/' + media.user_id">{{ media.owner }}</a></td>
            </tr>
        </table>
    </div>
</template>
<script>
module.exports = {
    data: function () {
        return {}
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
.media-show-wrapper {
    .media-display {
        text-align: center;
    }
    .url-display {
        word-break:break-all;
    }
    .media-table {
        th {
            border-radius: 0px;
            max-width: 35px;
        }
    }
}
</style>
