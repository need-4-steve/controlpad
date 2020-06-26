const JsPDF = require('../libs/jspdf.min.js')
const Auth = require('auth')
const Addresses = require('../resources/addresses.js')
const Orders = require('../resources/OrdersAPIv0.js')
const Invoices = require('../resources/invoice.js')
const JSZip = require('../libs/jszip.min.js')
const Users = require('../resources/users.js')
// const OrderFormatTypes = ['invoice', 'invoice-list', 'order', 'order-list']
const InvoiceDataTypes = ['invoice', 'invoice-list']
const PicklistFormatTypes = ['picklist-single', 'picklist']
const Moment = require('moment-timezone')

// Types single, list  |  invoice, order, picklist  | pdf, csv
// invoice -> Invoice ID: , Payment Status: Unpaid
/**
* fileName - will append .csv or .pdf - if missing a new window will open the data
* fileTypes - array conaining 'csv','pdf' if you want both files
* dataType - invoice, invoice-list, order, order-list, picklist-single, picklist - this will determine how data is pulled
* params - query params when index is called
*/
function CpOrdersFile (fileName, fileTypes, dataType, params) {
  this.dataType = dataType
  this.orders = []
  this.timezone = 'utc'
  this.showAddressOnInvoice = false
  this.sortColums = [
    'receipt_id',
    'store_owner_user_id',
    'total_price',
    'total_tax',
    'created_at',
    'updated_at',
    'status',
    'customer_id',
    'total_discount'
  ]

  if (['invoice', 'order', 'picklist-single'].includes(this.dataType)) {
    // Single order
    this.params = params
  } else {
    this.params = {
      per_page: 500,
      page: 1
    }
    Object.assign(this.params, params)
  }

  this.fileName = fileName
  this.fileTypes = fileTypes // pdf, csv
  this.stop = false
  this.running = false
}

CpOrdersFile.prototype.run = function run (callback) {
  this.callback = callback
  if (this.running) {
    this.message({error: true, message: 'Already running'})
    // TODO log error
    return
  }

  if (!this.fileTypes || this.fileTypes.length === 0) {
    this.message({error: true, message: 'No file type selected'})
    return
  }
  this.getSettings()
}

CpOrdersFile.prototype.cancel = function cancel () {
  this.stop = true
}

CpOrdersFile.prototype.handleOrderResponse = function handleOrderResponse (response) {
  if (this.stop) {
    return
  }
  return new Promise((resolve, reject) => {
    if (response.data && response.data.length > 0) {
      this.orders = this.orders.concat(response.data)
      if ((response.to - response.from) >= (this.params.per_page - 1)) {
        this.params.page++
        this.pullOrders()
      } else {
        this.process()
      }
    } else if (!response.error) {
      // Single order
      this.orders.push(response)
      this.process()
    } else {
      this.message({error: true, message: 'Failed to get orders'})
    }
    resolve()
  })
}

CpOrdersFile.prototype.pullOrders = function pullOrders () {
  if (this.stop) {
    return
  }
  if (this.timezone !== 'utc') {
    if (this.params.start_date) {
      this.params.start_date = Moment.tz(this.params.start_date, 'YYYY-MM-DD', this.timezone).tz('utc').format('YYYY-MM-DD HH:mm:ss')
    }
    if (this.params.end_date) {
      this.params.end_date = Moment.tz(this.params.end_date, 'YYYY-MM-DD', this.timezone).endOf('day').tz('utc').format('YYYY-MM-DD HH:mm:ss')
    }
  }
  switch (this.dataType) {
    case 'invoice':
      this.listName = 'items'
      this.params.items = 1 // expand items
      Invoices.getPdfInvoiceByUid(this.params.orderId).then((result) => { return this.handleOrderResponse(result) })
      break
    case 'invoice-list':
      this.listName = 'items'
      this.params.items = 1 // expand items
      this.params.column = this.params.sort_by
      this.params.order = this.params.in_order
      Invoices.indexPdf(this.params).then((result) => { return this.handleOrderResponse(result) })
      break
    case 'order':
    case 'picklist-single':
      this.listName = 'lines'
      this.params.orderlines = 1
      Orders.get(this.params, this.params.orderId).then((result) => { return this.handleOrderResponse(result) })
      break
    default:
      this.listName = 'lines'
      this.params.orderlines = 1 // Expand lines
      if (!this.sortColums.includes(this.params.sort_by)) {
        if (this.params.sort_by === 'subtotal_price') {
          this.params.sort_by = 'total_price'
        } else {
          this.params.sort_by = 'created_at'
        }
      }
      Orders.getOrders(this.params).then((result) => { return this.handleOrderResponse(result) })
      break
  }
}

