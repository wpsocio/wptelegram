var i={exports:{}},n={},u=React;/**
 * @license React
 * react-jsx-runtime.production.min.js
 *
 * Copyright (c) Facebook, Inc. and its affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */var m=u,v=Symbol.for("react.element"),y=Symbol.for("react.fragment"),R=Object.prototype.hasOwnProperty,d=m.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED.ReactCurrentOwner,x={key:!0,ref:!0,__self:!0,__source:!0};function l(t,e,a){var r,o={},s=null,p=null;a!==void 0&&(s=""+a),e.key!==void 0&&(s=""+e.key),e.ref!==void 0&&(p=e.ref);for(r in e)R.call(e,r)&&!x.hasOwnProperty(r)&&(o[r]=e[r]);if(t&&t.defaultProps)for(r in e=t.defaultProps,e)o[r]===void 0&&(o[r]=e[r]);return{$$typeof:v,type:t,key:s,ref:p,props:o,_owner:d.current}}n.Fragment=y;n.jsx=l;n.jsxs=l;i.exports=n;var E=i.exports;let c="";const _=wp.i18n.createI18n,f=(_==null?void 0:_())||wp.i18n,O=(t,e)=>{c=e,f.setLocaleData(t,e)},w=t=>f.__(t,c),b=()=>document.documentElement.dir==="rtl";var j=wp.i18n.sprintf;export{w as _,j as a,b as i,E as j,O as s};
//# sourceMappingURL=index-CYu8q2MW.js.map
