<template lang="html">
  <div class="media-index-wrapper">
        <div class="space-between">
            <div>
                <button v-if="Auth.hasAnyRole('Superadmin', 'Admin')" @click="uploadMediaModal = true, selectedFile = {}" :disabled="count.fileSize > 250" class="cp-button-link">Upload File</button>
                  <button v-else @click="uploadMediaModal = true, selectedFile = {}" :disabled="count.fileSize > 25" class="cp-button-link">Upload File</button>
                <p v-if="Auth.hasAnyRole('Superadmin', 'Admin')">You are using {{count.fileSize}} MB out of 250 MB</p>
                <p v-else>You are using {{count.fileSize}} MB out of 25 MB</p>
            </div>
        </div>
        <div class="cp-tabs-standard">
            <button @click="resetIndexRequest('All')" v-if="count.all > 0">All </button>
            <button @click="resetIndexRequest('Image')" v-if="count.image > 0">Images </button>
            <button @click="resetIndexRequest('Video')"v-if="count.video > 0">Video </button>
            <button @click="resetIndexRequest('Document')" v-if="count.document > 0">Documents </button>
            <button @click="resetIndexRequest('Spreadsheet')" v-if="count.sheet > 0">Spreadsheet </button>
            <button @click="resetIndexRequest('PDF')" v-if="count.pdf > 0">PDF </button>
            <button @click="resetIndexRequest('Presentation')" v-if="count.presentation > 0">Presentation </button>
        </div>
        <cp-table-controls
          :date-picker="false"
          :index-request="mediaRequest"
          :resource-info="pagination"
          :get-records="getMedia">
        </cp-table-controls>
        <div class="products" v-show="media.length > 0">
            <ul class="tiles product-grid">
                <li class="no-bullets item"  v-for="file in media" v-if="file.type === 'Image' && mediaRequest.status === 'Image'">
                    <div class="product-img-wrapper">
                        <img :src="file.url_sm || ''" :title="file.title">
                        <div class="mobile-buttons">
                        <i class="mdi mdi-eye" @click="mediaShow(file)"></i>
                        <i class="mdi mdi-pencil" v-if="Auth.hasAnyRole('Superadmin', 'Admin') || media.is_public !== 0" @click="mediaEdit(file)"></i>
                        <a class="mdi mdi-download" :href="file.url" :download="file.url"></a>
                        <i class="mdi mdi-delete" @click="showConfirm = true; pendingDelete[0] = file.id"></i>
                      </div>
                    </div>
                        <div class="options tile-overlay" @click="$event.stopPropagation();">
                                <a @click="mediaShow(file)" class="media">View File</a>
                                <a v-if="Auth.hasAnyRole('Superadmin', 'Admin') || media.is_public !== 0" @click="mediaEdit(file)" class="media" >Edit</a>
                                <a :href="file.url" :download="file.url" class="media">Download</a>
                                <a class="media" @click="showConfirm = true; pendingDelete[0] = file.id">Delete</a>
                        </div>
                </li>
            </ul>
            <table class="cp-table-standard desktop">
              <thead v-if="mediaRequest.status !== 'Image'">
                <th><!-- icon --></th>
                <th>Title</th>
                <th>Description</th>
                <th>Edit</th>
                <th>Download</th>
                <th>{{$getGlobal('title_rep').value}}s Viewable </th>
                <th>Delete</th>
              </thead>
              <tbody>
                <tr v-for="file in media" v-if="mediaRequest.status !== 'Image'">
                    <td v-if="file.type === 'Document'" class="mdi mdi-file-word"></td>
                    <td v-if="file.type === 'Spreadsheet'" class="mdi mdi-file-excel"></td>
                    <td v-if="file.type === 'PDF'" class="mdi mdi-file-pdf"></td>
                    <td v-if="file.type === 'Presentation'" class="mdi mdi-file-powerpoint"></td>
                    <td v-if="file.type === 'Video'"><a @click="mediaShow(file)" class="media, mdi mdi-file-video"></a></td>
                    <td v-if="file.type === 'Image'"><a @click="mediaShow(file)" class="media mdi mdi-file-image"><img :src="file.url_sm" alt=""></a></td>
                    <td>{{ file.title }}</td>
                    <td>{{ file.description }}</td>
                    <td><a v-if="Auth.hasAnyRole('Superadmin', 'Admin') || media.is_public !== 0" @click="mediaEdit(file)" class="media, mdi mdi-pencil" ></a></td>
                    <td> <a :href="file.url" :download="file.url" class="media, mdi mdi-download"></a></td>
                    <td v-if="file.is_public"><i class="mdi mdi-check"></i></td>
                    <td v-else></td>
                    <td><a class="media mdi mdi-delete" @click="showConfirm = true; pendingDelete[0] = file.id"></a></td>
                </tr>
              </tbody>
            </table>
            <section v-if="mediaRequest.status !== 'Image'" class="cp-table-mobile">
              <div v-for="file in media">
              <div><span></span><span>
                <div v-if="file.type === 'Document'" class="mdi mdi-file-word"></div>
                <div v-if="file.type === 'Spreadsheet'" class="mdi mdi-file-excel"></div>
                <div v-if="file.type === 'PDF'" class="mdi mdi-file-pdf"></div>
                <div v-if="file.type === 'Presentation'" class="mdi mdi-file-powerpoint"></div>
                <div v-if="file.type === 'Video'"><a @click="mediaShow(file)" class="media, mdi mdi-file-video"></a></div>
                <div v-if="file.type === 'Image'"><a @click="mediaShow(file)" class="media, mdi mdi-file-image"></a></div></span></div>
              <div><span>Title: </span><span>{{ file.title }}</span></div>
              <div><span>Description: </span><span>{{ file.description }}</span></div>
              <div v-if="Auth.hasAnyRole('Superadmin', 'Admin') || media.is_public !== 0"><span>Edit: </span><span><a @click="mediaEdit(file)" class="media, mdi mdi-pencil" ></a></span></div>
              <div><span>Download: </span><span><a :href="file.url" :download="file.url" class="media, mdi mdi-download"></a></span></div>
              <div  v-if="file.is_public"><span>{{$getGlobal('title_rep').value}}s Viewable: </span><span><div class="mdi mdi-check"></div></span></div>
              <div v-else><span>{{$getGlobal('title_rep').value}}s Viewable: </span><span></span></div>
              <div><span>Delete: </span><span><a class="media, mdi mdi-delete" @click="showConfirm = true; pendingDelete[0] = file.id"></a></span></div>
            </div>
            </section>
        </div>
        <div class="products" v-show="media.length <= 0">
          <p>No Media found</p>
        </div>
        <transition name="modal">
        <section class="cp-modal-standard" v-if="showMediaModal" @click="showMediaModal = false, selectedFile = {}">
            <div class="cp-modal-body" @click="$event.stopPropagation();">
                <cp-media :media="selectedFile" ></cp-media>
                <button class="cp-button-standard close-button" @click="exitMediaModal()">Close</button>
            </div>
        </section>
      </transition>
      <transition name="modal">
        <section class="cp-modal-standard" v-if="editMediaModal" @click="editMediaModal = false, selectedFile = {}">
            <div class="cp-modal-body" @click="$event.stopPropagation();">
                <cp-media-edit :media="selectedFile" v-model="selectedFile"></cp-media-edit>
                <button class="cp-button-standard close-button" @click="updateMedia()" v-if="!updating">Update</button>
                <button class="cp-button-standard close-button" v-else>Updating . . .</button>
                <button class="cp-button-standard close-button" @click="exitMediaModal()">Cancel</button>
            </div>
        </section>
      </transition>
        <transition name="modal">
        <section class="cp-modal-standard" v-if="uploadMediaModal" @click="uploadMediaModal = false, selectedFile = {}">
            <div class="cp-modal-body" @click="$event.stopPropagation();">
                <cp-media-create :modal-display="uploadMediaModal" :get-media="getMedia" v-model="uploadMediaModal"></cp-media-create>
                <button class="cp-button-standard close-button" @click="exitMediaModal()">Cancel</button>
            </div>
        </section>
      </transition>
        <div class="align-center">
            <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
            <cp-pagination :pagination="pagination" :callback="getMedia" :offset="2"></cp-pagination>
        </div>
        <cp-confirm
        :show="showConfirm"
        :message="'Are you sure you want to delete this media?'"
        v-model="showConfirm"
        :callback="deleteMedia"
        :params="pendingDelete"></cp-confirm>
    </div>
