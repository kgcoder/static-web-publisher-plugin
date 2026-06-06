/*
Visible Connections

Copyright (c) 2025 Karen Grigorian
Code licensed under the MIT License.

This software implements document types defined by the Default Web project.

Default Web document types are licensed under CC BY-ND 4.0 and are maintained externally.

For the official list of document types and specifications, see:
https://github.com/kgcoder/default-web
*/

import { getXMLFromHeaderInfo } from "../HeaderMethods.js"
import { escapeXml, getProtocolAndDomainFromUrl, showToastMessage } from "../helpers.js"


export async function parsePlainTextPage(content,cleanUrl) {
  

    const urlInfo = getProtocolAndDomainFromUrl(cleanUrl)
        if(!urlInfo){
            showToastMessage('Parsing error')
            return null
        }
    
    const {protocol, domain} = urlInfo
    

    let headerInfo = {}


    const titleMatch = content.match(/^Title: (.*?)$/im)
    if (titleMatch) {
        headerInfo.h1Text = titleMatch[1]
    }


    const finalUrl = `${cleanUrl}#pr=text`


    let headerString = getXMLFromHeaderInfo(headerInfo)

    headerString = headerString ? `\n\n${headerString}\n\n` : '\n\n'

    
    const xmlString = `<hdoc>\n\n<metadata>\n<title>${escapeXml(headerInfo.h1Text ?? '')}</title>\n</metadata>\n\n<panels>\n<top>\n<site-name href="${protocol}://${domain}">${domain}</site-name>\n</top>\n</panels>${headerString}<content type="text">${content}</content>\n\n</hdoc>`

    const dataObject = {html:content,headerInfo,xmlString,connectedDocsData:[],type:'text',url:finalUrl,docSubtype:3,docType:'h',isPlainText:true}

    return dataObject
    
   
}


