/*! For license information please see wptelegram--p2tg-gb.5da8d52c.js.LICENSE.txt */
!function(e){var t={};function n(r){if(t[r])return t[r].exports;var o=t[r]={i:r,l:!1,exports:{}};return e[r].call(o.exports,o,o.exports,n),o.l=!0,o.exports}n.m=e,n.c=t,n.d=function(e,t,r){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},n.r=function(e){"undefined"!==typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"===typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var o in e)n.d(r,o,function(t){return e[t]}.bind(null,o));return r},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="/",n(n.s=344)}({0:function(e,t){e.exports=window.React},1:function(e,t,n){"use strict";e.exports=n(151)},100:function(e,t,n){"use strict";n.d(t,"a",(function(){return o}));var r=n(35);function o(e){var t=function(e,t){if("object"!==Object(r.a)(e)||null===e)return e;var n=e[Symbol.toPrimitive];if(void 0!==n){var o=n.call(e,t||"default");if("object"!==Object(r.a)(o))return o;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===t?String:Number)(e)}(e,"string");return"symbol"===Object(r.a)(t)?t:String(t)}},11:function(e,t,n){"use strict";n.d(t,"a",(function(){return c}));var r=n(54);var o=n(75),i=n(46);function c(e){return function(e){if(Array.isArray(e))return Object(r.a)(e)}(e)||Object(o.a)(e)||Object(i.a)(e)||function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}},13:function(e,t,n){"use strict";function r(e,t){if(null==e)return{};var n,r,o=function(e,t){if(null==e)return{};var n,r,o={},i=Object.keys(e);for(r=0;r<i.length;r++)n=i[r],t.indexOf(n)>=0||(o[n]=e[n]);return o}(e,t);if(Object.getOwnPropertySymbols){var i=Object.getOwnPropertySymbols(e);for(r=0;r<i.length;r++)n=i[r],t.indexOf(n)>=0||Object.prototype.propertyIsEnumerable.call(e,n)&&(o[n]=e[n])}return o}n.d(t,"a",(function(){return r}))},151:function(e,t,n){"use strict";n(152);var r=n(0),o=60103;if(t.Fragment=60107,"function"===typeof Symbol&&Symbol.for){var i=Symbol.for;o=i("react.element"),t.Fragment=i("react.fragment")}var c=r.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED.ReactCurrentOwner,a=Object.prototype.hasOwnProperty,l={key:!0,ref:!0,__self:!0,__source:!0};function u(e,t,n){var r,i={},u=null,s=null;for(r in void 0!==n&&(u=""+n),void 0!==t.key&&(u=""+t.key),void 0!==t.ref&&(s=t.ref),t)a.call(t,r)&&!l.hasOwnProperty(r)&&(i[r]=t[r]);if(e&&e.defaultProps)for(r in t=e.defaultProps)void 0===i[r]&&(i[r]=t[r]);return{$$typeof:o,type:e,key:u,ref:s,props:i,_owner:c.current}}t.jsx=u,t.jsxs=u},152:function(e,t,n){"use strict";var r=Object.getOwnPropertySymbols,o=Object.prototype.hasOwnProperty,i=Object.prototype.propertyIsEnumerable;function c(e){if(null===e||void 0===e)throw new TypeError("Object.assign cannot be called with null or undefined");return Object(e)}e.exports=function(){try{if(!Object.assign)return!1;var e=new String("abc");if(e[5]="de","5"===Object.getOwnPropertyNames(e)[0])return!1;for(var t={},n=0;n<10;n++)t["_"+String.fromCharCode(n)]=n;if("0123456789"!==Object.getOwnPropertyNames(t).map((function(e){return t[e]})).join(""))return!1;var r={};return"abcdefghijklmnopqrst".split("").forEach((function(e){r[e]=e})),"abcdefghijklmnopqrst"===Object.keys(Object.assign({},r)).join("")}catch(o){return!1}}()?Object.assign:function(e,t){for(var n,a,l=c(e),u=1;u<arguments.length;u++){for(var s in n=Object(arguments[u]))o.call(n,s)&&(l[s]=n[s]);if(r){a=r(n);for(var f=0;f<a.length;f++)i.call(n,a[f])&&(l[a[f]]=n[a[f]])}}return l}},160:function(e,t){e.exports=window.wp.data},2:function(e,t,n){"use strict";n.d(t,"c",(function(){return a})),n.d(t,"a",(function(){return l})),n.d(t,"b",(function(){return u}));var r=n(48),o="",i=r.createI18n,c=(null===i||void 0===i?void 0:i())||r,a=function(e,t){o=t,c.setLocaleData(e,t)},l=function(e){return c.__(e,o)},u=function(){return"rtl"===document.documentElement.dir}},212:function(e,t){e.exports=window.wp.plugins},213:function(e,t){e.exports=window.wp.editPost},214:function(e,t){e.exports=window.wp.compose},215:function(e,t){e.exports=window.wp.mediaUtils},28:function(e,t){e.exports=window.wp.components},344:function(e,t,n){e.exports=n(346)},346:function(e,t,n){"use strict";n.r(t);var r,o,i,c=n(212),a=n(213),l=n(28),u=n(2),s=n(6),f=n(61),b=n(48),d=n(1),j=function(){return Object(d.jsx)(l.SVG,{width:"24",height:"24",xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24",role:"img","aria-hidden":"true",focusable:"false",children:Object(d.jsx)(l.Path,{fillRule:"evenodd",d:"M10.289 4.836A1 1 0 0111.275 4h1.306a1 1 0 01.987.836l.244 1.466c.787.26 1.503.679 2.108 1.218l1.393-.522a1 1 0 011.216.437l.653 1.13a1 1 0 01-.23 1.273l-1.148.944a6.025 6.025 0 010 2.435l1.149.946a1 1 0 01.23 1.272l-.653 1.13a1 1 0 01-1.216.437l-1.394-.522c-.605.54-1.32.958-2.108 1.218l-.244 1.466a1 1 0 01-.987.836h-1.306a1 1 0 01-.986-.836l-.244-1.466a5.995 5.995 0 01-2.108-1.218l-1.394.522a1 1 0 01-1.217-.436l-.653-1.131a1 1 0 01.23-1.272l1.149-.946a6.026 6.026 0 010-2.435l-1.148-.944a1 1 0 01-.23-1.272l.653-1.131a1 1 0 011.217-.437l1.393.522a5.994 5.994 0 012.108-1.218l.244-1.466zM14.929 12a3 3 0 11-6 0 3 3 0 016 0z"})})},p=n(7),O=n(4),y=n(214),g=n(160),m="_wptg_p2tg_",v=Object(O.a)({channels:[],delay:"0",disable_notification:!1,message_template:"",override_switch:!1,send2tg:!0},null===(r=window.wptelegram)||void 0===r?void 0:r.savedSettings),h=Object(y.compose)([Object(g.withSelect)((function(e){return{data:e("core/editor").getEditedPostAttribute(m)||v}})),Object(g.withDispatch)((function(e,t,n){var r=n.select;return{updateField:function(t){return function(n){var o=r("core/editor").getEditedPostAttribute(m)||v;e("core/editor").editPost(Object(p.a)({},m,Object(O.a)(Object(O.a)({},o),{},Object(p.a)({},t,n))),{undoIgnore:!0})}}}}))]),w=n(11),x=(null===(o=window.wptelegram)||void 0===o||null===(i=o.uiData)||void 0===i?void 0:i.allChannels)||[],S=h((function(e){var t=e.data,n=e.isDisabled,r=e.updateField,o=Object(f.useCallback)((function(e){return function(){var n=-1!==t.channels.indexOf(e)?t.channels.filter((function(t){return t!==e})):[].concat(Object(w.a)(t.channels),[e]);r("channels")(n)}}),[t.channels,r]);return Object(d.jsx)(l.BaseControl,{id:"wptg-send-to",label:Object(u.a)("Send to"),children:Object(d.jsx)("div",{role:"group",id:"wptg-send-to","aria-label":Object(u.a)("Send to"),children:x.map((function(e,r){return Object(d.jsx)(l.CheckboxControl,{label:e,disabled:n,checked:-1!==t.channels.indexOf(e),onChange:o(e)},e+r)}))})})})),_=n(13),P=n(100),C=n(215),k=function(e){var t=e.open;return Object(d.jsx)(l.Button,{isSecondary:!0,onClick:t,id:"wptg-upload-media",children:Object(u.a)("Add or Upload Files")})},D=[],T=h((function(e){var t=e.data,n=e.isDisabled,r=e.updateField,o=Object(f.useCallback)((function(e){return function(){var n=t.files,o=(n[e],Object(_.a)(n,[e].map(P.a)));r("files")(o)}}),[t.files,r]),i=Object(f.useCallback)((function(e){var t=e.reduce((function(e,t){var n=t.id,r=t.url;return Object(O.a)(Object(O.a)({},e),{},Object(p.a)({},n,r))}),{});r("files")(t)}),[r]);return Object(d.jsx)(l.Disabled,{isDisabled:n,children:Object(d.jsxs)(l.BaseControl,{id:"wptg-files",label:Object(u.a)("Files"),help:Object(u.a)("Files to be sent after the message."),children:[Object(d.jsx)("br",{}),Object(d.jsx)(C.MediaUpload,{multiple:!0,onSelect:i,allowedTypes:D,render:k}),Object(d.jsx)("ul",{role:"group",id:"wptg-files","aria-label":Object(u.a)("Files"),children:Object.entries(t.files).map((function(e,t){var n=Object(s.a)(e,2),r=n[0],i=n[1].split("/"),c=i[i.length-1];return Object(d.jsx)("li",{children:Object(d.jsxs)(l.Flex,{justify:"flex-start",children:[Object(d.jsx)(l.Button,{icon:Object(d.jsx)(l.Icon,{icon:"no-alt"}),onClick:o(r)}),Object(d.jsx)("span",{children:c})]})},r+t)}))})]})})})),E={width:"100%",maxWidth:"650px"},A={display:"flex",flexDirection:"column",justifyContent:"space-between"},F={width:"100%",justifyContent:"center",marginTop:"2em"},I={height:"1.5em"},M=h((function(e){var t=e.data,n=e.updateField,r=Object(f.useState)(!1),o=Object(s.a)(r,2),i=o[0],c=o[1],a=Object(f.useCallback)((function(){return c(!0)}),[]),p=Object(f.useCallback)((function(){return c(!1)}),[]),O=Object(d.jsx)("div",{style:I}),y=Object(f.useCallback)((function(e){"wptg-upload-media"!==e.target.id&&p()}),[p]);return Object(d.jsxs)(d.Fragment,{children:[Object(d.jsx)(l.Button,{"aria-label":Object(u.a)("Override settings"),disabled:!t.send2tg,icon:Object(d.jsx)(j,{}),isSmall:!0,onClick:a}),i&&Object(d.jsx)(l.Modal,{isDismissible:!1,title:Object(b.sprintf)("%s (%s)",Object(u.a)("Post to Telegram"),Object(u.a)("Override settings")),onRequestClose:y,style:E,children:Object(d.jsxs)("div",{style:A,children:[Object(d.jsxs)("div",{children:[Object(d.jsx)(l.ToggleControl,{label:Object(u.a)("Override settings"),checked:t.override_switch,onChange:n("override_switch")}),O,Object(d.jsx)(S,{isDisabled:!t.override_switch}),O,Object(d.jsx)(T,{isDisabled:!t.override_switch}),O,Object(d.jsx)(l.ToggleControl,{disabled:!t.override_switch,label:Object(u.a)("Disable Notifications"),checked:t.disable_notification,onChange:n("disable_notification")}),O,Object(d.jsx)(l.TextControl,{disabled:!t.override_switch,label:Object(u.a)("Delay in Posting"),value:t.delay,onChange:n("delay"),step:"0.5",min:0,type:"number"}),O,Object(d.jsx)(l.TextareaControl,{disabled:!t.override_switch,label:Object(u.a)("Message Template"),value:t.message_template,onChange:n("message_template"),rows:10})]}),Object(d.jsx)(l.Button,{style:F,isPrimary:!0,onClick:p,children:Object(u.a)("Save Changes")})]})})]})}));Object(c.registerPlugin)("wptelegram-post-to-telegram",{icon:null,render:h((function(e){var t=e.data,n=e.updateField;return Object(d.jsxs)(a.PluginPostStatusInfo,{children:[Object(d.jsx)(l.ToggleControl,{label:Object(u.a)("Send to Telegram"),checked:t.send2tg,onChange:n("send2tg")}),Object(d.jsx)(M,{}),"\xa0"]})}))})},35:function(e,t,n){"use strict";function r(e){return(r="function"===typeof Symbol&&"symbol"===typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"===typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}n.d(t,"a",(function(){return r}))},4:function(e,t,n){"use strict";n.d(t,"a",(function(){return i}));var r=n(7);function o(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,r)}return n}function i(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?o(Object(n),!0).forEach((function(t){Object(r.a)(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):o(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}},46:function(e,t,n){"use strict";n.d(t,"a",(function(){return o}));var r=n(54);function o(e,t){if(e){if("string"===typeof e)return Object(r.a)(e,t);var n=Object.prototype.toString.call(e).slice(8,-1);return"Object"===n&&e.constructor&&(n=e.constructor.name),"Map"===n||"Set"===n?Array.from(e):"Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?Object(r.a)(e,t):void 0}}},48:function(e,t){e.exports=window.wp.i18n},54:function(e,t,n){"use strict";function r(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,r=new Array(t);n<t;n++)r[n]=e[n];return r}n.d(t,"a",(function(){return r}))},6:function(e,t,n){"use strict";n.d(t,"a",(function(){return c}));var r=n(76);var o=n(46),i=n(77);function c(e,t){return Object(r.a)(e)||function(e,t){if("undefined"!==typeof Symbol&&Symbol.iterator in Object(e)){var n=[],r=!0,o=!1,i=void 0;try{for(var c,a=e[Symbol.iterator]();!(r=(c=a.next()).done)&&(n.push(c.value),!t||n.length!==t);r=!0);}catch(l){o=!0,i=l}finally{try{r||null==a.return||a.return()}finally{if(o)throw i}}return n}}(e,t)||Object(o.a)(e,t)||Object(i.a)()}},61:function(e,t){e.exports=window.wp.element},7:function(e,t,n){"use strict";function r(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}n.d(t,"a",(function(){return r}))},75:function(e,t,n){"use strict";function r(e){if("undefined"!==typeof Symbol&&Symbol.iterator in Object(e))return Array.from(e)}n.d(t,"a",(function(){return r}))},76:function(e,t,n){"use strict";function r(e){if(Array.isArray(e))return e}n.d(t,"a",(function(){return r}))},77:function(e,t,n){"use strict";function r(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}n.d(t,"a",(function(){return r}))}});
//# sourceMappingURL=wptelegram--p2tg-gb.5da8d52c.js.map