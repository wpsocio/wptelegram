/*! For license information please see wptelegram--p2tg-gb.56bb94c1.js.LICENSE.txt */
!function(e){var t={};function n(r){if(t[r])return t[r].exports;var i=t[r]={i:r,l:!1,exports:{}};return e[r].call(i.exports,i,i.exports,n),i.l=!0,i.exports}n.m=e,n.c=t,n.d=function(e,t,r){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},n.r=function(e){"undefined"!==typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"===typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var i in e)n.d(r,i,function(t){return e[t]}.bind(null,i));return r},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="/",n(n.s=416)}({0:function(e,t){e.exports=window.React},1:function(e,t,n){"use strict";e.exports=n(191)},106:function(e,t,n){"use strict";function r(e){if("undefined"!==typeof Symbol&&Symbol.iterator in Object(e))return Array.from(e)}n.d(t,"a",(function(){return r}))},107:function(e,t,n){"use strict";function r(e){if(Array.isArray(e))return e}n.d(t,"a",(function(){return r}))},108:function(e,t,n){"use strict";function r(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}n.d(t,"a",(function(){return r}))},13:function(e,t,n){"use strict";n.d(t,"a",(function(){return a}));var r=n(73);var i=n(106),o=n(61);function a(e){return function(e){if(Array.isArray(e))return Object(r.a)(e)}(e)||Object(i.a)(e)||Object(o.a)(e)||function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}},132:function(e,t,n){"use strict";n.d(t,"a",(function(){return i}));var r=n(47);function i(e){var t=function(e,t){if("object"!==Object(r.a)(e)||null===e)return e;var n=e[Symbol.toPrimitive];if(void 0!==n){var i=n.call(e,t||"default");if("object"!==Object(r.a)(i))return i;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===t?String:Number)(e)}(e,"string");return"symbol"===Object(r.a)(t)?t:String(t)}},191:function(e,t,n){"use strict";n(192);var r=n(0),i=60103;if(t.Fragment=60107,"function"===typeof Symbol&&Symbol.for){var o=Symbol.for;i=o("react.element"),t.Fragment=o("react.fragment")}var a=r.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED.ReactCurrentOwner,c=Object.prototype.hasOwnProperty,l={key:!0,ref:!0,__self:!0,__source:!0};function s(e,t,n){var r,o={},s=null,u=null;for(r in void 0!==n&&(s=""+n),void 0!==t.key&&(s=""+t.key),void 0!==t.ref&&(u=t.ref),t)c.call(t,r)&&!l.hasOwnProperty(r)&&(o[r]=t[r]);if(e&&e.defaultProps)for(r in t=e.defaultProps)void 0===o[r]&&(o[r]=t[r]);return{$$typeof:i,type:e,key:s,ref:u,props:o,_owner:a.current}}t.jsx=s,t.jsxs=s},192:function(e,t,n){"use strict";var r=Object.getOwnPropertySymbols,i=Object.prototype.hasOwnProperty,o=Object.prototype.propertyIsEnumerable;function a(e){if(null===e||void 0===e)throw new TypeError("Object.assign cannot be called with null or undefined");return Object(e)}e.exports=function(){try{if(!Object.assign)return!1;var e=new String("abc");if(e[5]="de","5"===Object.getOwnPropertyNames(e)[0])return!1;for(var t={},n=0;n<10;n++)t["_"+String.fromCharCode(n)]=n;if("0123456789"!==Object.getOwnPropertyNames(t).map((function(e){return t[e]})).join(""))return!1;var r={};return"abcdefghijklmnopqrst".split("").forEach((function(e){r[e]=e})),"abcdefghijklmnopqrst"===Object.keys(Object.assign({},r)).join("")}catch(i){return!1}}()?Object.assign:function(e,t){for(var n,c,l=a(e),s=1;s<arguments.length;s++){for(var u in n=Object(arguments[s]))i.call(n,u)&&(l[u]=n[u]);if(r){c=r(n);for(var f=0;f<c.length;f++)o.call(n,c[f])&&(l[c[f]]=n[c[f]])}}return l}},198:function(e,t){e.exports=window.wp.data},2:function(e,t,n){"use strict";n.d(t,"c",(function(){return c})),n.d(t,"a",(function(){return l})),n.d(t,"b",(function(){return s}));var r=n(31),i="",o=r.createI18n,a=(null===o||void 0===o?void 0:o())||r,c=function(e,t){i=t,a.setLocaleData(e,t)},l=function(e){return a.__(e,i)},s=function(){return"rtl"===document.documentElement.dir}},278:function(e,t){e.exports=window.wp.plugins},279:function(e,t){e.exports=window.wp.editPost},280:function(e,t){e.exports=window.wp.mediaUtils},3:function(e,t,n){"use strict";n.d(t,"a",(function(){return o}));var r=n(7);function i(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,r)}return n}function o(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?i(Object(n),!0).forEach((function(t){Object(r.a)(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):i(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}},31:function(e,t){e.exports=window.wp.i18n},38:function(e,t){e.exports=window.wp.components},416:function(e,t,n){e.exports=n(418)},418:function(e,t,n){"use strict";n.r(t);var r,i,o,a,c=n(278),l=n(2),s=n(58),u=n(279),f=n(38),b=n(6),d=n(31),j=n(1),O=function(){return Object(j.jsx)(f.SVG,{width:"24",height:"24",xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24",role:"img","aria-hidden":"true",focusable:"false",children:Object(j.jsx)(f.Path,{fillRule:"evenodd",d:"M10.289 4.836A1 1 0 0111.275 4h1.306a1 1 0 01.987.836l.244 1.466c.787.26 1.503.679 2.108 1.218l1.393-.522a1 1 0 011.216.437l.653 1.13a1 1 0 01-.23 1.273l-1.148.944a6.025 6.025 0 010 2.435l1.149.946a1 1 0 01.23 1.272l-.653 1.13a1 1 0 01-1.216.437l-1.394-.522c-.605.54-1.32.958-2.108 1.218l-.244 1.466a1 1 0 01-.987.836h-1.306a1 1 0 01-.986-.836l-.244-1.466a5.995 5.995 0 01-2.108-1.218l-1.394.522a1 1 0 01-1.217-.436l-.653-1.131a1 1 0 01.23-1.272l1.149-.946a6.026 6.026 0 010-2.435l-1.148-.944a1 1 0 01-.23-1.272l.653-1.131a1 1 0 011.217-.437l1.393.522a5.994 5.994 0 012.108-1.218l.244-1.466zM14.929 12a3 3 0 11-6 0 3 3 0 016 0z"})})},p=n(13),g=n(7),y=n(3),v=n(198),m="_wptg_p2tg_",h=Object(y.a)({channels:[],delay:"0",disable_notification:!1,message_template:"",override_switch:!1,send2tg:!0,send_featured_image:!0,files:{}},null===(r=window.wptelegram)||void 0===r?void 0:r.savedSettings),w=function(){var e=Object(v.useDispatch)("core/editor").editPost,t=x().data;return Object(s.useCallback)((function(n){return function(r){var i=Object(y.a)(Object(y.a)({},t),{},Object(g.a)({},n,r));e(Object(g.a)({},m,i),{undoIgnore:!0}),h=i}}),[t,e])},x=function(){return Object(v.useSelect)((function(e){var t=e("core/editor"),n=t.getEditedPostAttribute,r=t.isSavingPost,i=t.isPublishingPost,o=t.isEditedPostDirty,a=n(m);return{data:a||h,isDirty:o(),isSaving:r()||i(),savedData:a}}),[])},S=(null===(i=window.wptelegram)||void 0===i||null===(o=i.uiData)||void 0===o?void 0:o.allChannels)||[],_=function(e){var t=e.isDisabled,n=x().data,r=w(),i=Object(s.useCallback)((function(e){return function(){var t=-1!==n.channels.indexOf(e)?n.channels.filter((function(t){return t!==e})):[].concat(Object(p.a)(n.channels),[e]);r("channels")(t)}}),[n.channels,r]);return Object(j.jsx)(f.BaseControl,{id:"wptg-send-to",label:Object(l.a)("Send to"),children:Object(j.jsx)("div",{role:"group",id:"wptg-send-to","aria-label":Object(l.a)("Send to"),children:S.map((function(e,r){return Object(j.jsx)(f.CheckboxControl,{label:e,disabled:t,checked:-1!==n.channels.indexOf(e),onChange:i(e)},e+r)}))})})},P=n(9),C=n(132),k=n(280),D=function(e){var t=e.open;return Object(j.jsx)(f.Button,{isSecondary:!0,onClick:t,id:"wptg-upload-media",children:Object(l.a)("Add or Upload Files")})},T=[],E=function(e){var t=e.isDisabled,n=x().data,r=w(),i=Object(s.useCallback)((function(e){return function(){var t=n.files,i=(t[e],Object(P.a)(t,[e].map(C.a)));r("files")(i)}}),[n.files,r]),o=Object(s.useCallback)((function(e){var t=e.reduce((function(e,t){var n=t.id,r=t.url;return Object(y.a)(Object(y.a)({},e),{},Object(g.a)({},n,r))}),{});r("files")(t)}),[r]);return Object(j.jsx)(f.Disabled,{isDisabled:t,children:Object(j.jsxs)(f.BaseControl,{id:"wptg-files",label:Object(l.a)("Files"),help:Object(l.a)("Files to be sent after the message."),children:[Object(j.jsx)("br",{}),Object(j.jsx)(k.MediaUpload,{multiple:!0,onSelect:o,allowedTypes:T,render:D}),Object(j.jsx)("ul",{role:"group",id:"wptg-files","aria-label":Object(l.a)("Files"),children:Object.entries(n.files).map((function(e,t){var n=Object(b.a)(e,2),r=n[0],o=n[1].split("/"),a=o[o.length-1];return Object(j.jsx)("li",{children:Object(j.jsxs)(f.Flex,{justify:"flex-start",children:[Object(j.jsx)(f.Button,{icon:Object(j.jsx)(f.Icon,{icon:"no-alt"}),onClick:i(r)}),Object(j.jsx)("span",{children:a})]})},r+t)}))})]})})},I={width:"100%",maxWidth:"650px"},A={display:"flex",flexDirection:"column",justifyContent:"space-between"},F={width:"100%",justifyContent:"center",marginTop:"2em"},M={height:"1.5em"},B=function(){var e=x().data,t=w(),n=Object(s.useState)(!1),r=Object(b.a)(n,2),i=r[0],o=r[1],a=Object(s.useCallback)((function(){return o(!0)}),[]),c=Object(s.useCallback)((function(){return o(!1)}),[]),u=Object(s.useCallback)((function(e){"wptg-upload-media"!==e.target.id&&c()}),[c]),p=Object(j.jsx)("div",{style:M});return Object(j.jsxs)(j.Fragment,{children:[Object(j.jsx)(f.Button,{"aria-label":Object(l.a)("Override settings"),disabled:!e.send2tg,icon:Object(j.jsx)(O,{}),isSmall:!0,onClick:a}),i&&Object(j.jsx)(f.Modal,{isDismissible:!1,title:Object(d.sprintf)("%s (%s)",Object(l.a)("Post to Telegram"),Object(l.a)("Override settings")),onRequestClose:u,style:I,children:Object(j.jsxs)("div",{style:A,children:[Object(j.jsxs)("div",{children:[Object(j.jsx)(f.ToggleControl,{label:Object(l.a)("Override settings"),checked:e.override_switch,onChange:t("override_switch")}),p,Object(j.jsx)(_,{isDisabled:!e.override_switch}),p,Object(j.jsx)(E,{isDisabled:!e.override_switch}),p,Object(j.jsx)(f.ToggleControl,{disabled:!e.override_switch,label:Object(l.a)("Disable Notifications"),checked:e.disable_notification,onChange:t("disable_notification")}),p,Object(j.jsx)(f.TextControl,{disabled:!e.override_switch,label:Object(l.a)("Delay in Posting"),value:e.delay,onChange:t("delay"),step:"0.5",min:0,type:"number"}),p,Object(j.jsx)(f.ToggleControl,{disabled:!e.override_switch,label:Object(l.a)("Featured Image"),checked:e.send_featured_image,onChange:t("send_featured_image"),help:Object(l.a)("Send Featured Image (if exists).")}),p,Object(j.jsx)(f.TextareaControl,{disabled:!e.override_switch,label:Object(l.a)("Message Template"),value:e.message_template,onChange:t("message_template"),rows:10})]}),Object(j.jsx)(f.Button,{style:F,isPrimary:!0,onClick:c,children:Object(l.a)("Save Changes")})]})})]})},R=function(){var e=x(),t=e.data,n=e.savedData,r=e.isSaving,i=e.isDirty,o=w();return Object(s.useEffect)((function(){!i||r||n||o("send2tg")(t.send2tg)}),[t.send2tg,i,r,n,o]),Object(j.jsxs)(u.PluginPostStatusInfo,{children:[Object(j.jsx)(f.ToggleControl,{label:Object(l.a)("Send to Telegram"),checked:t.send2tg,onChange:o("send2tg")}),Object(j.jsx)(B,{}),"\xa0"]})},N=null===(a=window.wptelegram)||void 0===a?void 0:a.i18n;Object(l.c)(N,"wptelegram"),Object(c.registerPlugin)("wptelegram-post-to-telegram",{icon:null,render:function(){return Object(j.jsx)(R,{})}})},47:function(e,t,n){"use strict";function r(e){return(r="function"===typeof Symbol&&"symbol"===typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"===typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}n.d(t,"a",(function(){return r}))},58:function(e,t){e.exports=window.wp.element},6:function(e,t,n){"use strict";n.d(t,"a",(function(){return a}));var r=n(107);var i=n(61),o=n(108);function a(e,t){return Object(r.a)(e)||function(e,t){if("undefined"!==typeof Symbol&&Symbol.iterator in Object(e)){var n=[],r=!0,i=!1,o=void 0;try{for(var a,c=e[Symbol.iterator]();!(r=(a=c.next()).done)&&(n.push(a.value),!t||n.length!==t);r=!0);}catch(l){i=!0,o=l}finally{try{r||null==c.return||c.return()}finally{if(i)throw o}}return n}}(e,t)||Object(i.a)(e,t)||Object(o.a)()}},61:function(e,t,n){"use strict";n.d(t,"a",(function(){return i}));var r=n(73);function i(e,t){if(e){if("string"===typeof e)return Object(r.a)(e,t);var n=Object.prototype.toString.call(e).slice(8,-1);return"Object"===n&&e.constructor&&(n=e.constructor.name),"Map"===n||"Set"===n?Array.from(e):"Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?Object(r.a)(e,t):void 0}}},7:function(e,t,n){"use strict";function r(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}n.d(t,"a",(function(){return r}))},73:function(e,t,n){"use strict";function r(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,r=new Array(t);n<t;n++)r[n]=e[n];return r}n.d(t,"a",(function(){return r}))},9:function(e,t,n){"use strict";function r(e,t){if(null==e)return{};var n,r,i=function(e,t){if(null==e)return{};var n,r,i={},o=Object.keys(e);for(r=0;r<o.length;r++)n=o[r],t.indexOf(n)>=0||(i[n]=e[n]);return i}(e,t);if(Object.getOwnPropertySymbols){var o=Object.getOwnPropertySymbols(e);for(r=0;r<o.length;r++)n=o[r],t.indexOf(n)>=0||Object.prototype.propertyIsEnumerable.call(e,n)&&(i[n]=e[n])}return i}n.d(t,"a",(function(){return r}))}});
//# sourceMappingURL=wptelegram--p2tg-gb.56bb94c1.js.map