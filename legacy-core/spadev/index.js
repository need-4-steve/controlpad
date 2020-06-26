const fs = require('fs')
const path = require('path')
const moment = require('moment')
const rev = require('rev-hash')
const Swig = require('./lib/swig.js')
const util = require('./lib/util.js')
const cla = require('./lib/cla.js')
const gitInfo = require('git-repo-info')()
require('colors') // adds color properties to strings automatically

const VALID_ENVS = ['prod', 'dev', 'local']

const watch = cla.bool('-w', '--watch')
const verbose = cla.bool('-v', '--verbose')
const env = cla.str('-e', '--env') || 'dev'
if (!VALID_ENVS.includes(env)) throw Error(`Invalid environment: '${env}'`)

gitInfo.abbreviatedSha = gitInfo.abbreviatedSha.substring(0, gitInfo.abbreviatedSha.length - 3)

const root = path.join(__dirname, '../resources')
const dest = path.join(__dirname, '../public/www')
const viewDest = path.join(__dirname, '../resources/views/spa')
const actions = {
  'put': 'PUT'.cyan,
  'purge': 'DEL'.red
}

const usingOverride = fs.existsSync(path.join(root, 'src/env.override.json'))

const log = (msg, stdout = false) => {
  let time = (new Date()).toISOString()
  time = time.substr(0, time.length - 5).replace('T', ' ')
  msg = (typeof msg === 'string' ? msg : JSON.stringify(msg))
  fs.appendFileSync('build.log', `\n${time} — ${msg.trim()}`)
  if (stdout) {
    process.stdout.write(msg)
  }
}

if (env === 'prod' && gitInfo.branch !== 'master') {
  log('  ' + 'WARNING: Building production code from non-master branch!'.red + '\n', true)
}
if (usingOverride && gitInfo.branch === 'master') {
  log('  ' + 'WARNING: Using env.override.json on master branch!'.red + '\n', true)
}

log('\n  ' + 'Running with the following parameters...'.green, true)
log('\n  ======================================================', true)
log(`\n    Environment: ${(env + '').cyan}`, true)
log(`\n     Git Branch: ${(gitInfo.branch + '').cyan}`, true)
log(`\n        Git Tag: ${(gitInfo.tag + '').cyan}`, true)
log(`\n     Git Commit: ${(gitInfo.sha + '').cyan}`, true)
log(`\n    Git Message: ${(gitInfo.commitMessage + '').cyan}\n`, true)

const swig = Swig({
  watch,
  root,
  clear: [path.join(dest, '**/*'), path.join(viewDest, '**/*')],
  channels: {
    appEnv: path.join(root, 'src/env.{json,override.json}'),
    appVue: [path.join(root, 'assets/js/**/*.vue'), path.join(root, 'src/app/**/*.vue')],
    appJsUnwrapped: path.join(root, 'assets/js/**/*.js'),
    appJs: path.join(root, 'src/app/**/*.js'),
    appJsWrap: path.join(root, 'assets/js/**/*.js'),
    appSass: path.join(root, '{assets,src/app}/**/*.scss'),

    index: {
      source: path.join(root, 'src/static/**/*.html'),
      root: path.join(root, 'src/static')
    },
    static: {
      source: path.join(root, 'src/static/**/!(*.html)'),
      root: path.join(root, 'src/static')
    },
    write: '::',
    writeBlade: '::'
  }
})

  .on('watch', (changes) => {
    if (verbose) {
      const time = `[${moment().format('YYYY-MM-DD HH:mm:ss')}]`.yellow
      const action = actions[changes.action]
      const filename = path.relative(root, changes.filename).magenta
      const msg = `  ${time} ${action} ${filename}`
      log('\n' + msg, true)
    }
  }).on('error', (err) => {
    log(err)
    console.error(err)
  }).on('index', (file) => {
    swig.cache('index', file)
    if (swig.ready) buildIndices()
  }).on('static', (file) => {
    swig.push('write', file)
  }).on('writeBlade', (file) => {
    const filepath = path.basename(file.relative, '.html')
    const filename = path.join(viewDest, filepath) + '.blade.php'
    return swig.write(filename, file.content)
  }).on('write', (file) => {
    const filename = path.join(dest, file.relative)
    log(`Writing out file: ${filename}...`)
    return swig.write(filename, file.content)
  }).on('ready', () => {
    log('\n\n  ' + 'Building files...'.green, true)
    return Promise.all([
      buildEnv('app'),
      buildSass('app'),
      buildJs('app'),
      buildIndices()
    ]).then(() => {
      log('done.'.green + '\n', true)
      if (watch) log('  ' + 'Watching files...'.green + '\n', true)
      else log('\n', true)
    })
  })

  .on('appEnv', (file) => {
    swig.cache('appEnv', file)
    if (swig.ready) buildEnv('app')
  }).on('appVue', (file) => {
    if (util.skip(file.relative)) return
    const vue = util.transformVueFile(file)
    swig.push('appJs', util.wrapModule(vue.scriptFile))
    if (vue.styleFile) swig.push('appSass', vue.styleFile)
  }).on('appJsUnwrapped', (file) => {
    swig.push('appJs', util.wrapModule(file))
  }).on('appJs', (file) => {
    swig.cache('appJs', file)
    if (swig.ready) buildJs('app', true)
  }).on('appJsWrap', (file) => {
    swig.cache('appJs', util.wrapModule(file))
    if (swig.ready) buildJs('app', true)
  }).on('appSass', (file) => {
    if (util.skip(file.relative)) return
    swig.cache('appSass', file)
    if (swig.ready) buildSass('app', true)
  })