function getGlobal (key, parseValue = true) {
  if (window.Vue.prototype.$getGlobal(key)) {
    if (parseValue) {
      return window.Vue.prototype.$getGlobal(key).value
    } else {
      return window.Vue.prototype.$getGlobal(key)
    }
  } else {
    console.log('could not find ' + key)
    return ''
  }
}

CpOrdersFile.prototype.message = function message (message) {
  if (this.callback) {
    this.callback(message)
  }
}

CpOrdersFile.prototype.getSettings = function getSettings () {
  this.companyName = getGlobal('company_name')
  this.companyAddress = getGlobal('company_address')
  if (!this.companyAddress) {
    this.companyAddress = getGlobal('address')
  }
  this.companyLogoUrl = getGlobal('back_office_logo')
  this.ein = getGlobal('ein', false)
  Users.userSettings(Auth.getOwnerId()).then((response) => {
    if (response.timezone) {
      this.timezone = response.timezone
    }
    if (response.showAddressOnInvoice) {
      this.showAddressOnInvoice = (response.show_address_on_invoice && Auth.getOwnerId() !== 1)
    }
    if (this.showAddressOnInvoice) {
      var addressParams = {
        label: 'Business',
        addressable_id: Auth.getOwnerId(),
        addressable_type: 'App\\Models\\User'
      }
      Addresses.show(addressParams).then((response) => {
        this.businessAddress = response
        Users.userCompanyInfo().then((response) => {
          this.companyInfo = response
          Users.getAuthUser().then((response) => {
            this.storeOwner = response
            this.getLogo()
          })
        })
      })
    } else {
      this.getLogo()
    }
  })
}

CpOrdersFile.prototype.getLogo = function getLogo () {
  if (this.companyLogoUrl) {
    fetch(this.companyLogoUrl + '?cache=none').then((response) => {
      if (response.ok) {
        response.blob().then((blob) => {
          this.imgType = blob.type
          var reader = new FileReader()
          reader.readAsDataURL(blob)
          reader.onloadend = () => {
            this.img = new Image()
            this.img.src = reader.result
            this.pullOrders()
          }
        })
      } else {
        this.pullOrders()
      }
    })
  } else {
    this.pullOrders()
  }
}

