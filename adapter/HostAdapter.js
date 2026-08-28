/*
RW Reader

Copyright (c) 2025 Karen Grigorian
Code licensed under the MIT License.

This software implements document types defined by the Reader's Web project.

Reader's Web document types are licensed under CC BY-ND 4.0 and are maintained externally.

For the official list of document types and specifications, see:
https://github.com/kgcoder/readers-web-specs
*/

export default class HostAdapter {

    async fetchWebPage(url, options) {
        options = options || {}

        try {
            const proxyUrl = window.vcReaderData != null ? window.vcReaderData.proxyUrl : undefined
            if (!proxyUrl) throw new Error('Proxy URL not configured')

            const currentPageUrl = options.currentPageUrl
            const params = new URLSearchParams({
                source_url: currentPageUrl.split('#')[0],
                target_url: url,
            })
            if (options.isForCondoc) {
                params.set('for_condoc', '1')
            }

            const result = await fetch(proxyUrl + '?' + params)
            if (!result.ok) throw new Error('Proxy error ' + result.status)
            const text = await result.text()
            return {text, error: ''}
        } catch (e) {
            return {error: e, text: ''}
        }
    }

    // This plugin does not persist per-visitor settings today — theme/fontSet are
    // configured site-wide by the admin and server-rendered via window.vcReaderData.
    // If that changes, implement these with window.localStorage (no message relay
    // needed here, unlike the browser extension).
    getSetting(key) {
        return Promise.resolve(undefined)
    }

    saveSetting(key, value) {
    }
}
