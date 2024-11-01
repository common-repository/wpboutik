(()=>{"use strict";var t,e={905:()=>{const t=window.React,e=window.wp.blocks,o=window.wp.i18n,r=window.wp.blockEditor,n=(window.wp.components,window.wp.coreData),{useSelect:i}=wp.data,a=JSON.parse('{"UU":"wpboutik/add-to-cart"}');(0,e.registerBlockType)(a.UU,{icon:(0,t.createElement)((function(){return(0,t.createElement)("svg",{width:"2em",height:"2em",viewBox:"0 0 24 24",fill:"none"},(0,t.createElement)("path",{"fill-rule":"evenodd","clip-rule":"evenodd",d:"M14 2C14 1.44772 13.5523 1 13 1C12.4477 1 12 1.44772 12 2V8.58579L9.70711 6.29289C9.31658 5.90237 8.68342 5.90237 8.29289 6.29289C7.90237 6.68342 7.90237 7.31658 8.29289 7.70711L12.2929 11.7071C12.6834 12.0976 13.3166 12.0976 13.7071 11.7071L17.7071 7.70711C18.0976 7.31658 18.0976 6.68342 17.7071 6.29289C17.3166 5.90237 16.6834 5.90237 16.2929 6.29289L14 8.58579V2ZM1 3C1 2.44772 1.44772 2 2 2H2.47241C3.82526 2 5.01074 2.90547 5.3667 4.21065L5.78295 5.73688L7.7638 13H18.236L20.2152 5.73709C20.3604 5.20423 20.9101 4.88998 21.4429 5.03518C21.9758 5.18038 22.29 5.73006 22.1448 6.26291L20.1657 13.5258C19.9285 14.3962 19.1381 15 18.236 15H8V16C8 16.5523 8.44772 17 9 17H16.5H18C18.5523 17 19 17.4477 19 18C19 18.212 18.934 18.4086 18.8215 18.5704C18.9366 18.8578 19 19.1715 19 19.5C19 20.8807 17.8807 22 16.5 22C15.1193 22 14 20.8807 14 19.5C14 19.3288 14.0172 19.1616 14.05 19H10.95C10.9828 19.1616 11 19.3288 11 19.5C11 20.8807 9.88071 22 8.5 22C7.11929 22 6 20.8807 6 19.5C6 18.863 6.23824 18.2816 6.63048 17.8402C6.23533 17.3321 6 16.6935 6 16V14.1339L3.85342 6.26312L3.43717 4.73688C3.31852 4.30182 2.92336 4 2.47241 4H2C1.44772 4 1 3.55228 1 3ZM16 19.5C16 19.2239 16.2239 19 16.5 19C16.7761 19 17 19.2239 17 19.5C17 19.7761 16.7761 20 16.5 20C16.2239 20 16 19.7761 16 19.5ZM8 19.5C8 19.2239 8.22386 19 8.5 19C8.77614 19 9 19.2239 9 19.5C9 19.7761 8.77614 20 8.5 20C8.22386 20 8 19.7761 8 19.5Z",fill:"#3c54cc"}))}),null),edit:function({attributes:e,setAttributes:a,context:{postId:u,postType:c}}){if("wpboutik_product"!=c)return(0,t.createElement)("div",{...(0,r.useBlockProps)({className:"wp-block-button__link wp-element-button wpboutik_archive_add_to_cart_button"})},(0,o.__)("Add to cart","wpboutik"));i((t=>t("core").getEntityRecord("postType","wpboutik_product",u)));const[p]=(0,n.useEntityProp)("postType",c,"meta",u),l=function(t){if(null==t)return!0;let e=!0;if(1==t.gestion_stock){if(1==t.continu_rupture)return e;if(t?.variants&&"gift_card"!=t.type){if("[]"!=t.variants){const o=JSON.parse(t.variants);e=!1;for(let t of o)if(0!=+t.quantity){e=!0;break}}}else t.quantity,e=!1}return e}(p);return(0,t.createElement)("div",{...(0,r.useBlockProps)({className:"wp-block-button__link wp-element-button wpboutik_archive_add_to_cart_button"})},l?null!=p&&"[]"!=p.variants&&p.variants?(0,o.__)("Choose options","wpboutik"):(0,o.__)("Add to cart","wpboutik"):(0,o.__)("Out of stock","wpboutik"))}})}},o={};function r(t){var n=o[t];if(void 0!==n)return n.exports;var i=o[t]={exports:{}};return e[t](i,i.exports,r),i.exports}r.m=e,t=[],r.O=(e,o,n,i)=>{if(!o){var a=1/0;for(l=0;l<t.length;l++){for(var[o,n,i]=t[l],u=!0,c=0;c<o.length;c++)(!1&i||a>=i)&&Object.keys(r.O).every((t=>r.O[t](o[c])))?o.splice(c--,1):(u=!1,i<a&&(a=i));if(u){t.splice(l--,1);var p=n();void 0!==p&&(e=p)}}return e}i=i||0;for(var l=t.length;l>0&&t[l-1][2]>i;l--)t[l]=t[l-1];t[l]=[o,n,i]},r.o=(t,e)=>Object.prototype.hasOwnProperty.call(t,e),(()=>{var t={560:0,368:0};r.O.j=e=>0===t[e];var e=(e,o)=>{var n,i,[a,u,c]=o,p=0;if(a.some((e=>0!==t[e]))){for(n in u)r.o(u,n)&&(r.m[n]=u[n]);if(c)var l=c(r)}for(e&&e(o);p<a.length;p++)i=a[p],r.o(t,i)&&t[i]&&t[i][0](),t[i]=0;return r.O(l)},o=globalThis.webpackChunkwpboutik=globalThis.webpackChunkwpboutik||[];o.forEach(e.bind(null,0)),o.push=e.bind(null,o.push.bind(o))})();var n=r.O(void 0,[368],(()=>r(905)));n=r.O(n)})();