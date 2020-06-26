const concat = require('concat-files')
const glob = require('glob')
const sass = require('sass')
const fs = require('fs-extra')

// legacy angular build

function makeVersion () {
  return Math.random().toString(36).substring(7)
}

function buildJS () {
  glob('./public/angular/*.js', function (error, files) {
    if (error) throw error
    if (files.length > 0) {
      fs.unlink(files[0], function (error) {
        if (error) throw error
        buildJS()
      })
    } else {
      glob('{resources/assets/js/directives/*.js,resources/assets/js/services/*.js,resources/assets/js/controllers/*.js}', function (error, files) {
        if (error) throw error
        let concatFiles = [
          'resources/assets/js/angApp.js',
          'resources/assets/js/helpers.js',
          'resources/assets/js/slick.js'
        ]
        concatFiles = concatFiles.concat(files)
        concat(concatFiles, 'public/angular/angApp-' + makeVersion() + '.js', function (error) {
          if (error) throw error
          console.log('Angular App built.')
        })
      })
    }
  })
}

function buildSass () {
  glob('./public/angular/*.css', function (error, files) {
    if (error) throw error
    if (files.length > 0) {
      fs.unlink(files[0], function (error) {
        if (error) throw error
        buildSass()
      })
    } else {
      let content = sass.renderSync({ file: 'resources/assets/sass/store.scss' })
      fs.outputFile('./public/angular/store-' + makeVersion() + '.css', content.css.toString(), (error) => {
        if (error) throw error
        console.log('Sass has been compiled.')
      })
    }
  })
}

buildSass()
buildJS()
