import{g as q}from"./_commonjsHelpers-CqkleIqs.js";function h(r,n){for(var i=0;i<n.length;i++){const o=n[i];if(typeof o!="string"&&!Array.isArray(o)){for(const s in o)if(s!=="default"&&!(s in r)){const u=Object.getOwnPropertyDescriptor(o,s);u&&Object.defineProperty(r,s,u.get?u:{enumerable:!0,get:()=>o[s]})}}}return Object.freeze(Object.defineProperty(r,Symbol.toStringTag,{value:"Module"}))}var p={exports:{}},a={},l,O;function D(){return O||(O=1,l=React),l}/**
 * @license React
 * react-jsx-runtime.production.min.js
 *
 * Copyright (c) Facebook, Inc. and its affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */var w;function P(){if(w)return a;w=1;var r=D(),n=Symbol.for("react.element"),i=Symbol.for("react.fragment"),o=Object.prototype.hasOwnProperty,s=r.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED.ReactCurrentOwner,u={key:!0,ref:!0,__self:!0,__source:!0};function v(_,e,y){var t,c={},f=null,x=null;y!==void 0&&(f=""+y),e.key!==void 0&&(f=""+e.key),e.ref!==void 0&&(x=e.ref);for(t in e)o.call(e,t)&&!u.hasOwnProperty(t)&&(c[t]=e[t]);if(_&&_.defaultProps)for(t in e=_.defaultProps,e)c[t]===void 0&&(c[t]=e[t]);return{$$typeof:n,type:_,key:f,ref:x,props:c,_owner:s.current}}return a.Fragment=i,a.jsx=v,a.jsxs=v,a}var g;function S(){return g||(g=1,p.exports=P()),p.exports}var J=S(),d,j;function T(){return j||(j=1,d=wp.i18n),d}var R=T();const k=q(R),L=h({__proto__:null,default:k},[R]);let E="";const m=R.createI18n,b=(m==null?void 0:m())||L,N=(r,n)=>{E=n,b.setLocaleData(r,n)},A=r=>b.__(r,E),C=()=>document.documentElement.dir==="rtl";export{A as _,R as a,C as i,J as j,D as r,N as s};
//# sourceMappingURL=index-Cg7uBMVa.js.map
