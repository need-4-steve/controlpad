<template>
  <div class="category-create-edit">
    <img v-if="category.media && category.media[0]" style="max-width: 50%" :src="category.media[0].url_xs" alt="">
    <div id="dropzone">
      <label>Add New Image</label>
        <form action="/upload-media" method="POST" class="dropzone" id="drop-zone-media">
            <cp-input v-if="category.media && category.media[0]" type="hidden" name="media[]" v-model="category.media[0]"></cp-input>
        </form>
    </div>
    <form class="cp-form-standard" v-if="category">
      <div>
        <label>Category Name</label>
        <cp-input :class="{ error: validationErrors.name }" type="text" v-model="category.name"></cp-input>
        <span v-show="validationErrors.name" class="cp-warning-message">{{ validationErrors.name}}</span>
      </div>
      <div class="category-checkbox">
        <label>Show Category On Store:</label>
        <input type="checkbox"  v-model="category.show_on_store">
      </div>
      <button v-if="!edit" class="cp-button-standard" type="button" name="button"  :disabled="proccessing" @click="CreateOrUpdate">Create Category</button>
      <button v-if="edit" class="cp-button-standard" type="button" name="button"  :disabled="proccessing" @click="CreateOrUpdate">Update Category</button>
      <div class="cp-box-standard subcategory">
      <div class="inactive-overlay" v-show="!edit"></div>
        <label>Add Subcategory</label>
        <cp-input type="text" v-model="newChild.name"></cp-input>
        <button class="cp-button-standard" :disabled="proccessing" type="button" name="button" @click="createCategory()">Add</button>
      <div>
      <label>Subcategory Name</label>
        <span v-for="(child, index) in category.children">
          <div class="subcategory-input-wrapper">
          <cp-input type="text" v-model="child.name"></cp-input>
          <i class="mdi mdi-close x" @click="deleteCategory(child, index)" role="button"></i>
        </div>
        </span>
      </div>
    </div>
    </form>
  </div>
</template>
<script>
const Category = require('../../resources/categories.js')
const Dropzone = require('dropzone')
const Media = require('../../resources/media.js')
Dropzone.autoDiscover = false

module.exports = {
  data: () => ({
    newChild: {},
    validationErrors: {},
    proccessing: false
  }),
  props: {
    category: {
      type: Object,
      default: function () {
        return {
          name: '',
          children: [],
          media: [{
            id: null
          }] }
      }
    },
    edit: {
      type: Boolean,
      default: false
    }
  },
  mounted () {
    this.uploadDropzone()
  },
  methods: {
    CreateOrUpdate: function () {
      if (this.edit) {
        this.updateCategory()
      } else {
        this.createCategory()
      }
    },
    updateCategory: function () {
      this.proccessing = true
      let formObject

      if (this.category.media.length !== 0) {
        formObject = {
          'id': this.category.id,
          'name': this.category.name,
          'file': this.category.media[0].id,
          'show_on_store': this.category.show_on_store
        }
      } else {
        formObject = {
          id: this.category.id,
          name: this.category.name,
          show_on_store: this.category.show_on_store
        }
      }
      Category.editCategoryPut(formObject)
        .then((response) => {
          if (response.error) {
            this.validationErrors = response.message
            this.$toast(response.message, {error: true})
            return
          }
          this.proccessing = false
          this.$toast('Successfully updated', { dismiss: false })
          this.$emit('update-category', this.category)
        })
    },
    createCategory: function () {
      this.proccessing = true
      this.validationErrors = {}
      let formObject

      if (this.newChild.name) {
        formObject = {
          name: this.newChild.name,
          parent_id: this.category.id
        }
      } else if (this.category.media.length !== 0) {
        formObject = {
          'id': this.category.id,
          'name': this.category.name,
          'file': this.category.media[0].id,
          'show_on_store': this.category.show_on_store
        }
      } else if (this.parent_id === undefined || this.parent_id === null) {
        formObject = {
          name: this.category.name,
          show_on_store: this.category.show_on_store,
          parent_id: null,
          file: this.category.media[0].id
        }
        this.category.children = []
      } else {
        formObject = {
          name: this.newChild.name,
          show_on_store: this.category.show_on_store,
          parent_id: this.category.parent_id,
          file: this.category.media[0].id
        }
      }
      Category.categoryCreate(formObject)
        .then((response) => {
          if (response.error) {
            this.validationErrors = response.message
            this.$toast(response.message, {error: true})
          } else {
            if (this.newChild.name) {
              this.proccessing = false
              this.category.children.push(response)
              this.newChild.name = ''
              return
            }
            this.proccessing = false
            this.$toast('Successfully created.', {dismiss: false})
            this.$emit('update-category', response)
          }
        })
    },
    deleteCategory: function (category) {
      Category.deleteCategory(category.id)
        .then((response) => {
          if (response.error) {
            this.$toast(response.message, { error: true })
          } else {
            for (var i = 0; i < this.category.children.length; i++) {
              if (this.category.children[i].id === category.id) {
                this.category.children.splice([i], 1)
              }
            }
            this.$toast('Category Deleted.', { dismiss: false })
          }
        })
    },
    uploadDropzone () {
      var $this = this
      var uploadZoneConfig = Media.dropzoneConfig()
      uploadZoneConfig.maxFiles = 1
      uploadZoneConfig.success = function (file, response) {
        $this.category.media[0] = response
        $this.$toast('File successfully uploaded.')
      }
      uploadZoneConfig.error = function (file, response) {
        $this.$toast('There was an error uploading your image. Try again or contact support.', { error: true })
      }
      uploadZoneConfig.removedfile = function (file, response) {
        var _ref
        if (file.previewElement) {
          if ((_ref = file.previewElement) != null) {
            _ref.parentNode.removeChild(file.previewElement)
          }
        }
        for (var i = 0; i < $this.category.media.length; ++i) {
          if ($this.category.media[i] === file.imageId) {
            $this.category.media.splice(i, 1)
          }
        }
        return $this.uploadZone._updateMaxFilesReachedClass()
      }
      this.uploadZone = new Dropzone('#drop-zone-media', uploadZoneConfig)
    }
  },
  components: {
    'CpDropzone': require('../../cp-components-common/CpDropzone.vue'),
    CpPhotos: require('../../cp-components-common/temp-dependencies/CpPhotos.vue'),
    CpInput: require('../../cp-components-common/inputs/CpInput.vue')
  }
}
</script>
<style lang="scss" scoped>
.category-create-edit {
  .subcategory-input-wrapper{
    span {
      width: 45%;
    }
    display: flex;
    .x {
      padding: 15px 10px;
    }
  }
  .categories-form-button{
    margin-top: 16px;
  }
 .cp-input {
    width: 95%;
  }
  .category-checkbox {
    display: flex;
    label {
      width: 35%;
    }
  }
}
</style>
