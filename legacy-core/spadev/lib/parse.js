var parse5 = require('parse5')

module.exports = (str) => {
  const parser = new parse5.SAXParser({ locationInfo: true })
  const elements = []
  let current = null
  parser.on('startTag', (name, attrs, selfClosing, location) => {
    const tag = { name, attrs, openingTag: location, count: 0 }
    if (current) {
      if (current.name === tag.name) current.count++
      return
    }
    current = tag
  })
  parser.on('endTag', (name, location) => {
    const tag = { name, location }
    if (!current || current.name !== tag.name) return
    if (current.count > 0) current.count--
    else {
      current.closingTag = tag.location
      elements.push(current)
      current = null
    }
  })
  parser.end(str)
  return elements.map((element) => {
    delete element.count
    const attrs = {}
    element.attrs.forEach((attr) => {
      attrs[attr.name] = attr.value !== '' ? attr.value : true
    })
    element.attrs = attrs
    element.content = str.substring(element.openingTag.endOffset, element.closingTag.startOffset)
    return element
  })
}
