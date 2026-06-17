/*
Visible Connections

Copyright (c) 2025 Karen Grigorian
Code licensed under the MIT License.

This software implements document types defined by the Default Web project.

Default Web document types are licensed under CC BY-ND 4.0 and are maintained externally.

For the official list of document types and specifications, see:
https://github.com/kgcoder/default-web
*/

import g from './Globals.js'


const currentRequests = new Set()
const responseCache = new Map()


export function fetchWebPage(url,isForCondoc = false) {
  if (!g.readingManager.mainDocData) return

  if (responseCache.has(url)) return Promise.resolve(responseCache.get(url))
  if (currentRequests.has(url)) return
  currentRequests.add(url)

  return new Promise(async (resolve) => {
    const currentPageUrl = g.readingManager.mainDocData.url
    const currentPageHostname = new URL(currentPageUrl).hostname

    try {
      const requestedPageHostname = new URL(url).hostname

      if (requestedPageHostname === currentPageHostname) {
        try {
          const result = await fetch(url)
          const text = await result.text()
          const response = {text, error: ''}
          currentRequests.delete(url)
          responseCache.set(url, response)
          resolve(response)
        } catch (e) {
          currentRequests.delete(url)
          resolve({error: e, text: ''})
        }
      } else {
        try {
          const proxyUrl = window.vcReaderData?.proxyUrl
          if (!proxyUrl) throw new Error('Proxy URL not configured')
          const params = new URLSearchParams({
            source_url: currentPageUrl.split('#')[0],
            target_url: url,
            ...(isForCondoc ? { for_condoc: '1' } : {}),
          })
          const result = await fetch(`${proxyUrl}?${params}`)
          if (!result.ok) throw new Error(`Proxy error ${result.status}`)
          const text = await result.text()
          const response = {text, error: ''}
          currentRequests.delete(url)
          responseCache.set(url, response)
          resolve(response)
        } catch (e) {
          currentRequests.delete(url)
          resolve({error: e, text: ''})
        }
      }
    } catch (e) {
      resolve({error: e, text: 'Something is wrong with the URL'})
    }
  })
}

export function invalidateCacheForUrl(url) {
  for (const key of responseCache.keys()) {
    if (key.startsWith(url)) {
      responseCache.delete(key)
    }
  }
}
