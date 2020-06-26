<template>
  <div class="all-categories-wrapper">
    <div class="category-button"><button class="cp-button-standard" @click="categoryModal = true">Add Category</button></div>
    <table class="cp-table-standard desktop">
      <thead>
          <tr>
              <th>Image</th>
              <th>Category</th>
              <th>Subcategory</th>
              <th><!-- Delete --></th>
          </tr>
        </thead>
          <tbody>
              <tr v-for="category in categories" v-if="!loading">
                  <td v-if="category.media && category.media[0]"><a @click="showCategory(category)"><img :src="category.media[0].url_xxs"></a></td>
                  <td v-else></td>
                  <td><a @click="showCategory(category)">{{category.name}}</a></td>
                  <td>
                    <ul style="padding: 0;">
                      <li v-for="sub in category.children" class="no-bullets"><div class="sub-list-box"> {{sub.name}} </div></li>
                    </ul>
                  </td>
                  <td><i class="mdi mdi-close pointer" @click="showConfirm = true; pendingDelete = category"></i></td>
              </tr>
          </tbody>
    </table>
    <section  class="cp-table-mobile">
      <div v-for="category in categories" v-if="!loading">
        <div v-if="category.media && category.media[0]"><span>Image: </span><span><img :src="category.media[0].url_xxs"></span></div>
        <div><span>Category: </span><span>{{category.name}}</span></div>
        <div><span>Subcategory: </span>
          <span>
            <ul style="padding: 0;">
              <li v-for="sub in category.children" class="no-bullets"><div class="sub-list-box"> {{sub.name}} </div></li>
            </ul>
          </span>
        </div>
        <div><span></span><span><a @click="showCategory(category)"><i class="mdi mdi-pencil"></i></a></span></div>
        <div><span></span><span><i class="mdi mdi-cross pointer" @click="showConfirm = true; pendingDelete = category.id"></i></span></div>
      </div>
    </section>
    <!-- Edit Modal -->
    <transition name="modal" v-show="!loading">
    <section class="cp-modal-standard" v-if="categoryModal">
      <div class="cp-modal-body">
        <i class="mdi mdi-close x" @click="categoryModal = false; edit = false; selectedCategory = JSON.parse(JSON.stringify(emptyCategory))" role="button"></i>
        <h2 v-if="this.edit">Edit Category</h2>
        <h2 v-if="!this.edit">Create Category</h2>
        <cp-category-form :category="selectedCategory" @update-category="updateCategory" :edit="edit"></cp-category-form>
        <button class="cp-button-standard categories-modal-close-button" @click="categoryModal = false; edit = false; selectedCategory = JSON.parse(JSON.stringify(emptyCategory))" type="button" name="button">Close</button>
      </div>
    </section>
  </transition>
  <cp-confirm
  :show="showConfirm"
  :message="'Are you sure you want to delete this category?'"
  v-model="showConfirm"
  :callback="deleteCategory"
  :params="pendingDelete"></cp-confirm>
        <div class="align-center">
            <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
        </div>
  </div>
</template>
<script>
const Category = require('../../resources/categories.js')

module.exports = {
  data: () => ({
    edit: false,
    pendingDelete: {},
    showConfirm: false,
    selectedCategory: {
      name: '',
      show_on_store: false,
      children: [],
      media: [{
        id: null
      }] },
    emptyCategory: {
      name: '',
      children: [],
      media: [{
        id: null
      }],
      show_on_store: false
    },
    editCategory: {},
    categoryModal: false,
    loading: false,
    categories: [],
    indexRequest: {
      search_term: null,
      column: 'created_at',
      order: 'DESC',
      page: 1,
      per_page: 15
    }
  }),
  mounted () {
    this.getCategories()
  },
  methods: {
    updateCategory: function (update) {
      if (update.level === undefined) {
        this.selectedCategory.media[0] = update
        this.edit = true
        return
      } else {
        if (!update.children) {
          update.children = []
        }
        this.edit = true
        this.selectedCategory = update
        this.categories.push(update)
      }
    },
    showCategory: function (category) {
      this.edit = true
      this.categoryModal = true
      this.selectedCategory = category
    },
    getCategories: function () {
      this.loading = true
      Category.categoriesGet()
        .then((response) => {
          if (!response.error) {
            this.categories = response
            this.loading = false
          }
        })
    },
    deleteCategory: function (category) {
      Category.deleteCategory(category.id)
      .then((response) => {
        if (response.error) {
          this.$toast(response.message, { error: true })
          return
        } else {
          this.getCategories()
          this.pendingDelete = {}
          this.$toast('Category Deleted.', { dismiss: false })
        }
      })
    }
  },
  components: {
    CpSearchBox: require('../../cp-components-common/inputs/CpSearchBox.vue'),
    CpCategoryForm: require('./CpCategoryForm.vue'),
    CpConfirm: require('../../cp-components-common/CpConfirm.vue')
  }
}

</script>
<style lang="scss" scoped>
.all-categories-wrapper{
  .categories-modal-close-button{
    margin-top: 16px;
  }
  .x {
    float: right;
  }
  .category-button{
    margin-bottom: 10px;
  }

}
</style>