const assets = {}
const escapeRegex = text => text.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&')
const buildIndices = () => {
  log('Building indices...')
  const files = swig.cache('index')
  const promises = files.map((file) => {
    let str = file.content.toString()
    const assetValues = Object.keys(assets).map((key) => { return { key, value: assets[key] } })
    const placeholders = (str.match(/{{{asset\|[^}]+}}}/g) || [])
    const hasEnoughAssets = !!placeholders.reduce((accu, curr) => {
      const assetExists = assetValues.some(x => curr === `{{{asset|${x.key}}}}`)
      if (!assetExists) return false
      return accu
    }, true)
    if (!hasEnoughAssets) {
      log(`...not enough assets (${Object.keys(assets).map(x => assets[x]).join(', ')}), skipping for now...`)
      return Promise.resolve()
    } else {
      log(`...building with these assets: ${Object.keys(assets).map(x => assets[x]).join(', ')}`)
    }
    assetValues.forEach((asset) => {
      const regex = new RegExp(escapeRegex(`{{{asset|${asset.key}}}}`))
      str = str.replace(regex, asset.value)
    })

    let matches = []
    let match

    const gitRegex = /{{{git\|([a-zA-Z]+)}}}/g
    while ((match = gitRegex.exec(str)) !== null) matches.push(match)
    matches.forEach(([placeholder, key]) => {
      const regex = new RegExp(escapeRegex(placeholder))
      str = str.replace(regex, gitInfo[key])
    })

    const envRegex = /{{{env:([a-z]+)\|([^}]*)}}}/g
    matches = []
    while ((match = envRegex.exec(str)) !== null) matches.push(match)
    matches.forEach(([placeholder, targetEnv, text]) => {
      const regex = new RegExp(escapeRegex(placeholder))
      str = str.replace(regex, env === targetEnv ? text : '')
    })

    str = str.replace(/<!--([\s\S]*?)-->\s*/g, match => '')
    const newFile = Object.assign({}, file, { content: str })
    if (/{{{asset\|[^}]+}}}/.test(newFile.content.toString())) {
      throw Error(`

ERROR: Failed to build SPA.
================================================================================
Unable to replace all asset placeholders in '${file.relative}'.

Assets: ${JSON.stringify(assets, null, 2)}

Content:

${newFile.content.toString()}
================================================================================`)
    }
    return Promise.all([
      swig.push('write', newFile),
      swig.push('writeBlade', newFile)
    ])
  })
  return Promise.all(promises)
}

const buildJs = (name, rebuildIndices) => {
  log('Building js...')
  const files = swig.cache(`${name}Js`)
  const outFile = util.compileScripts({
    files,
    output: `${name}.js`,
    ordered: [
      'src/app/_common/modules.js'
    ]
  })
  outFile.relative = `${name}.${rev(outFile.content.toString())}.js`
  assets[`${name}.js`] = outFile.relative
  log(`...adding asset: <'${name}.js', '${outFile.relative}'>`)
  log(`...assets now include: ${Object.keys(assets).map(x => assets[x]).join(', ')}`)
  if (rebuildIndices) buildIndices()
  return Promise.resolve()
    .then(x => swig.clear(path.join(dest, `${name}.*.js`), { force: true }))
    .then(x => swig.push('write', outFile))
}

const buildSass = (name, rebuildIndices) => {
  log('Building scss...')
  const files = swig.cache(`${name}Sass`)
  const outFile = util.compileStyles({
    files,
    output: `${name}.css`,
    ordered: [
      'assets/sass/var.scss',
      'assets/sass/main.scss',
      'assets/sass/styles.scss',
      'assets/sass/libraries/linear_icons.scss',
      'assets/sass/libraries/dropzone.scss',
      'assets/sass/libraries/slick-theme.scss',
      'assets/sass/libraries/quill.core.scss',
      'assets/sass/libraries/quill.snow.scss',
      'assets/sass/boss.scss'
    ]
  })
  outFile.relative = `${name}.${rev(outFile.content.toString())}.css`
  assets[`${name}.css`] = outFile.relative
  log(`...adding asset: <'${name}.css', '${outFile.relative}'>`)
  log(`...assets now include: ${Object.keys(assets).map(x => assets[x]).join(', ')}`)
  if (rebuildIndices) buildIndices()
  return Promise.resolve()
    .then(x => swig.clear(path.join(dest, `${name}.*.css`), { force: true }))
    .then(x => swig.push('write', outFile))
}

const buildEnv = (name) => {
  log('Building env...')
  let envKey = env
  const envFile = swig.cache(`${name}Env`).reduce((acc, curr) => {
    if (curr.relative.endsWith('env.override.json')) return curr
    else if (!acc.relative.endsWith('env.override.json')) return curr
    return acc
  }, { relative: '' })
  if (envFile.relative.endsWith('env.override.json')) {
    envKey = 'override'
  }
  const envSettings = envFile.content
    ? JSON.parse(envFile.content.toString())[envKey]
    : envFile.content
  const relative = path.join(path.dirname(envFile.relative), 'env.json')
  swig.push(`${name}Js`, {
    action: envFile.action,
    key: relative,
    path: relative,
    relative: relative,
    content: `sms.addModule('env', { aliases: 'assets/js/resources/apiconfig.json' }, (require,exports,module) => {\nmodule.exports = ${JSON.stringify(envSettings, null, 2)};\n});`
  })
}
