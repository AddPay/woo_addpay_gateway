(()=>{"use strict";var t={};t.g=function(){if("object"==typeof globalThis)return globalThis;try{return this||new Function("return this")()}catch(t){if("object"==typeof window)return window}}(),(()=>{var e;t.g.importScripts&&(e=t.g.location+"");var r=t.g.document;if(!e&&r&&(r.currentScript&&(e=r.currentScript.src),!e)){var n=r.getElementsByTagName("script");if(n.length)for(var a=n.length-1;a>-1&&(!e||!/^http(s?):/.test(e));)e=n[a--].src}if(!e)throw new Error("Automatic publicPath is not supported in this browser");e=e.replace(/#.*$/,"").replace(/\?.*$/,"").replace(/\/[^\/]+$/,"/"),t.p=e})();const e=window.React,r=window.wp.htmlEntities,n=t.p+"images/logo.a4b54009.png",{registerPaymentMethod:a}=window.wc.wcBlocksRegistry,{getSetting:i}=window.wc.wcSettings,c=i("addpay_data",{}),o=(0,r.decodeEntities)(c.title),s=()=>(0,e.createElement)("img",{src:n,alt:"AddPay"});a({name:"addpay",label:(0,e.createElement)((t=>{const{PaymentMethodLabel:r}=t.components;return(0,e.createElement)(r,{text:o})}),null),content:(0,e.createElement)(s,null),edit:(0,e.createElement)(s,null),canMakePayment:()=>!0,ariaLabel:o,supports:{features:c.supports}})})();