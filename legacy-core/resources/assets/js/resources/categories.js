const Request = require('../resources/requestHandler.js')

module.exports = {
  categoriesGet: function (params) {
    return Request.get('/api/v1/categories/hierarchy', params)
  },
  subCategoriesGet: function (params) {
    return Request.get('/api/v1/categories/children', params)
  },
  categoryGet: function (id, params) {
    return Request.get('/api/v1/products/products-by-category/' + id, params)
  },
  productsGet: function () {
    return Request.productsByCategory
  },
  showCategoryGet: function (id, params) {
    return Request.get('/api/v1/categories/' + id, params)
  },
  editCategoryPut: function (category) {
    return Request.patch('/api/v1/categories/' + category.id, category)
  },
  deleteCategory: function (id, params) {
    return Request.delete('/api/v1/categories/' + id, params)
  },
  categoryCreate: function (category) {
    return Request.post('/api/v1/categories', category)
  }
}
