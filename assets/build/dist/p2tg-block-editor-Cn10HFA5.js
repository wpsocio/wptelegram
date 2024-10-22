import{_ as a,j as e,a as k,s as F}from"./index-CYu8q2MW.js";const b="_wptg_p2tg_",B="wptelegram-post-to-telegram";var _;let y={channels:[],delay:"0",disable_notification:!1,message_template:"",override_switch:!1,send2tg:!0,send_featured_image:!0,files:{},...(_=window.wptelegram)==null?void 0:_.savedSettings};const w=()=>{const{editPost:t}=wp.data.useDispatch("core/editor"),{data:s}=m();return wp.element.useCallback(c=>l=>{const o={...s,[c]:l};t({[b]:o},{undoIgnore:!0}),y=o},[s,t])},m=()=>wp.data.useSelect(t=>{const{getEditedPostAttribute:s,isSavingPost:c,isPublishingPost:l,isEditedPostDirty:o}=t(wp.editor.store),i=s(b);return{data:i||y,isDirty:o(),isSaving:c()||l(),savedData:i}},[]);var f,C;const g=((C=(f=window.wptelegram)==null?void 0:f.uiData)==null?void 0:C.allChannels)||[],P=()=>{var p;const{data:t}=m(),s=w(),c=wp.element.useCallback(n=>()=>{var h,x;const u=((h=t.channels)==null?void 0:h.indexOf(n))!==-1?(x=t.channels)==null?void 0:x.filter(S=>S!==n):[...t.channels||[],n];s("channels")(u)},[t.channels,s]),l=g.every(n=>{var r;return((r=t.channels)==null?void 0:r.indexOf(n))!==-1}),o=!l&&g.some(n=>{var r;return((r=t.channels)==null?void 0:r.indexOf(n))!==-1}),i=g.length>5?`(${((p=t.channels)==null?void 0:p.length)||0}/${g.length})`:"",d=`${a("Send to")} ${i}`;return e.jsx(wp.components.BaseControl,{id:"wptg-send-to",label:d,__nextHasNoMarginBottom:!0,children:e.jsxs(wp.components.Flex,{role:"group",direction:"column",id:"wptg-send-to","aria-label":d,as:"fieldset",children:[e.jsx(wp.components.CheckboxControl,{checked:l,indeterminate:o,onChange:n=>{const r=n?g:[];s("channels")(r)},label:a("Select all"),__nextHasNoMarginBottom:!0}),g.map((n,r)=>{var u;return e.jsx(wp.components.CheckboxControl,{label:n,checked:((u=t.channels)==null?void 0:u.indexOf(n))!==-1,onChange:c(n),__nextHasNoMarginBottom:!0},n+r)})]})})},D=({open:t})=>e.jsx(wp.components.Button,{variant:"secondary",onClick:t,id:"wptg-upload-media",children:a("Add or Upload Files")}),M=[];function T(){const{data:t}=m(),s=w(),c=wp.element.useCallback(o=>()=>{const{[o]:i,...d}=t.files||{};s("files")(d)},[t.files,s]),l=wp.element.useCallback(o=>{const i=o.reduce((d,{id:p,url:n})=>(d[p]=n,d),{});s("files")(i)},[s]);return e.jsxs(wp.components.BaseControl,{id:"wptg-files",label:a("Files"),help:a("Files to be sent after the message."),__nextHasNoMarginBottom:!0,children:[e.jsx(wp.mediaUtils.MediaUpload,{multiple:!0,onSelect:l,allowedTypes:M,render:D}),e.jsx("fieldset",{children:e.jsx("ul",{id:"wptg-files","aria-label":a("Files"),children:Object.entries(t.files||{}).map(([o,i],d)=>{const p=i.split("/"),n=p[p.length-1];return e.jsx("li",{children:e.jsxs(wp.components.Flex,{justify:"flex-start",children:[e.jsx(wp.components.Button,{icon:e.jsx(wp.components.Icon,{icon:"no-alt"}),onClick:c(o)}),e.jsx("span",{children:n})]})},o+d)})})})]})}const v=()=>e.jsx(wp.components.SVG,{width:"24",height:"24",xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24",role:"img","aria-hidden":"true",focusable:"false",children:e.jsx(wp.components.Path,{fillRule:"evenodd",d:"M10.289 4.836A1 1 0 0111.275 4h1.306a1 1 0 01.987.836l.244 1.466c.787.26 1.503.679 2.108 1.218l1.393-.522a1 1 0 011.216.437l.653 1.13a1 1 0 01-.23 1.273l-1.148.944a6.025 6.025 0 010 2.435l1.149.946a1 1 0 01.23 1.272l-.653 1.13a1 1 0 01-1.216.437l-1.394-.522c-.605.54-1.32.958-2.108 1.218l-.244 1.466a1 1 0 01-.987.836h-1.306a1 1 0 01-.986-.836l-.244-1.466a5.995 5.995 0 01-2.108-1.218l-1.394.522a1 1 0 01-1.217-.436l-.653-1.131a1 1 0 01.23-1.272l1.149-.946a6.026 6.026 0 010-2.435l-1.148-.944a1 1 0 01-.23-1.272l.653-1.131a1 1 0 011.217-.437l1.393.522a5.994 5.994 0 012.108-1.218l.244-1.466zM14.929 12a3 3 0 11-6 0 3 3 0 016 0z"})}),N={width:"100%",maxWidth:"650px"},I={width:"100%",justifyContent:"center",marginTop:"2em"},O=()=>{const{data:t}=m(),s=w(),[c,l]=wp.element.useState(!1),o=wp.element.useCallback(()=>l(!0),[]),i=wp.element.useCallback(()=>l(!1),[]),d=wp.element.useCallback(n=>{var r;n!=null&&n.target&&"id"in n.target&&((r=n==null?void 0:n.target)==null?void 0:r.id)==="wptg-upload-media"||i()},[i]),p=!t.override_switch;return e.jsxs(e.Fragment,{children:[e.jsx(wp.components.Button,{"aria-label":a("Override settings"),disabled:!t.send2tg,icon:e.jsx(v,{}),size:"small",onClick:o}),c&&e.jsx(wp.components.Modal,{isDismissible:!1,title:k("%s (%s)",a("Post to Telegram"),a("Override settings")),onRequestClose:d,style:N,children:e.jsxs(wp.components.Flex,{direction:"column",children:[e.jsxs(wp.components.Flex,{direction:"column",gap:8,children:[e.jsx(wp.components.ToggleControl,{label:a("Override settings"),checked:t.override_switch,onChange:s("override_switch"),__nextHasNoMarginBottom:!0}),e.jsx(wp.components.Disabled,{isDisabled:p,style:{opacity:p?.3:1},children:e.jsxs(wp.components.Flex,{direction:"column",gap:6,children:[e.jsx(P,{}),e.jsx(T,{}),e.jsx(wp.components.ToggleControl,{label:a("Disable Notifications"),checked:t.disable_notification,onChange:s("disable_notification"),__nextHasNoMarginBottom:!0}),e.jsx(wp.components.TextControl,{label:a("Delay in Posting"),value:t.delay||"0.5",onChange:s("delay"),step:"0.5",min:0,type:"number",__nextHasNoMarginBottom:!0}),e.jsx(wp.components.ToggleControl,{label:a("Featured Image"),checked:t.send_featured_image,onChange:s("send_featured_image"),help:a("Send Featured Image (if exists)."),__nextHasNoMarginBottom:!0}),e.jsx(wp.components.TextareaControl,{label:a("Message Template"),value:t.message_template||"",onChange:s("message_template"),rows:10,__nextHasNoMarginBottom:!0})]})})]}),e.jsx(wp.components.Button,{style:I,variant:"primary",onClick:i,children:a("Save Changes")})]})})]})},H={display:"flex",justifyContent:"space-between",alignItems:"flex-start",width:"100%",marginTop:"1rem"},A=wp.editor.PluginPostStatusInfo||wp.editPost.PluginPostStatusInfo,E=()=>{const{data:t,savedData:s,isSaving:c,isDirty:l}=m(),o=w();return wp.element.useEffect(()=>{l&&!c&&!s&&o("send2tg")(t.send2tg)},[t.send2tg,l,c,s,o]),e.jsx(A,{children:e.jsxs("div",{style:H,children:[e.jsx(wp.components.ToggleControl,{label:a("Send to Telegram"),checked:t.send2tg,onChange:o("send2tg"),__nextHasNoMarginBottom:!0}),e.jsx(O,{})]})})};var j;const U=(j=window.wptelegram)==null?void 0:j.i18n;F(U,"wptelegram");wp.plugins.registerPlugin(B,{render:E});
//# sourceMappingURL=p2tg-block-editor-Cn10HFA5.js.map
