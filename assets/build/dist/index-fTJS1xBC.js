var _={exports:{}},n={},O=React;/**
 * @license React
 * react-jsx-runtime.production.min.js
 *
 * Copyright (c) Facebook, Inc. and its affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */var l;function w(){if(l)return n;l=1;var t=O,o=Symbol.for("react.element"),d=Symbol.for("react.fragment"),v=Object.prototype.hasOwnProperty,y=t.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED.ReactCurrentOwner,E={key:!0,ref:!0,__self:!0,__source:!0};function p(s,e,c){var r,i={},u=null,f=null;c!==void 0&&(u=""+c),e.key!==void 0&&(u=""+e.key),e.ref!==void 0&&(f=e.ref);for(r in e)v.call(e,r)&&!E.hasOwnProperty(r)&&(i[r]=e[r]);if(s&&s.defaultProps)for(r in e=s.defaultProps,e)i[r]===void 0&&(i[r]=e[r]);return{$$typeof:o,type:s,key:u,ref:f,props:i,_owner:y.current}}return n.Fragment=d,n.jsx=p,n.jsxs=p,n}var R;function b(){return R||(R=1,_.exports=w()),_.exports}var j=b();let m="";const a=wp.i18n.createI18n,x=(a==null?void 0:a())||wp.i18n,L=(t,o)=>{m=o,x.setLocaleData(t,o)},T=t=>x.__(t,m),h=()=>document.documentElement.dir==="rtl";var k=wp.i18n.sprintf;export{T as _,k as a,h as i,j,L as s};
//# sourceMappingURL=index-fTJS1xBC.js.map
