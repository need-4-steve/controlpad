const path = require('path')
const server = require('live-server')
const cla = require('./lib/cla.js')

const catch404 = cla.bool('-c', '--catch404')
const logLevel = cla.str('-l', '--logs')
const port = cla.str('-p', '--port')

const mimes = {
  '.html': 'text/html',
  '.js': 'application/javascript',
  '.css': 'text/css',
  '.ttf': 'font/ttf'
}

const params = {
  port,
  root: path.join(__dirname, '../public/www'),
  logLevel,
  middleware: [
    function (req, res, next) {
      const mime = mimes[path.extname(req.url)] || ''
      res.setHeader('Content-Type', mime)
      next()
    }
  ]
}

if (catch404) params.file = 'index.html'
server.start(params)