</template>

<script>
const Media = require('../../resources/media.js')
const Auth = require('auth')

module.exports = {
  data: function () {
    return {
      pendingDelete: [],
      showConfirm: false,
      media: [],
      showMediaModal: false,
      editMediaModal: false,
      uploadMediaModal: false,
      updating: false,
      uploading: false,
      selectedFile: {},
      pagination: {},
      loading: true,
      count: {
        'all': 0,
        'image': 0,
        'document': 0,
        'sheet': 0,
        'presentation': 0,
        'pdf': 0,
        'video': 0,
        'fileSize': 0
      },
      mediaRequest: {
        status: 'All',
        search_term: '',
        per_page: 15
      },
      Auth: Auth
    }
  },
  mounted: function () {
    Auth.hasAnyRole('Superadmin', 'Admin') ? this.mediaRequest.per_page = '100' : this.mediaRequest.per_page = '15'
    this.getTypeCount()
    this.getMedia()
  },
  methods: {
    deleteMedia: function (media) {
      Media.delete(media)
        .then((response) => {
          this.loading = false
          if (response.error) {
            return this.$toast(response.message, {error: true})
          }
          this.$toast('Media successfully deleted!', {error: false})
          this.getTypeCount()
          this.getMedia()
        })
    },
    getMedia: function () {
      this.loading = true
      this.mediaRequest.page = this.pagination.current_page
      Media.index(this.mediaRequest)
        .then((response) => {
          this.loading = false
          if (response.error) {
            return this.$toast(response.message, {error: true})
          }
          this.media = response.data
          response.per_page = parseInt(response.per_page)
          this.pagination = response
        })
    },
    updateMedia: function () {
      this.updating = true
      Media.update(this.selectedFile.id, this.selectedFile)
        .then((response) => {
          if (response.error) {
            this.editMediaModal = false
            this.updating = false
            return this.$toast(response.message, {error: true})
          }
          this.editMediaModal = false
          this.updating = false
          this.getMedia()
          return this.$toast('The file was successfully updated.')
        })
    },
    mediaShow: function (file) {
      this.selectedFile = file
      this.showMediaModal = true
      window.scrollTo(0, 0)
    },
    mediaEdit: function (file) {
      this.selectedFile = JSON.parse(JSON.stringify(file)) // needs to be it's own instance
      this.editMediaModal = true
      window.scrollTo(0, 0)
    },
    exitMediaModal: function () {
      this.showMediaModal = false
      this.editMediaModal = false
      this.uploadMediaModal = false
      this.selectedFile = {}
    },
    resetIndexRequest: function (status) {
      this.mediaRequest.status = status
      this.getMedia()
    },
    getTypeCount: function () {
      Media.mediaTypeCount(this.mediaRequest)
        .then((response) => {
          if (response.error) {
            return this.$toast('errorMessage', response.message)
          }
          this.count = response
        })
    }
  },
  events: {
    'childMessage': function (child) {
      if (child.error === true) {
        this.$toast(child.message, {error: true})
      } else {
        this.$toast(child.message, {error: false})
        this.getTypeCount()
        this.getMedia()
      }
    }
  },
  components: {
    CpMedia: require('./CpMedia.vue'),
    CpMediaEdit: require('./CpMediaEdit.vue'),
    CpMediaCreate: require('./CpMediaCreate.vue'),
    CpTableControls: require('../../cp-components-common/tables/CpTableControls.vue'),
    CpConfirm: require('../../cp-components-common/CpConfirm.vue')
  }
}
</script>

<style lang="scss">
.media-index-wrapper {
    .close-button {
        float: right;
    }
    button {
        &.cp-button-standard {
            margin-left: 5px;
        }
    }
    .media-thumbnail img {
          width: 50px;
    }
.product-img-wrapper{}
@media (max-width: 767px) {
    .product-grid .item .tile-overlay{
    display: none;
    }
.product-img-wrapper{
  height: 176px !important;
  width: 136px;
}a:link {
    color: none;
}
.mobile-buttons{
  display: flex;
justify-content: space-between;
padding-top: 16px;
}

}
}
</style>
