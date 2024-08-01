var i={exports:{}},n={};const u=React;/**
 * @license React
 * react-jsx-runtime.production.min.js
 *
 * Copyright (c) Facebook, Inc. and its affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */var m=u,y=Symbol.for("react.element"),R=Symbol.for("react.fragment"),d=Object.prototype.hasOwnProperty,x=m.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED.ReactCurrentOwner,v={key:!0,ref:!0,__self:!0,__source:!0};function l(r,e,p){var t,o={},s=null,a=null;p!==void 0&&(s=""+p),e.key!==void 0&&(s=""+e.key),e.ref!==void 0&&(a=e.ref);for(t in e)d.call(e,t)&&!v.hasOwnProperty(t)&&(o[t]=e[t]);if(r&&r.defaultProps)for(t in e=r.defaultProps,e)o[t]===void 0&&(o[t]=e[t]);return{$$typeof:y,type:r,key:s,ref:a,props:o,_owner:x.current}}n.Fragment=R;n.jsx=l;n.jsxs=l;i.exports=n;var E=i.exports;let c="";const _=wp.i18n.createI18n,f=(_==null?void 0:_())||wp.i18n,O=(r,e)=>{c=e,f.setLocaleData(r,e)},w=r=>f.__(r,c),b=()=>document.documentElement.dir==="rtl",j=wp.i18n.sprintf;export{w as _,j as a,b as i,E as j,O as s};
//# sourceMappingURL=index-D7Lh2MVf.js.map
