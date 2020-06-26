const Request = require('../resources/requestHandler.js')

const Auth = require('auth')

module.exports = {
  index: function (params) {
    return Request.get('/api/v1/media', params)
  },
  indexWithFilters: function (params) {
    return Request.get('/api/v1/media/filter', params)
  },
  update: function (id, params) {
    return Request.patch('/api/v1/media/' + id, params)
  },
  process: function (params, options) {
    return Request.post('/api/v1/media/process', params, options)
  },
  delete: function (id) {
    return Request.delete('/api/v1/media/' + id)
  },
  mediaTypeCount: function (param) {
    return Request.get('/api/v1/media/count', param)
  },
  dropzoneConfig: function (fileTypes = '') {
    const headers = Object.assign({}, Auth.getAuthHeaders())
    return {
      maxFileSize: 5000,
      maxFiles: 1,
      acceptedFiles: '.jpg, .mp4, .jpeg, .JPEG, .PNG, image/JPG, .png, .gif, .GIF, .doc, .pdf, .docx, .odt, .rtf, .txt, .epub, .xlsx, .ods, .csv, .tsv, .pptx, .odp, .svg, ' + fileTypes,
      dictDefaultMessage: 'Click or drop your file here to upload',
      dictMaxFilesExceeded: 'You may only upload one file at a time.',
      addRemoveLinks: true,
      dictRemoveFile: 'Remove File',
      headers
    }
  }
}
