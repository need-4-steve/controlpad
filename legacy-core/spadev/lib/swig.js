const fs = require('fs')
const path = require('path')
const find = require('multi-glob').glob // require('glob')
const chokidar = require('chokidar')
const mkdirp = require('mkdirp')
const del = require('del')

const PUT_ACTION = 'put'
const PURGE_ACTION = 'purge'
const FIND_OPTS = { nodir: true }
const WATCH_OPTS = { ignoreInitial: true }
// const TAGS = ['template', 'script', 'style']
// const TAG_TO_EXT = {
//   'template': '.htm',
//   'text/html': '.htm',
//   'script': '.js',
//   'text/js': '.js',
//   'text/javascript': '.js',
//   'application/javascript': '.js',
//   'text/json': '.json',
//   'application/json': '.json',
//   'style': '.css',
//   'text/sass': '.scss',
//   'text/scss': '.scss',
//   'text/less': '.less'
// }

// const FILE_MODEL = {
//   action: PUT_ACTION || PURGE_ACTION,
//   key: String,
//   path: String,
//   relative: String,
//   content: Buffer || String
// }

/* const Swig = */module.exports = ({
  cwd = process.cwd(),
  root = '',
  clear = false,
  watch = false,
  channels = {},
  channelsArr = Object.keys(channels).map((key) => {
    if (key === 'ready' || key === 'error') throw Error(`SWIG ERROR (constructor): Invalid channel name: ${key}.`)
    if (typeof channels[key] === 'string' || Array.isArray(channels[key])) {
      channels[key] = { source: channels[key] }
    }
    channels[key].name = key
    channels[key].root = channels[key].root || root
    return channels[key]
  })
} = {}) => {
  const bus = new EventBus()
  const onError = bus.trigger.bind(bus, 'error')
  const onReady = () => {
    Promise
      .resolve()
      .then(bus.trigger.bind(bus, 'ready'))
      .catch(onError)
  }
  const cache = {}
  const swig = {
    PUT: PUT_ACTION,
    PURGE: PURGE_ACTION,
    ready: false,
    on (keys, file) {
      bus.on(keys, file)
      return swig
    },
    off (keys, listener) {
      bus.off(keys, listener)
      return swig
    },
    push (channelName, files) {
      if (!Array.isArray(files)) files = [files]
      const promises = files
        .map(file => bus.trigger(channelName, file))
        .filter(p => typeof p === 'object' && typeof p.then === 'function')
      return Promise.all(promises)
    },
    cache (key, file) {
      if (!cache[key]) cache[key] = {}
      if (file === void 0) return Object.keys(cache[key]).map(x => cache[key][x])
      if (file.action === PUT_ACTION) {
        cache[key][file.key] = file
      } else if (file.action === PURGE_ACTION) {
        delete cache[key][file.key]
      }
    },
    write,
    clear: del
  }
  Promise.resolve().then(() => {
    if (clear) return del(clear, { force: true })
  }).then(() => {
    return Promise.all(
      channelsArr.map((channel) => {
        return new Promise((resolve, reject) => {
          find(channel.source, FIND_OPTS, (err, filenames) => {
            if (err) reject(err)
            Promise
              .all(filenames.map((filename) => {
                return readFile(channel, filename).then((file) => {
                  bus.trigger(channel.name, file)
                  return file
                })
              }))
              .then(resolve)
              .catch(reject)
          })
        })
      })
    )
  }).then(() => {
    bus.afterPending().then(() => {
      swig.ready = true
      onReady()
    })
    if (watch) {
      channelsArr.forEach((channel) => {
        const watcher = (action, filename) => {
          bus.trigger('watch', { action, filename })
          getActionFile(channel, action, filename)
            .then(bus.trigger.bind(bus, channel.name))
            .then(x => bus.trigger('postWatch', action))
            .catch(onError)
        }
        chokidar.watch(channel.source, WATCH_OPTS)
          .on('add', watcher.bind(null, PUT_ACTION))
          .on('change', watcher.bind(null, PUT_ACTION))
          .on('unlink', watcher.bind(null, PURGE_ACTION))
          .on('error', onError)
      })
    }
  }).catch(onError)
  return swig
}

const getActionFile = (channel, action, filename) => {
  switch (action) {
    case PUT_ACTION: return readFile(channel, filename)
    case PURGE_ACTION: return Promise.resolve().then(() => {
      const relative = channel.root ? path.relative(channel.root, filename) : void 0
      return {
        action: PURGE_ACTION,
        key: relative,
        path: filename,
        relative,
        content: null
      }
    })
    default:
      return Promise.reject(new Error(`SWIG ERROR (watcher): Invalid action: ${action}.`))
  }
}