CpOrdersFile.prototype.process = function process () {
  if (this.stop) {
    return
  }
  this.orderCount = this.orders.length
  if (this.orderCount === 0) {
    this.message({error: true, message: 'No orders'})
    return
  }
  if (this.dataType === 'invoice' || this.dataType === 'invoice-list') {
    this.itemIdTitle = 'Invoice ID'
    this.itemIdName = 'uid'
  } else {
    this.itemIdTitle = 'Order ID'
    this.itemIdName = 'receipt_id'
  }
  var order
  for (var i = 0; i < this.orderCount; i++) {
    order = this.orders[i]
    if (this.timezone !== 'utc') {
      order.created_at = Moment.tz(order.created_at, 'YYYY-MM-DD HH:mm:ss', 'utc').tz(this.timezone).format('D MMM YYYY h:mm:ss A z')
    }
    order.total_discount = window.Vue.options.filters.currency(order.total_discount)
    order.subtotal_price = window.Vue.options.filters.currency(order.subtotal_price)
    order.total_shipping = window.Vue.options.filters.currency(order.total_shipping)
    if (order.total_tax !== undefined) {
      order.total_tax = window.Vue.options.filters.currency(order.total_tax)
    }
    if (order.total_price) {
      order.total_price = window.Vue.options.filters.currency(order.total_price)
    }
  }

  var csvFile = null
  var pdfFile = null
  if (this.fileTypes.includes('csv')) {
    if (InvoiceDataTypes.includes(this.dataType)) {
      csvFile = this.processInvoiceCsv()
    } else {
      csvFile = this.processCsv()
    }
  }
  if (this.fileTypes.includes('pdf')) {
    pdfFile = this.processPdf()
  }
  if (csvFile != null && pdfFile != null) {
    var zipFile = new JSZip()
    zipFile.file((this.fileName ? this.fileName + '.csv' : 'Download.csv'), csvFile)
    zipFile.file((this.fileName ? this.fileName + '.pdf' : 'Download.pdf'), pdfFile.output('blob'))
    console.log('Starting zip file')
    zipFile.generateAsync({type: 'blob'})
      .then((content) => {
        console.log('Downloading zip file')
        console.log(content)
        this.download((this.fileName ? this.fileName : 'Download'), 'zip', window.URL.createObjectURL(content))
        this.message({error: false, message: 'Finished'})
      })
  } else if (csvFile != null) {
    var uri = window.URL.createObjectURL(new window.Blob([csvFile], {
      type: 'text/plain'
    }))
    var csvFileName = (this.fileName ? this.fileName : 'Download') // Add .csv
    this.download(csvFileName, 'csv', uri)
    this.message({error: false, message: 'Finished'})
  } else if (pdfFile != null) {
    var pdfFileName = (this.fileName ? this.fileName : null) // Add .pdf or stay null
    this.download(pdfFileName, 'pdf', window.URL.createObjectURL(pdfFile.output('blob')))
    this.message({error: false, message: 'Finished'})
  } else {
    this.message({error: true, message: 'No file created'})
  }
}

CpOrdersFile.prototype.processInvoiceCsv = function processInvoiceCsv () {
  var csv = 'Order Date,' + this.itemIdTitle + ',Receipt ID,Buyer ID,Buyer Name,Buyer Email,Seller ID,Seller Name,' +
  'Subtotal,Discount,' +
  'Order Type,Item Count,Options\n'

  var order
  for (var i = 0; i < this.orderCount; i++) {
    if (this.stop) {
      return
    }

    order = this.orders[i]
    csv +=
    order.created_at + ',' +
    order.id + ',' +
    order[this.itemIdName] + ',' +
    order.customer_id + ',' +
    csvConvert(order.customer_first_name + ' ' + order.customer_last_name) + ',' +
    order.customer_email + ',' +
    order.store_owner_user_id + ',' +
    ',' + // TODO seller full name, not in the orders api
    csvConvert(order.subtotal_price) + ',' +
    csvConvert(order.total_discount) + ',' +
    (order.order_type ? csvConvert(order.order_type.name) : '') + ','
    var itemCount = 0
    let lineCount = 0
    if (order[this.listName]) {
      lineCount = order[this.listName].length
      for (var l = 0; l < lineCount; l++) {
        itemCount += order[this.listName][l].quantity
      }
    }
    csv +=
    itemCount + ','
    var itemNames = []
    var item
    for (var l = 0; l < lineCount; l++) {
      item = order[this.listName][l]
      itemNames.push(item.name + ' ' + item.variant + ' ' + item.option)
    }
    csv +=
    csvConvert(itemNames.join(', ')) +
    '\n'
  }
  return csv
}

