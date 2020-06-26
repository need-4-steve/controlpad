const path = require('path')
const sass = require('sass')
const parse = require('./parse.js')
const fileTransforms = require('./fileTransforms.js')
const skip = module.exports.skip = require('./skipFiles.js')
require('colors')

module.exports.compileScripts = ({
  files = [],
  ordered = [],
  output = 'code.js'
} = {}) => {
  files = sort(files, ordered)
  const content = files
    .filter(x => !skip(x.relative))
    .map((file) => {
      const fileComment = `\n\n\n${'//'.repeat(50)}\n// ${file.relative}\n\n`
      return `${fileComment}${file.content}`
    })
    .join('\n')
  return {
    relative: output,
    content
  }
}

module.exports.wrapModule = (file) => {
  const relative = file.relative.substr(0, file.relative.indexOf(':'))
  if (fileTransforms[file.key]) {
    if (file.templateStr) {
      file.content += `\n;module.exports.template = ${JSON.stringify(file.templateStr)};`
    }
    file.content = fileTransforms[file.key](file)
    return file
  } else if (file.vue) {
    const meta = file.vue.componentName
      ? { name: file.vue.componentName, path: relative }
      : { path: relative }
    file.content = `
      sms.vComponent('${file.vue.moduleId}', ${JSON.stringify(meta)}, (require,exports,module)=>{
        ${file.content.toString()};
        module.exports.template = ${JSON.stringify(file.templateStr)};
      })
    `
  } else {
    const meta = { path: relative }
    file.content = `sms.addModule('${file.key}', ${JSON.stringify(meta)}, (require,exports,module)=>{\n${file.content.toString()}\n});`
  }
  return file
}

module.exports.compileStyles = ({
  files = [],
  ordered = [],
  output = 'styles.css'
} = {}) => {
  files = sort(files, ordered)
  const data = files
    .map(x => x.content.toString())
    .join('\n')
    .replace(/@import\s+(['"])[^\1]+?\1;?/g, ' ')
  let content
  try {
    content = sass.renderSync({ data }).css
  } catch (err) {
    if (typeof err === 'object' && err.formatted) {
      throw getSassErrorMessage(err, files)
    }
    throw err
  }
  return {
    relative: output,
    content
  }
}

const sort = (source, ordered) => {
  const assignOrder = (o) => {
    if (!o.order) {
      o.order = ordered.indexOf(o.relative)
      if (o.order < 0) o.order = ordered.length
    }
  }
  const compare = (a, b) => {
    assignOrder(a)
    assignOrder(b)
    if (a.order < b.order) return -1
    if (a.order > b.order) return 1
    return 0
  }
  return source.sort(compare)
}

const getSassErrorMessage = (err, files) => {
  let file = null
  let relLineNo = 0
  let count = 0
  for (let i = 0; i < files.length; i++) {
    const fileLineCount = files[i].content.toString().split('\n').length
    if (count + fileLineCount < err.line) {
      count += fileLineCount
      continue
    }
    relLineNo = err.line - count
    file = files[i]
    break
  }
  if (!file) return err.formatted
  const offset = file.lineOffset || 0
  const lines = file.content.toString().split('\n')
  const pre = lines.slice(relLineNo - 5, relLineNo).join('\n')
  const post = lines.slice(relLineNo + 1, relLineNo + 6).join('\n')
  return `Sass ${err.formatted.substr(0, err.formatted.indexOf('\n'))}\n`.green +
    `  at ${file.path}::${relLineNo + offset}:${err.column}\n\n`.green +
    pre.white +
    `\n${lines[relLineNo]}\n`.red +
    post.white +
    '\n\n'
}

const spinalToCamelRe = /(^|-|_)([a-z])/g
const fileNameToComponentName = str => {
  let name = path
    .basename(str, path.extname(str))
    .replace(spinalToCamelRe, (m, p1, p2) => p2.toUpperCase())
  if (name.charAt(0).toLowerCase() === 'c' && name.charAt(1).toLowerCase() === 'p') name = name.substr(2)
  return `Cp${name}`
}
module.exports.transformVueFile = (file) => {
  const parts = {}
  const content = (file.content || '').toString()
  try {
    parse(content).forEach((element) => {
      parts[element.name] = {
        key: element.attrs['id'] || file.relative,
        attrs: element.attrs,
        content: element.content,
        lineOffset: element.openingTag.line
      }
    })
  } catch (e) {
    throw new Error(e.message + `\n  File: ${file.relative}`)
  }

  const {
    script = { key: file.relative, attrs: {}, content: '' },
    style = { key: file.relative, attrs: {}, content: '' },
    template = { content: '' }
  } = parts

  const scope = typeof script.attrs.scope === 'string'
    ? script.attrs.scope
    : typeof script.attrs.scope === 'boolean'
      ? script.key.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase() + '-scoped'
      : false

  const templateStr = scope
    ? `<${scope}>${template.content.toString()}</${scope}>`
    : template.content.toString()

  const vue = { moduleId: script.key }
  if (script.key.indexOf('/') !== -1) {
    vue.componentName = fileNameToComponentName(script.key)
  }

  const scriptFile = {
    vue,
    action: file.action,
    key: script.key,
    path: file.path,
    relative: `${file.relative}:script`,
    content: script.content,
    templateStr
  }

  let styleFile = null
  if (style) {
    const content = scope
      ? `${scope}{${style.content.toString()}}`
      : style.content.toString()

    styleFile = {
      action: file.action,
      key: style.key,
      attrs: style.attrs,
      lang: style.lang,
      path: file.path,
      relative: `${file.relative}:style.${style.lang}`,
      lineOffset: style.lineOffset,
      content
    }
  }

  return { scriptFile, styleFile }
}
