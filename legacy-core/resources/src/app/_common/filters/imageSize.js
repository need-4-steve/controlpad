window.Vue.filter('imageSize', function (image, size) {
  let reg = /(?:\.([^.]+))?$/
  let ext = reg.exec(image)[1]
  image = image.replace(/\.[^/.]+$/, '')
  return image + '-' + size + '.' + ext
})