CpOrdersFile.prototype.processCsv = function processCsv () {
  var csv = 'Status,Order Date,' + this.itemIdTitle + ',Pid,Receipt ID,Buyer ID,Buyer Name,Buyer Email,Seller ID,Seller Name,Payment Type,Cash Type,Payment ID,Gateway Transaction ID,' +
  'Subtotal,Discount,Tax,Shipping,Total,' +
  'Cash Sale,Source,Order Type,Shipping Address 1,Shipping Address 2,Shipping City,Shipping State,Shipping Zip,' +
  'Billing Address 1,Billing Address 2,Billing City,Billing State,Billing Zip,Item Count,Options\n'

  var order
  for (var i = 0; i < this.orderCount; i++) {
    if (this.stop) {
      return
    }

    order = this.orders[i]
    csv +=
    order.status + ',' +
    order.created_at + ',' +
    order.id + ',' +
    order.pid + ',' +
    order[this.itemIdName] + ',' +
    order.buyer_id + ',' +
    csvConvert(order.buyer_first_name + ' ' + order.buyer_last_name) + ',' +
    order.buyer_email + ',' +
    order.seller_id + ',' +
    ',' + // TODO seller full name, not in the orders api
    order.payment_type + ',' +
    order.cash_type + ',' +
    csvConvert(order.transaction_id) + ',' +
    csvConvert(order.gateway_reference_id) + ',' +
    csvConvert(order.subtotal_price) + ',' +
    csvConvert(order.total_discount) + ',' +
    csvConvert(order.total_tax) + ',' +
    csvConvert(order.total_shipping) + ',' +
    csvConvert(order.total_price) + ',' +
    (order.cash ? 'yes' : 'no') + ',' +
    csvConvert(order.source) + ',' +
    csvConvert(order.type_description) + ','
    if (order.shipping_address) {
      csv +=
      csvConvert(order.shipping_address.line_1) + ',' +
      csvConvert(order.shipping_address.line_2) + ',' +
      csvConvert(order.shipping_address.city) + ',' +
      csvConvert(order.shipping_address.state) + ',' +
      csvConvert(order.shipping_address.zip) + ','
    } else {
      csv += ',,,,,'
    }
    if (order.billing_address) {
      csv +=
      csvConvert(order.billing_address.line_1) + ',' +
      csvConvert(order.billing_address.line_2) + ',' +
      csvConvert(order.billing_address.city) + ',' +
      csvConvert(order.billing_address.state) + ',' +
      csvConvert(order.billing_address.zip) + ','
    } else {
      csv += ',,,,,'
    }
    var itemCount = 0
    let lineCount = 0
    if (order[this.listName]) {
      lineCount = order[this.listName].length
      for (var l = 0; l < lineCount; l++) {
        itemCount += order[this.listName][l].quantity
      }
    }
    csv +=
    itemCount + ','
    var itemNames = []
    var item
    for (var l = 0; l < lineCount; l++) {
      item = order[this.listName][l]
      itemNames.push(item.name + ' ' + item.variant + ' ' + item.option)
    }
    csv +=
    csvConvert(itemNames.join(', ')) +
    '\n'
  }
  return csv
}

function csvConvert (value) {
  if (value !== undefined) {
    if (typeof value !== 'string') {
      return value
    }
    var result = value.replace('"', '""')
    if (result.includes(',')) {
      result = '"' + result + '"'
    }
    return result
  } else {
    return ''
  }
}