const readFile = (channel, filename) => {
  return new Promise((resolve, reject) => {
    filename = filename.replace(/\\/g, '/')
    fs.readFile(filename, (err, data) => {
      if (err) return reject(err)
      const relative = channel.root ? path.relative(channel.root, filename) : void 0
      resolve({
        action: PUT_ACTION,
        key: relative,
        path: filename,
        relative,
        content: data
      })
    })
  })
}

const write = (filename, content) => {
  return new Promise((resolve, reject) => {
    mkdirp(path.dirname(filename), (err) => {
      if (err) return reject(err)
      fs.writeFile(filename, content, (err) => {
        if (err) return reject(err)
        resolve()
      })
    })
  })
}

// const parse = (file) => {
//   const files = []
//   const xml = file.content.toString()
//   const $ = cheerio.load(xml)
//   TAGS.forEach((tag)=>{
//     $(tag).each((index, element)=>{
//       element = $(element)
//       const key = element.attr('id') || `${file.path}:${tag}-${index}`
//       const mime = element.attr('type') || tag
//       const ext = TAG_TO_EXT[mime]
//       if(!ext) console.log('>>>', mime, tag)
//       files.push({
//         action: PUT_ACTION,
//         key,
//         path: file.path,
//         relative: `${file.relative}${ext}`,
//         content: element.html(),
//         attrs: element.get(0).attribs
//       })
//     })
//   })
//   return files
// }

function EventBus () {
  this.repo = {}
  this.count = 0
  this.promises = []
}
EventBus.prototype.afterPending = function () {
  return new Promise((resolve, reject) => {
    process.nextTick(() => {
      if (this.count !== 0) throw new Error(`How? Just How?`)
      return Promise.all(this.promises).then(resolve).catch(reject)
    })
  })
}
EventBus.prototype.on = function (keys, fn) {
  if (typeof fn !== 'function') throw new Error(`EVENTBUS ERROR (on): Invalid parameter. Second parameter must be a function.`)
  if (typeof keys !== 'string' && !Array.isArray(keys)) throw new Error(`EVENTBUS ERROR (on): Invalid parameter. First parameter must be string or array of strings.`)
  if (typeof keys === 'string') keys = keys.split(/\s/).filter(Boolean)
  keys.forEach((key) => {
    if (!this.repo[key]) this.repo[key] = []
    if (!this.repo[key].includes(fn)) this.repo[key].push(fn)
  })
}
EventBus.prototype.off = function (keys, fn) {
  if (typeof fn !== 'function') throw new Error(`EVENTBUS ERROR (off): Invalid parameter. Second parameter must be a function.`)
  if (typeof keys !== 'string' && !Array.isArray(keys)) throw new Error(`EVENTBUS ERROR (off): Invalid parameter. First parameter must be string or array of strings.`)
  if (typeof keys === 'string') keys = keys.split(/\s/).filter(Boolean)
  keys.forEach((key) => {
    if (!this.repo[key]) return
    if (!this.repo[key].includes(fn)) return
    const index = this.repo[key].indexOf(fn)
    if (index !== -1) this.repo[key].splice(index, 1)
  })
}
EventBus.prototype.trigger = function (keys, data) {
  if (typeof keys !== 'string' && !Array.isArray(keys)) throw new Error(`EVENTBUS ERROR (trigger): Invalid parameter. First parameter must be string or array of strings.`)
  if (typeof keys === 'string') keys = keys.split(/\s/).filter(Boolean)
  const localPromises = []
  keys.forEach((key) => {
    if (!this.repo[key]) return
    this.repo[key].forEach((fn) => {
      this.count++
      const p = fn(data)
      if (typeof p === 'object' && typeof p.then === 'function') {
        this.promises.push(p)
        localPromises.push(p)
        p.then(() => {
          this.promises.splice(this.promises.indexOf(p), 1)
          this.count--
        }).catch((err) => {
          this.promises.splice(this.promises.indexOf(p), 1)
          this.count--
          this.trigger('error', err)
        })
      } else {
        this.count--
      }
    })
  })
  return Promise.all(localPromises)
}

// Usage:
//
// Swig({
//   root: '...',
//   channels: {
//     js: {
//       source: '...',
//       root: '...',
//       first: ['...'],
//       last: ['...']
//     }
//   }
// }).on('js', (file)=>{
//
// }).on('sass', (file)=>{
//
// }).on('static', (file)=>{
//
// })
