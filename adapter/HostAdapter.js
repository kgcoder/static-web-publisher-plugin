/*
RW Reader

Copyright (c) 2025 Karen Grigorian
Code licensed under the MIT License.

This software implements document types defined by the Reader's Web project.

Reader's Web document types are licensed under CC BY-ND 4.0 and are maintained externally.

For the official list of document types and specifications, see:
https://github.com/kgcoder/readers-web-specs
*/

import { setFontSet } from '../reader/Fonts.js'
import g from '../reader/Globals.js'
import { getHdocJsonAndContentFromCurrentDocument, parseHtmlPageWithEmbeddedHDoc } from '../reader/parsers/EmbHDOCParser.js'
import { parseStaticContent } from "../reader/parsers/ParsingManager.js"
import { addListenersToContainer, applyAllSavedSettings, dispatchReaderReady, loadUIAndIcons } from "../reader/readerStartUp.js"

export default class HostAdapter {

    
    allowFontResizing = false
    allowDynamicThemeChange = false
    
    mainDocumentTitleSpanId = "CurrentDocumentTitleSpan-rwp"
    mainDocumentInfoButtonId = "CurrentDocumentInfoButton-rwp"
    currentDocumentCloseButtonId = "shouldn't-exist"
    
    shouldBlockCrossOriginCommentsRequests = true
    isPromotionalButtonSupported = true
    
    constructor(){
        this.initReader()
    }
    
    
    initReader(){
        let currentLocation

        document.addEventListener('DOMContentLoaded', async () => {
            const mainContainer = document.getElementById("AllDocumentsContainer");
            const mainContainerRect = mainContainer.getBoundingClientRect();
            g.adminBarHeight = mainContainerRect.top
        
            currentLocation = window.location.toString()
        
            if (currentLocation.includes('#')) {
                currentLocation = currentLocation.split('#')[0]
            }
        
            const container = document.getElementById("ui-root")
            
            addListenersToContainer(container)
        
        
            let isEmbeddedCdoc = false
            let isEmbeddedCondoc = false
            let contentString = ''
            try {
                const embeddedCdocScript = document.querySelector('#cdoc-source')
                const source = JSON.parse(embeddedCdocScript.textContent).source;
                if(source){
                    isEmbeddedCdoc = true
                    contentString = '<html><body>' + document.body.innerHTML + '</body></html>'
                }
            } catch {
                //do nothing
            }
        
            try {
                const embeddedCondocScript = document.querySelector('#condoc-source')
                const source = JSON.parse(embeddedCondocScript.textContent).source;
                if(source){
                    isEmbeddedCondoc = true
                    contentString = '<html><body>' + document.body.innerHTML + '</body></html>'
                }
            } catch {
                //do nothing
            }
        
        
            if(isEmbeddedCdoc || isEmbeddedCondoc){
                const {dataObject,error} = await parseStaticContent(contentString,currentLocation)
            
                if(dataObject ){
                    loadUIAndIcons()
                  
                    const fontSetId = (window.vcReaderData && window.vcReaderData.fontSet) ? window.vcReaderData.fontSet : 'default'
                    await setFontSet(fontSetId)
        
                    if(isEmbeddedCdoc){
                        await g.pdm.loadCollage(dataObject)
                    }else{
                        await g.pdm.showEmptyCondoc(dataObject)
                    }
                    dispatchReaderReady(currentLocation)
        
                    return
        
        
                }
            }
        
        
            const {hdocDataJSON, content} = getHdocJsonAndContentFromCurrentDocument()
        
            const dataObject = parseHtmlPageWithEmbeddedHDoc(currentLocation, content, hdocDataJSON)
        
            if(dataObject ){
                loadUIAndIcons()
                const fontSetId = (window.vcReaderData && window.vcReaderData.fontSet) ? window.vcReaderData.fontSet : 'default'
                await setFontSet(fontSetId)
            }
            await g.pdm.loadDocument(dataObject)
            dispatchReaderReady(currentLocation)

        });

    }


    getAssetsUrl(){
       return window.vcReaderData != null ? window.vcReaderData.assetsUrl : null
    }

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

    executeAfterOptionalDelay(func,delay = 500){
        setTimeout(() => {
            //testing if the extension has replaced the UI. This test is only needed in this plugin.
            const titleSpan = document.getElementById(this.mainDocumentTitleSpanId)
            if(titleSpan){
                func()
            }
        },delay)
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


    reloadPage(){}


    getCurrentThemeName() {
        const rootEl = document.getElementById('ui-root')
        if (!rootEl) return null

        for (const className of rootEl.classList) {
            if (className.startsWith('theme-')) return className.slice('theme-'.length)
        }

        return null
    }

    
    //UI additions ---------------


    getOpenCommentsInNewTabLabel(){
        return (window.vcReaderData != null && window.vcReaderData.openInNewTabCommentsLabel)
            ? window.vcReaderData.openInNewTabCommentsLabel
            : 'Open in a new tab to view comments'
    }




 
}
