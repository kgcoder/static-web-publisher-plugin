/*
Visible Connections

Copyright (c) 2025 Karen Grigorian
Code licensed under the MIT License.

This software implements document types defined by the Default Web project.

Default Web document types are licensed under CC BY-ND 4.0 and are maintained externally.

For the official list of document types and specifications, see:
https://github.com/kgcoder/default-web
*/

import FLPointEnd from "./FLPointEnd.js"
import FLTextEnd from "./FLTextEnd.js"

class FLEnd{

    static fromObject(object){
        const {t:type} = object
        if(type === 't'){
            return FLTextEnd.fromObject(object)
        }else if(type === 'p'){
            return FLPointEnd.fromObject(object)
        }
    }


}

export default FLEnd