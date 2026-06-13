/*
Visible Connections

Copyright (c) 2025 Karen Grigorian
Code licensed under the MIT License.

This software implements document types defined by the Default Web project.

Default Web document types are licensed under CC BY-ND 4.0 and are maintained externally.

For the official list of document types and specifications, see:
https://github.com/kgcoder/default-web
*/

import g from "./Globals.js"
import { setTheme, showToastMessage } from "./helpers.js";
import IconsInfo from "./Icons.js";
import { parseStaticContent } from "./parsers/ParsingManager.js";
import { checkKey } from "./KeyboardManager.js";
import { getObjectFromLocalStorage } from "./LocalStorageManager.js";
import { getHdocJsonAndContentFromCurrentDocument, parseHtmlPageWithEmbeddedHDoc } from "./parsers/EmbHDOCParser.js";
import { parseCDOC } from "./parsers/CDOCParser.js";
import { parseCondoc } from "./parsers/CondocParser.js";

let mainDocData
let currentLocation

document.addEventListener('DOMContentLoaded', onLoad);

async function onLoad() {
    console.log('page loaded')

    const mainContainer = document.getElementById("AllDocumentsContainer");
    const mainContainerRect = mainContainer.getBoundingClientRect();
    g.adminBarHeight = mainContainerRect.top

    console.log('g.adminBarHeight',g.adminBarHeight)
    currentLocation = window.location.toString()

    if (currentLocation.includes('#')) {
        currentLocation = currentLocation.split('#')[0]
    }


    const container = mainContainer.parentElement
    //snapping

    container.addEventListener('scroll',() => {
        if (g.pdm.isFlinksListOpen) {
            g.pdm.toggleFlinksList()
        }
    })

    container.addEventListener('scrollend', () => {
        const halfway = container.scrollWidth / 4;

        if (container.scrollLeft > halfway) {
            container.scrollTo({
            left: container.scrollWidth,
            behavior: 'smooth'
            });
        } else {
            container.scrollTo({
            left: 0,
            behavior: 'smooth'
            });
        }
    });


    const mainDiv = document.getElementById('CurrentDocumentMainDiv');
    if (mainDiv) {
        const cdocPre = mainDiv.querySelector('pre.cdoc-source')
        if(cdocPre){
            const source = cdocPre.textContent
            console.log('source:',source)

            const dataObject = await parseCDOC(currentLocation,source)
            
            if(dataObject ){
                dataObject.docSubtype = 8
                console.log('dataObject',dataObject)

                loadUIAndIcons()

                await g.pdm.loadCollage(dataObject)

                return;
            }

        }
        const condocPre = mainDiv.querySelector('pre.condoc-source')
        if(condocPre){
            const source = condocPre.textContent
            console.log('condoc source:',source)

            const dataObject = await parseCondoc(currentLocation,source)
            console.log('condoc dataObject',dataObject)
            if(dataObject ){
                dataObject.docSubtype = 9
                console.log('dataObject',dataObject)

                loadUIAndIcons()

                await g.pdm.showEmptyCondoc(dataObject)

                return;
            }

        }
    }

    const {hdocDataJSON, content} = getHdocJsonAndContentFromCurrentDocument()

    const dataObject = parseHtmlPageWithEmbeddedHDoc(currentLocation, content, hdocDataJSON)

    if(dataObject ){
        loadUIAndIcons()
    }
    console.log({dataObject})
    await g.pdm.loadDocument(dataObject)


 

}





function loadUIAndIcons() {

    g.flinksCanvas = document.getElementById('flinks-canvas')
    g.flinksCtx = g.flinksCanvas.getContext("2d")
    g.iconsInfo = new IconsInfo()

    g.iconsInfo.loadAllIcons()
    g.pdm.loadUI()


    document.onkeydown = checkKey

    useSavedTheme()

}


async function useSavedTheme() {
    let { value: saved } = await getObjectFromLocalStorage('theme')
    if (!saved) {
        saved = "light"
    }
    setTheme(saved)
    g.currentTheme = saved
}



