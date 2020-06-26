const Request = require('../resources/requestHandler.js')
const config = require('env').apis
const apiUrl = config.inventory

module.exports = {
  getProduct (params, id) {
    return Request.get(apiUrl + 'products/' + id, params)
  },
  getProducts (params) {
    return Request.get(apiUrl + 'products', params)
  },
  createProduct (params) {
    return Request.post(apiUrl + 'products', params)
  },
  updateProduct (params, id) {
    return Request.patch(apiUrl + 'products/' + id, params)
  },
  deleteProduct (id) {
    return Request.delete(apiUrl + 'products/' + id)
  },
  getVariant (params, id) {
    return Request.get(apiUrl + 'variants/' + id, params)
  },
  getVariants (params) {
    return Request.get(apiUrl + 'variants', params)
  },
  createVariant (params) {
    return Request.post(apiUrl + 'variants', params)
  },
  updateVariant (params, id) {
    return Request.patch(apiUrl + 'variants/' + id, params)
  },
  deleteVariant (id) {
    return Request.delete(apiUrl + 'variants/' + id)
  },
  getItem (params, id) {
    return Request.get(apiUrl + 'items/' + id, params)
  },
  getItems (params) {
    return Request.get(apiUrl + 'items', params)
  },
  createItem (params) {
    return Request.post(apiUrl + 'items', params)
  },
  updateItem (params, id) {
    return Request.patch(apiUrl + 'items/' + id, params)
  },
  deleteItem (id) {
    return Request.delete(apiUrl + 'items/' + id)
  },
  updateInventory (params, id) {
    return Request.patch(apiUrl + 'inventory/' + id, params)
  },
  getCategory (params, id) {
    return Request.get(apiUrl + 'category/' + id, params)
  },
  getCategories (params) {
    return Request.get(apiUrl + 'category', params)
  },
  createCategory (params) {
    return Request.post(apiUrl + 'category', params)
  },
  updateCategory (params, id) {
    return Request.patch(apiUrl + 'category/' + id, params)
  },
  deleteCategory (params, id) {
    return Request.delete(apiUrl + 'category/' + id, params)
  },
  getBundles (params) {
    return Request.get(apiUrl + 'bundles', params)
  },
  getBundle (params, id) {
    return Request.get(apiUrl + 'bundles/' + id, params)
  },
  createBundle (params) {
    return Request.post(apiUrl + 'bundles', params)
  },
  updateBundle (params, id) {
    return Request.patch(apiUrl + 'bundles/' + id, params)
  }
}
