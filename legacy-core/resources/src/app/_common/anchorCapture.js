window.sms.runModule('anchorCapture', (require, exports, module) => {
  const regex = /attachment;\s*filename=(['"])([^\1]*)\1/i
  const captureAnchorDownloads = (e) => {
    e.preventDefault()
    const url = e.target.href
    const download = e.target.download
    const anchor = document.createElement('a')
    const token = (window.localStorage.getItem('jwt_token') || '')
    const headers = new window.Headers()
    const options = { headers }
    if (url.indexOf(window.location.origin) === 0) {
      options.credentials = 'include'
      headers.append('Authorization', `Bearer ${token}`)
    }
    let filename = 'filename.ext'
    window.fetch(url, options)
      .then(res => {
        filename = ((res.headers.get('Content-Disposition') || '').match(regex) || [])[2]
        return res.blob()
      })
        .then((blob) => {
            var iOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
            if (iOS) {
                let url = window.URL.createObjectURL(blob);
                window.open(url, filename, "_blank");
            }
            else {
                const blobUrl = window.URL.createObjectURL(blob)
                anchor.href = blobUrl
                anchor.download = download || filename
                anchor.target = '_self'
                anchor.style.display = 'none'
                window.document.body.append(anchor)
                anchor.click()
                window.URL.revokeObjectURL(blobUrl)
                anchor.remove()
            }
      })
  }

  const captureAnchorRoutes = function (e) {
    const url = this.href
    if (url.indexOf(origin) === 0 && url.indexOf(apiOrigin) !== 0 && this.target !== '_blank') {
      e.preventDefault()
      const route = url.substr(origin.length)
      window.VueInstance.$router.push(route)
    }
  }

  const origin = window.location.origin
  const apiOrigin = window.location.origin + '/api/'

  new window.MutationObserver(() => {
    document
      .querySelectorAll('a[download]')
      .forEach((item) => {
        if (item.$__hasLinkListener) return
        item.$__hasLinkListener = true
        item.addEventListener('click', captureAnchorDownloads)
      })
    document
      .querySelectorAll('a:not([href^="/print-"]):not([no-vue-route])')
      .forEach((item) => {
        if (item.$__hasLinkListener) return
        item.$__hasLinkListener = true
        item.addEventListener('click', captureAnchorRoutes)
      })
  }).observe(document, {
    subtree: true,
    attributes: true,
    childList: true
  })
})
