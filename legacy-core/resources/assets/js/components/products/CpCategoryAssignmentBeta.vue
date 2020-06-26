<template lang="html">
  <div class="cp-box-standard category-select-wrapper">
    <div class="cp-box-heading">
      CATEGORIES
    </div>
    <div class="cp-box-body">
      <div class="category-select-body">
        <div class="col">
          <cp-select
          v-if="!loadingCategories"
          label="Select Parent Category"
          type="text"
          :options="categories"
          :key-value="{ name: 'name', value: 'id' }"
          v-model="parentSelect"
          @input="filterSubcategories(parentSelect)"></cp-select>
        </div>
        <div class="col">
          <cp-select
          label="Select Child Category"
          type="text"
          :options="filteredSubcategories"
          :key-value="{ name: 'name', value: 'id' }"
          v-model="childSelect"
          @input="addCategory(childSelect)">
          </cp-select>
        </div>
      </div>
      <div v-if="addedCategories && addedCategories.length > 0">
        <h4>ADDED CATEGORIES </h4>
        <hr />
      </div>
      <div class="selected-category-list category-select-body" v-for="(category, index) in newAddedCategories">
          <span v-if="category.parent" class="col">{{ category.parent.name}}</span>
          <span v-else class="col">{{ category.name}}</span>
          <span class="col" v-if="category.parent">{{ category.name }}</span>
          <i class="mdi mdi-close pointer right" @click="removeCategory(category.id, index)"></i>
      </div>
    </div>
  </div>
</template>

<script>
const Categories = require('../../resources/categories.js')

module.exports = {
  data () {
    return {
      loadingCategories: true,
      parentSelect: null,
      childSelect: null,
      categories: [],
      subCategories: [],
      selectedCategory: {},
      filteredSubcategories: [],
      addedCategoryIds: [],
      newAddedCategories: []
    }
  },
  props: {
    addedCategories: {
      type: Array,
      default () {
        return []
      }
    }
  },
  mounted () {
    this.newAddedCategories = this.addedCategories
    this.getCategories()
    this.getSubCategories()
  },
  methods: {
    getCategories () {
      this.loadingCategories = true
      Categories.categoriesGet()
        .then((response) => {
          if (response.error) {
            this.validationErrors = response.message
            return
          }
          this.loadingCategories = false
          this.categories = response
        })
    },
    getSubCategories () {
      Categories.subCategoriesGet()
        .then((response) => {
          if (response.error) {
            this.validationErrors = response.message
            return
          }
          this.subCategories = response
        })
    },
    removeCategory (id, index) {
      this.newAddedCategories.splice(index, 1)
      for (var j = 0; j < this.addedCategoryIds.length; j++) {
        if (this.addedCategoryIds[j] === id) {
          this.addedCategoryIds.splice(j, 1)
        }
      }
    },
    addCategory (categoryId) {
      let category = this.obtainCategoryObject(categoryId, this.filteredSubcategories)
      if (category && category.name !== 'None') {
        this.newAddedCategories.push(category)
      } else {
        let parentCategory = this.obtainCategoryObject(this.parentSelect, this.categories)
        this.newAddedCategories.push(parentCategory)
      }
      this.childSelect = 'none'
      this.$emit('categories', this.newAddedCategories)
    },
    obtainCategoryObject (parentId, categoryArray) {
      for (var i = 0; i < categoryArray.length; i++) {
        if (!categoryArray[i].id) {
          continue
        } else if (categoryArray[i].id === parentId || categoryArray[i].id.toString() === parentId) {
          return categoryArray[i]
        }
      }
    },
    filterSubcategories (parentId) {
      let parent = this.obtainCategoryObject(parentId, this.categories)

      this.filteredSubcategories = [
        { name: 'Add Subcategory', id: 'none' },
        { name: 'None', id: null }
      ]
      if (!parent) {
        return []
      }
      for (var i = 0; i < this.subCategories.length; i++) {
        if (parent.id === this.subCategories[i].parent_id || this.subCategories[i].id === 'none') {
          this.filteredSubcategories.push(this.subCategories[i])
        }
      }
    }
  },
  components: {
    CpSelect: require('../../cp-components-common/inputs/CpSelect.vue')
  }
}
</script>

<style lang="scss" scoped>
.category-select-wrapper {
  hr {
    display: block;
    margin-top: 0px;

  }
  h4 {
    margin-top: 20px;
    margin-bottom: 10px;
  }
}
.category-select-body {
  display: flex;
  &.selected-category-list {
    margin-top: 15px;
    border-bottom: 1px solid lightgrey;
    span {
      padding: 5px;
    }
  }
  .col {
    flex: 1;
    .right { float: right }
    select {
      color: red;
      width: 100% !important;
    }
  }
}
</style>
