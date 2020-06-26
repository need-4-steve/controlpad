/* global Vue */
const sms = (() => {
  const seen = []
  const _modules = {}
  const _cache = {}
  let _loaders = {}
  let _resolver = x => x

  const load = (source) => {
    const module = _modules[source]
    if (!module) throw Error(`sms error: module not found: '${source}'.`)
    if (seen.indexOf(module.__key) > -1) {
      const depPath = seen.join(' -> ') + ' -> ' + module.__key
      throw Error(`sms error: circular dependency detected: ${depPath}`)
    }
    seen.push(module.__key)
    _loaders[module.__type](module)
    if (typeof module.__transform === 'function') {
      module.exports = module.__transform(module.exports, module)
    }
    seen.pop()
    _cache[module.__key] = module
    _cache[source] = module
  }

  const require = function (source) {
    source = _resolver(source, this)
    if (!_cache[source]) load(source)
    return _cache[source].exports
  }

  const $create = (module) => {
    if (typeof module.__key !== 'string') throw new Error(`sms error: invalid module key`)
    if (typeof module.__fn !== 'function') throw new Error(`sms error: invalid module function: '${typeof module.__fn}'.\n\nconst module = ${JSON.stringify(module, null, 2)}\n`)
    if (Object.keys(_loaders).indexOf(module.__type) === -1) throw new Error(`sms error: invalid module type: '${module.__type}'. Are you missing a module loader for this type?`)
    if (_modules[module.__key]) throw new Error(`sms error: module with key '${module.__key}' has already been added.`)
    module.__require = require.bind(module)
    module.__run = x => require.call({}, module.__key)
    _modules[module.__key] = module
    if (module.__aliases) module.__aliases.forEach(alias => (_modules[alias] = module))
  }

  const $modules = asHash => asHash ? _modules : Object.keys(_modules).map(key => _modules[key])
  const $cache = asHash => asHash ? _cache : Object.keys(_cache).map(key => _cache[key])
  const $config = ({
    resolver = x => x,
    loaders = {},
    plugins = {}
  } = {}) => {
    _resolver = resolver
    _loaders = loaders
    Object.keys(plugins).map((key) => {
      if (key[0] === '$') throw Error(`sms error: plugins cannot start with '$'.`)
      instance[key] = plugins[key].bind(null, $create)
    })
  }

  const instance = { $config, $modules, $cache }

  window.sms = instance

  return instance
})();