CpOrdersFile.prototype.processPdf = function processPdf () {
  if (this.stop) {
    return
  }
  this.doc = new JsPDF('p', 'pt', 'letter')
  this.innerBox = {
    right: this.doc.internal.pageSize.width - 35,
    left: 35,
    top: 35,
    bottom: this.doc.internal.pageSize.height - 50,
    width: this.doc.internal.pageSize.width - 70,
    centerX: (this.doc.internal.pageSize.width / 2)
  }
  var skuName
  if (InvoiceDataTypes.includes(this.dataType)) {
    skuName = 'sku'
  } else {
    skuName = 'manufacturer_sku'
  }
  if (PicklistFormatTypes.includes(this.dataType)) {
    this.columns = [
      {description: 'SKU', name: 'custom_sku', altName: skuName, width: 100},
      {description: 'Description', name: 'name', width: 186},
      {description: 'Variant', name: 'variant', width: 120},
      {description: 'Option', name: 'option', width: 70},
      {description: 'QTY', name: 'quantity', width: 60, align: 'right'}
    ]
  } else {
    this.columns = [
      {description: 'SKU', name: 'custom_sku', altName: skuName, width: 100},
      {description: 'Description', name: 'name', width: 166},
      {description: 'Variant', name: 'variant', width: 100},
      {description: 'Option', name: 'option', width: 60},
      {description: 'QTY', name: 'quantity', width: 60},
      {description: 'Price', name: 'price', width: 50, align: 'right', filter: 'currency'}
    ]
  }

  var order
  for (var i = 0; i < this.orderCount; i++) {
    if (this.stop) {
      return
    }
    order = this.orders[i]
    this.orderPage = 0 // Incremented by startNewPage()
    if (i > 0) {
      this.doc.addPage() // Each order starts on a new page
    }
    this.writePageHeader(order)
    var startY = 132
    try {
      this.doc.addImage(this.img, this.imgType, this.innerBox.left, this.innerBox.top, (40 / this.img.height) * this.img.width, 40, 'logo')
    } catch (err) {
      console.log(err)
      // logo will be missing, maybe log error in the future
    }
    this.doc.setFontStyle('bold')
    this.doc.text(this.companyName, this.innerBox.left, startY)
    startY += 10
    this.doc.setFontStyle('normal')
    if (this.ein.show) {
      this.doc.text('EIN: ' + this.ein.value, this.innerBox.left, startY)
      startY += 10
    }
    this.doc.text(this.companyAddress, this.innerBox.left, startY)
    if (this.showAddressOnInvoice) {
      this.writeCompanyInfo()
    }
    if (order.billing_address) {
      this.writeAddress(order.billing_address, order.buyer_email, 'Bill To:', true)
    } else {
      this.writeCustomer(order)
    }
    if (order.shipping_address) {
      this.writeAddress(order.shipping_address, null, 'Ship To:', false)
    }
    this.doc.line(this.innerBox.left, 252, this.innerBox.right, 252)
    startY = 253
    this.doc.setFontSize(10)
    startY = this.writeHeaders(startY, order)
    var lineCount
    if (order[this.listName]) {
      lineCount = order[this.listName].length
    } else {
      lineCount = 0
    }
    for (var lineNumber = 0; lineNumber < lineCount; lineNumber++) {
      startY = this.writeRow(order[this.listName][lineNumber], startY, (lineNumber % 2 === 0))
    }
    this.doc.line(this.innerBox.left, startY, this.innerBox.right, startY)
    startY += 3
    this.writeSummary(order, startY)
  }
  return this.doc
}

CpOrdersFile.prototype.download = function download (fileName, ext, url) {
  if (this.stop) {
    return
  }
  if (!fileName) {
    // If no file name defined, try and open in new window
    // Popup blocker can prevent window as an example for null window
    if (window.open(url) != null) {
      return
    }
    fileName = 'Download'
  }
  var a = document.createElement('a')
  document.body.appendChild(a)
  a.style = 'display: none'
  a.href = url
  a.download = fileName + '.' + ext
  a.click()
  window.URL.revokeObjectURL(url)
  a.parentNode.removeChild(a)
}

CpOrdersFile.prototype.writeCustomer = function writeCustomer (invoice) {
  var left = this.innerBox.left
  var y
  this.doc.setFontStyle('bold')
  this.doc.text('Customer:', left, 200)
  this.doc.setFontStyle('normal')
  y = 210
  var name
  if (invoice.customer_first_name && invoice.customer_first_name.length > 0) {
    if (invoice.customer_last_name && invoice.customer_last_name.length > 0) {
      name = invoice.customer_first_name + ' ' + invoice.customer_last_name
    } else {
      name = invoice.customer_first_name
    }
  } else if (invoice.customer_last_name && invoice.customer_last_name.length > 0) {
    name = invoice.customer_last_name
  }
  if (name) {
    this.doc.text(name, left, y)
    y += 10
  }
  if (invoice.customer_email) {
    this.doc.text(invoice.customer_email, left, y)
  }
}

