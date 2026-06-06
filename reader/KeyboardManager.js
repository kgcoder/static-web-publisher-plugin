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
import { setTheme } from './helpers.js'
import { getObjectFromLocalStorage } from './LocalStorageManager.js'


export const checkKey = async (e) => {

    if ((e.metaKey || e.ctrlKey) && e.key === '-') {
        e.preventDefault()
        g.pdm.updateFontSize(-1)   
    }
    if ((e.metaKey || e.ctrlKey) && e.key === '=') {
        e.preventDefault()
        g.pdm.updateFontSize(1)
        
            
    }

    if(e.code === 'Escape'){
        g.readingManager.processEscape()
    }

    if (e.code === 'KeyF') {
        if(e.altKey || e.ctrlKey || e.metaKey || e.shiftKey)return
        if(g.readingManager.rightNotesData.length){
            g.pdm.toggleFullScreen()
        }
    }
    if (e.key === '[' && e.ctrlKey) {

        const {value: saved} = await getObjectFromLocalStorage('theme') || "light";

        let next = 'light'
        if (saved === 'light') {
            next = 'dark'
        } else if (saved === 'dark') {
            next = 'sepia'
        } else if (saved === 'sepia') {
            next = 'light'
        }
        
        setTheme(next, true)

    }



    if (e.code === "KeyL") {
        g.readingManager.linkCreationButtonPressed()
    }

    if (e.keyCode == '8') {
        // delete (backspace)
        g.readingManager.deleteSelectedFlinks()
          
    }
}