(() => {
  const fnParamsRegex = /^\s*(?:(?:function\s*(?:\s[_$a-zA-Z][_$a-zA-Z0-9]+)?)?\(((?:[_$a-zA-Z][_$a-zA-Z0-9]+(\s*,\s*)?)*)\s*\))|(?:([_$a-zA-Z][_$a-zA-Z0-9]+)\s*=>)/
  const splitParamsRegex = /[,\s*]/
  const routes = {}

  const add = ($create, __key, __meta, __fn, flags, __transform) => {
    if (__fn === undefined) {
      __fn = __meta
      __meta = {}
    }
    const module = {
      __key,
      __meta,
      __fn,
      __type: 'sms',
      __transform
    }
    if (__meta && __meta.aliases) module.__aliases = __meta.aliases
    if (module.__aliases && typeof module.__aliases === 'string') {
      module.__aliases = module.__aliases.split(/[,\s]/).filter(Boolean)
    }
    if (!Array.isArray(flags)) flags = [flags]
    flags.forEach(flag => (module[flag] = true))
    module.__relative = (__meta || {}).path || __key
    $create(module)
    return module
  }

  sms.$config({
    resolver (relative, parent) {
      if (relative.indexOf('.') !== 0) return relative
      const rps = relative.split('/')
      const cps = parent.__relative.split('/')
      cps.pop()
      for (let i in rps) {
        const part = rps[i]
        if (part === '..') cps.pop()
        else if (part === '.') continue
        else cps.push(part)
      }
      return cps.join('/')
    },
    loaders: {
      cjs (module) {
        module.exports = {}
        module.__fn(module.__require, module.exports, module)
      },
      sms (module) {
        module.exports = {}
        let params = []
        if (typeof module.__fn === 'function') {
          const code = module.__fn.toString()
          let match = code.match(fnParamsRegex) || []
          const paramsStr = match[1] || match[3] || ''
          params = paramsStr.split(splitParamsRegex).filter(Boolean)
        } else if (Array.isArray(module.__fn)) {
          params = module.__fn.splice(0, module.__fn.length - 1)
          module.__fn = module.__fn.pop()
        }
        if (typeof module.__fn !== 'function') throw new Error(`sms error: Invalid module function. Found '${typeof module.__fn}'.`)
        const deps = params.map((param) => {
          switch (param) {
            case 'require': return module.__require
            case 'module': return module
            case 'exports': return module.exports
            default: return module.__require(param)
          }
        })
        const result = module.__fn(...deps)
        if (result !== undefined) module.exports = result
      }
    },
    plugins: {
      addNodeStyleModule ($create, __key, __fn) {
        $create({ __key, __fn, __type: 'cjs' })
      },
      addModule ($create, __key, __meta, __fn) {
        if (__fn === undefined) {
          __fn = __meta
          __meta = {}
        }
        const module = { __key, __meta, __fn, __type: 'sms' }
        if (__meta && __meta.aliases) module.__aliases = __meta.aliases
        if (module.__aliases && typeof module.__aliases === 'string') {
          module.__aliases = module.__aliases.split(/[,\s]/).filter(Boolean)
        }
        module.__relative = __meta.path || __key
        $create(module)
      },
      runModule ($create, __key, __meta, __fn) {
        if (__fn === undefined) {
          __fn = __meta
          __meta = {}
        }
        const module = { __key, __meta, __fn, __type: 'sms' }
        if (__meta && __meta.aliases) module.__aliases = __meta.aliases
        if (module.aliases && typeof module.__aliases === 'string') {
          module.__aliases = module.__aliases.split(/[,\s]/).filter(Boolean)
        }
        module.__relative = __meta.path || __key
        module.__autorun = true
        $create(module)
      },

      vInstance ($create, __fn) {
        /* const module = */add($create, '::Vue::', {}, __fn, ['__vue', '__vue_instance'])
      },
      vDirective ($create, __key, __meta, __fn) {
        add($create, __key, __meta, __fn, ['__vue', '__vue_directive'], (directive) => {
          Vue.directive(__key, directive)
          return directive
        })
      },
      // vRoute ($create, __key, __meta, __fn) {
      //   if (!__meta || typeof __meta !== 'object' || !__meta.route || typeof __meta.route !== 'object') {
      //     throw new Error(`sms error: invalid route object: ${JSON.stringify(__meta, null, 2)}`)
      //   }
      //   const flags = ['__vue', '__vue_component', '__vue_route']
      //   const route = __meta.route
      //   add($create, __key, __meta, __fn, flags, (component) => {
      //     route.path.split(/\s+/).filter(Boolean).forEach((path) => {
      //       route.name = route.name || __key
      //       Vue.component(route.name, component)
      //       route.component = component
      //       routes[route.name] = Object.assign({}, route, { path })
      //       // routes.push(Object.assign({}, route, { path }))
      //     })
      //     return component
      //   })
      // },
      vComponent ($create, __key, __meta, __fn) {
        const flags = ['__vue', '__vue_component', '__vue_nonroute']
        const name = (__meta && __meta.name) || __key
        add($create, __key, __meta, __fn, flags, (component) => {
          let routing = __meta.routing || component.routing || null
          if (routing) {
            delete component.routing
            if (typeof routing === 'string') routing = [{ path: routing }]
            if (!Array.isArray(routing)) routing = [routing]
            routing.forEach((route) => {
              if (!route.name) route.name = name
              route.component = component
              routes[route.name] = route
            })
          }
          Vue.component(name, component)
          return component
        })
      },
      vFilter ($create, __key, __meta, __fn) {
        add($create, __key, __meta, __fn, ['__vue', '__vue_filter'], (filter) => {
          Vue.filter(__key, filter)
          return filter
        })
      },
      vUse ($create, __key, __meta, __fn) {
        add($create, __key, __meta, __fn, ['__vue', '__vue_use'], (plugin) => {
          Vue.use(__key, plugin)
          return plugin
        })
      },
      vMixin ($create, __key, __meta, __fn) {
        add($create, __key, __meta, __fn, ['__vue', '__vue_mixin'])
      }
    }
  })

  sms.addModule('vRoutes', x => routes)
})()

// sms.add('one', function(require, exports, module){
//   console.log('enter one')
//   exports.name = 'one'
//   exports.foo = () => {}
//   console.log('exit one')
// })
//
// sms.add('two', ['require', 'exports', 'module', function(r, e, m){
//   console.log('enter two')
//   const three = r('three')
//   console.dir(three)
//   console.log('exit two')
// }])
//
// sms.add('three', ()=>{
//   console.log('enter three')
//   console.log('exit three')
//   return {
//     name: 'three',
//     foo(){}
//   }
// })
//
// sms.add('four', { run: true }, (one, two)=>{
//   console.log('enter four')
//   console.dir(one)
//   console.log('exit four')
// })
//
// sms.$modules().filter(module => module.__autorun).forEach(module => module.__run())

document.querySelectorAll('script[global]').forEach((element) => {
  const globalRef = element.getAttribute('global')
  const aliases = (element.getAttribute('aliases') || globalRef).split('|').filter(Boolean)
  aliases.forEach((alias) => {
    sms.addModule(alias, alias, (require, exports, module) => {
      module.exports = window[globalRef]
    })
  })
})

document.addEventListener('DOMContentLoaded', () => {
  sms.$modules().filter(m => m.__vue_route && m.__run())
  sms.$modules().filter(m => m.__vue_nonroute && m.__run())
  sms.$modules().filter(m => m.__vue_filter && m.__run())
  sms.$modules().filter(m => m.__vue_use && m.__run())
  sms.$modules().filter(m => m.__vue_instance && m.__run())
  sms.$modules().filter(m => m.__autorun && m.__run())
})
