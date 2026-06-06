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


export function fetchWebPage(url) {
  if(!g.readingManager.mainDocData)return

  if (currentRequests.has(url)) return
  currentRequests.add(url)


    
    
    return new Promise(async (resolve, reject) => {
        
        
        
        const currentPageUrl = g.readingManager.mainDocData.url
        console.log('currentPageUrl',currentPageUrl)
        const currentPageHostname = new URL(currentPageUrl).hostname
        
        try{
          const requestedPageHostname = new URL(url).hostname
          if (requestedPageHostname === currentPageHostname) {
          
              try {
                  const result = await fetch(url)
                  const text = await result.text()
          
                  currentRequests.delete(url)
                  resolve({text, error:''})
                  
              } catch (e) {
                  currentRequests.delete(url)
                  resolve({error:e, text:''})
              }
  
          
              return
          
          }else{
            //send request through the wp backend
          }

        }catch(e){
          resolve({error:e, text:'Something is wrong with the URL'})
        }








  });
}