CpOrdersFile.prototype.writeAddress = function writeAddress (address, email, header, leftSide) {
  var left = this.innerBox.left
  var y
  if (!leftSide) {
    left = ((this.innerBox.right + this.innerBox.left) / 2)
  }
  this.doc.setFontStyle('bold')
  this.doc.text(header, left, 200)
  this.doc.setFontStyle('normal')
  y = 210
  if (address.name) {
    this.doc.text(address.name, left, y)
    y += 10
  }
  if (address.line_1) {
    this.doc.text(address.line_1, left, y)
    y += 10
  }
  if (address.line_2) {
    this.doc.text(address.line_2, left, y)
    y += 10
  }
  if (address.city && address.state && address.zip) {
    this.doc.text(address.city + ', ' + address.state + ', ' + address.zip, left, y)
    y += 10
  }
  if (address.email) {
    this.doc.text(address.email, left, y)
  } else if (email) {
    this.doc.text(email, left, y)
  }
}

CpOrdersFile.prototype.writeCompanyInfo = function writeCompanyInfo () {
  var left = ((this.innerBox.right + this.innerBox.left) / 2)
  var startY = 132
  if (this.companyInfo.name) {
    this.doc.setFontStyle('bold')
    this.doc.text(this.companyInfo.name, left, startY)
    this.doc.setFontStyle('normal')
    startY += 10
  }
  if (this.businessAddress) {
    this.doc.setFontStyle('bold')
    this.doc.text(this.businessAddress.name, left, startY)
    startY += 10
    this.doc.setFontStyle('normal')
    this.doc.text(this.storeOwner.email, left, startY)
    startY += 10
    this.doc.text(this.businessAddress.address_1, left, startY)
    startY += 10
    if (this.businessAddress.address_2) {
      this.doc.text(this.businessAddress.address_2, left, startY)
      startY += 10
    }
    this.doc.text(this.businessAddress.city + ', ' + this.businessAddress.state + ', ' + this.businessAddress.zip, left, startY)
  } else {
    this.doc.text(this.storeOwner.full_name, left, startY)
    startY += 10
    this.doc.text(this.storeOwner.email, left, startY)
  }
}

CpOrdersFile.prototype.writeRow = function writeRow (order, startY, fill) {
  var endY = startY + this.doc.internal.getLineHeight() + 6
  if (endY > this.innerBox.bottom) {
    startY = this.startNewPage(order)
    endY = startY + this.doc.internal.getLineHeight() + 6
  }
  var textBottom = startY + this.doc.internal.getLineHeight()
  if (fill) {
    this.doc.setFillColor(219, 219, 219)
    this.doc.rect(this.innerBox.left, startY, this.innerBox.width, (endY - startY), 'f*')
  }
  var column
  var startX = this.innerBox.left + 3
  var endX
  var align
  var text
  for (var i = 0; i < this.columns.length; i++) {
    column = this.columns[i]
    endX = startX + column.width
    this.doc.internal.write('q')
    this.doc.rect(startX, startY, column.width - 3, endY - startY, null)
    this.doc.clip_fixed()
    if (column.align && column.align === 'right') {
      startX = endX - 3
      align = 'right'
    } else {
      align = null
    }
    if (order[column.name] != null) {
      text = order[column.name].toString()
    } else if (column.altName && order[column.altName] != null) {
      text = order[column.altName].toString()
    } else {
      text = ''
    }
    if (column.filter && column.filter === 'currency') {
      text = window.Vue.options.filters.currency(text)
    }
    this.doc.text(text, startX, textBottom, align)
    this.doc.internal.write('Q')
    startX = endX
  }
  // this.doc.line(this.innerBox.left, endY, this.innerBox.right, endY)
  return endY + 1
}

