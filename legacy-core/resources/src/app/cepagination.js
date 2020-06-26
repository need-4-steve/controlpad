window.sms.addModule('cepaginate', (require, exports, module) => {
  const createPaginationObject = (pagination, response) => {
    const paginationObject = {}
    paginationObject.last_page = Math.ceil(response.count / pagination.limit)
    paginationObject.current_page = pagination.current_page
    paginationObject.data = response.ledger
    paginationObject.total = response.count
    return paginationObject
  }

  module.exports = {
    paginate (pagination, response) {
      return createPaginationObject(pagination, response)
    }
  }
})