CpOrdersFile.prototype.writeHeaders = function writeHeaders (startY, order) {
  // Headers
  this.doc.setFontStyle('bold')
  var column
  var startX = this.innerBox.left + 3
  var endY = startY + this.doc.internal.getLineHeight() + 6
  if (endY > this.innerBox.bottom) {
    startY = this.startNewPage(order)
    endY = startY + this.doc.internal.getLineHeight() + 6
  }
  var textBottom = startY + this.doc.internal.getLineHeight()
  for (var i = 0; i < this.columns.length; i++) {
    column = this.columns[i]
    this.doc.internal.write('q')
    this.doc.rect(startX, startY, column.width - 3, endY - startY, null)
    this.doc.clip_fixed()
    this.doc.text(column.description, startX, textBottom)
    this.doc.internal.write('Q')
    startX = startX + column.width
  }
  this.doc.line(this.innerBox.left, endY, this.innerBox.right, endY)
  this.doc.setFontStyle('normal')
  return endY + 1
}

CpOrdersFile.prototype.writeSummary = function writeSummary (order, startY) {
  var leftX = this.innerBox.left
  for (var i = 0; i < this.columns.length - 2; i++) {
    leftX += this.columns[i].width
  }
  var itemCount = 0
  if (order[this.listName]) {
    for (var l = 0; l < order[this.listName].length; l++) {
      itemCount += order[this.listName][l].quantity
    }
  }
  var rightX = this.innerBox.right - 6
  if (startY + this.getSummaryHeight() > this.innerBox.bottom) {
    startY = this.startNewPage(order)
  }
  if (PicklistFormatTypes.includes(this.dataType)) {
    startY = this.writeSummaryLine(startY, leftX, rightX, 'Total Items:', itemCount.toString())
  } else {
    startY = this.writeSummaryLine(startY, leftX, rightX, 'Discount:', order.total_discount)
    startY = this.writeSummaryLine(startY, leftX, rightX, 'Subtotal:', order.subtotal_price)
    startY = this.writeSummaryLine(startY, leftX, rightX, 'Shipping:', order.total_shipping)
    if (order.total_tax) {
      startY = this.writeSummaryLine(startY, leftX, rightX, 'Sales Tax:', order.total_tax)
    }
    if (order.total_price) {
      startY = this.writeSummaryLine(startY, leftX, rightX, 'Order Total:', order.total_price)
    }
  }
  this.doc.line(this.innerBox.left, startY, this.innerBox.right, startY)
  this.writeOrderPageNumber(order)
}

CpOrdersFile.prototype.writeSummaryLine = function writeSummaryLine (startY, leftX, rightX, title, value) {
  this.doc.setFontStyle('bold')
  this.doc.text(title, leftX, startY + this.doc.internal.getLineHeight())
  this.doc.setFontStyle('normal')
  this.doc.text(value, rightX, startY + this.doc.internal.getLineHeight(), 'right')
  return startY + this.doc.internal.getLineHeight() + 7
}

CpOrdersFile.prototype.getSummaryHeight = function getSummaryHeight () {
  if (!this.summaryHeight) {
    if (PicklistFormatTypes.includes(this.dataType)) {
      this.summaryHeight = this.doc.internal.getLineHeight() + 6
    } else {
      this.summaryHeight = (this.doc.internal.getLineHeight() + 6) * 5
    }
  }
  return this.summaryHeight
}

CpOrdersFile.prototype.writePageHeader = function writePageHeader (order) {
  this.doc.setFontSize(8)
  this.doc.text(this.itemIdTitle + ': ' + order[this.itemIdName], this.innerBox.right, this.innerBox.top, null, null, 'right')
  this.doc.text('Payment Status: Paid', this.innerBox.right, this.innerBox.top + 16, null, null, 'right')
  this.doc.text('Order Date: ' + order.created_at, this.innerBox.right, this.innerBox.top + 32, null, null, 'right')
  return this.innerBox.top + 48
}

CpOrdersFile.prototype.writeOrderPageNumber = function writeOrderPageNumber (order) {
  this.orderPage++
  this.doc.text('Page ' + this.orderPage, this.innerBox.centerX, this.innerBox.bottom + 10, 'center')
}

CpOrdersFile.prototype.startNewPage = function startNewPage (order) {
  this.writeOrderPageNumber(order)
  this.doc.addPage()
  var endY = this.writePageHeader(order)
  this.doc.setFontSize(10)
  return endY
}

module.exports = CpOrdersFile
