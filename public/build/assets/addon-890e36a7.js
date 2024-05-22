const h={computed:{previewText(){return Object.values(this.previews).filter(t=>["null","[]","{}",""].includes(JSON.stringify(t))?null:t).map(t=>typeof t=="string"?escapeHtml(t):Array.isArray(t)&&typeof t[0]=="string"?escapeHtml(t.join(", ")):escapeHtml(JSON.stringify(t))).join(" / ")}}};function o(t,e,s,i,a,l,u,m){var n=typeof t=="function"?t.options:t;e&&(n.render=e,n.staticRenderFns=s,n._compiled=!0),i&&(n.functional=!0),l&&(n._scopeId="data-v-"+l);var d;if(u?(d=function(r){r=r||this.$vnode&&this.$vnode.ssrContext||this.parent&&this.parent.$vnode&&this.parent.$vnode.ssrContext,!r&&typeof __VUE_SSR_CONTEXT__<"u"&&(r=__VUE_SSR_CONTEXT__),a&&a.call(this,r),r&&r._registeredComponents&&r._registeredComponents.add(u)},n._ssrRegister=d):a&&(d=m?function(){a.call(this,(n.functional?this.parent:this).$root.$options.shadowRoot)}:a),d)if(n.functional){n._injectStyles=d;var v=n.render;n.render=function(_,c){return d.call(c),v(_,c)}}else{var p=n.beforeCreate;n.beforeCreate=p?[].concat(p,d):[d]}return{exports:t,options:n}}const{ValidatesFieldConditions:w}=FieldConditions,x={mixins:[w,h],inject:["storeName"],props:{filter:{},field:{},values:{},fields:{},meta:{},previews:{},errors:{},fieldPathPrefix:{},readOnly:{},parentName:{},index:{},collapsed:{default:!1}},data(){return{fieldPreviews:this.previews}},methods:{update(t,e){this.$emit("updated",{...this.values,[t]:e})},updateMeta(t,e){this.$emit("meta-updated",{...this.meta,[t]:e})},updatePreview(t,e){this.$emit("previews-updated",this.fieldPreviews={...this.fieldPreviews,[t]:e})},toggleCollapsed(){this.collapsed?this.expand():this.collapse()},collapse(){this.$emit("collapsed")},expand(){this.$emit("expanded")},fieldPath(t){return`${this.fieldPathPrefix}.values.${t.handle}`},fieldErrors(t){const e=this.$store.state.publish[this.storeName];return e?e.errors[this.fieldPath(t)]||[]:[]}}};var y=function(){var e=this,s=e._self._c;return s("div",{staticClass:"replicator-set mb-2"},[s("div",{staticClass:"replicator-set-header p-0",class:{collapsed:e.collapsed}},[s("div",{staticClass:"flex items-center justify-between flex-1 px-2 py-1.5 replicator-set-header-inner cursor-pointer",on:{click:e.toggleCollapsed}},[s("label",{staticClass:"text-xs whitespace-nowrap mr-2"},[e._v(" "+e._s(e.field.display)+" ")]),s("div",{directives:[{name:"show",rawName:"v-show",value:e.collapsed,expression:"collapsed"}],staticClass:"flex-1 min-w-0 w-1 pr-8"},[s("div",{staticClass:"help-block mb-0 whitespace-nowrap overflow-hidden text-ellipsis",domProps:{innerHTML:e._s(e.previewText)}})]),s("button",{staticClass:"flex group items-center",attrs:{"aria-label":e.__("statamic-filter-builder::fieldtypes.filter_builder.delete_filter")},on:{click:function(i){return e.$emit("removed")}}},[s("svg-icon",{staticClass:"w-4 h-4 text-gray-600 group-hover:text-gray-900",attrs:{name:"micro/trash"}})],1)])]),s("div",{directives:[{name:"show",rawName:"v-show",value:!e.collapsed,expression:"!collapsed"}],staticClass:"replicator-set-body flex-1 publish-fields @container"},e._l(e.fields,function(i){return s("set-field",{directives:[{name:"show",rawName:"v-show",value:e.showField(i,e.fieldPath(i)),expression:"showField(field, fieldPath(field))"}],key:i.handle,attrs:{field:i,value:e.values[i.handle],meta:e.meta[i.handle],"parent-name":e.parentName,"set-index":e.index,errors:e.fieldErrors(i),"field-path":e.fieldPath(i),"read-only":e.isReadOnly,"show-field-previews":!0},on:{updated:function(a){return e.update(i.handle,a)},"meta-updated":function(a){return e.updateMeta(i.handle,a)},"replicator-preview-updated":function(a){return e.updatePreview(i.handle,a)}}})}),1)])},$=[],g=o(x,y,$,!1,null,null,null,null);const C=g.exports,{ValidatesFieldConditions:b}=FieldConditions,P={mixins:[b],inject:["storeName"],data(){return{collapsed:this.value.map(t=>t.id),previews:this.meta.previews}},mounted(){this.meta.fields.length===0&&this.loadCollectionsMeta(this.collections)},computed:{mode(){return this.config.mode||"config"},collections(){const t=this.$store.state.publish[this.storeName],s=(this.fieldPathPrefix||"").slice(0,-this.handle.length)+this.config.field;return data_get(t.values,s)},fieldsObject(){return Object.fromEntries(this.meta.fields.map(t=>[t.handle,t]))},fieldsOptions(){return this.meta.fields.map(t=>({value:t.handle,label:t.display}))},itemFields(){return{field:Object.fromEntries(this.meta.fields.map(t=>[t.handle,t.fields]))}}},methods:{loadCollectionsMeta(t){const e={config:utf8btoa(JSON.stringify({...this.config,mode:"config",collections:t}))};this.$axios.get(cp_url("fields/field-meta"),{params:e}).then(s=>{this.meta=s.data.meta,this.value=s.data.value})},addItem(t,e){const s=uniqid();this.update([...this.value,{id:s,type:t,handle:e,values:this.meta.defaults[e]}]),this.updateMeta({...this.meta,existing:{...this.meta.existing,[s]:this.meta.new[e]}}),this.previews[s]={}},updateItem(t,e){this.update([...this.value.slice(0,t),{...this.value[t],values:e},...this.value.slice(t+1)])},updateItemMeta(t,e){this.updateMeta({...this.meta,existing:{...this.meta.existing,[t]:e}})},updateItemPreviews(t,e){this.previews[t]=e},removeItem(t){this.update([...this.value.slice(0,t),...this.value.slice(t+1)])},collapseItem(t){this.collapsed.includes(t)||this.collapsed.push(t)},expandItem(t){if(this.collapsed.includes(t)){var e=this.collapsed.indexOf(t);this.collapsed.splice(e,1)}},itemMeta(t){return this.meta.existing[t]},itemPreviews(t){return this.previews[t]},itemPath(t){return[...this.fieldPathKeys,t].join(".")}},watch:{collections:function(t,e){JSON.stringify(t)!==JSON.stringify(e)&&(this.update([]),this.updateMeta({...this.meta,existing:{}}),this.loadCollectionsMeta(t))}}},F=null,I=null;var N=o(P,F,I,!1,null,null,null,null);const f=N.exports;const O={mixins:[Fieldtype,f],components:{FilterItem:C}};var M=function(){var e=this,s=e._self._c;return s("div",[s("div",{},e._l(e.value,function(i,a){return s("filter-item",{key:i.id,attrs:{filter:i,field:e.fieldsObject[i.handle],values:i.values,fields:e.itemFields[i.type][i.handle],meta:e.itemMeta(i.id),previews:e.itemPreviews(i.id),"field-path-prefix":e.itemPath(a),"read-only":e.isReadOnly,"parent-name":e.name,index:a,collapsed:e.collapsed.includes(i.id)},on:{collapsed:function(l){return e.collapseItem(i.id)},expanded:function(l){return e.expandItem(i.id)},updated:function(l){return e.updateItem(a,l)},"meta-updated":function(l){return e.updateItemMeta(i.id,l)},removed:function(l){return e.removeItem(a)},"previews-updated":function(l){return e.updateItemPreviews(i.id,l)}}})}),1),s("div",{staticClass:"flex"},[s("v-select",{staticClass:"w-52",attrs:{"append-to-body":!0,placeholder:e.__("statamic-filter-builder::fieldtypes.filter_builder.add_filter"),options:e.fieldsOptions,reduce:i=>i.value,value:null},on:{input:function(i){return e.addItem("field",i)}}})],1)])},R=[],S=o(O,M,R,!1,null,null,null,null);const j=S.exports,{ValidatesFieldConditions:k}=FieldConditions,T={mixins:[k,h],inject:["storeName"],props:{sort:{},field:{},values:{},fields:{},meta:{},previews:{},errors:{},fieldPathPrefix:{},readOnly:{},parentName:{},index:{},collapsed:{default:!1}},data(){return{fieldPreviews:this.previews}},methods:{update(t,e){this.$emit("updated",{...this.values,[t]:e})},updateMeta(t,e){this.$emit("meta-updated",{...this.meta,[t]:e})},updatePreview(t,e){this.$emit("previews-updated",this.fieldPreviews={...this.fieldPreviews,[t]:e})},toggleCollapsed(){this.collapsed?this.expand():this.collapse()},collapse(){this.$emit("collapsed")},expand(){this.$emit("expanded")},fieldPath(t){return`${this.fieldPathPrefix}.values.${t.handle}`},fieldErrors(t){const e=this.$store.state.publish[this.storeName];return e?e.errors[this.fieldPath(t)]||[]:[]}}};var E=function(){var e=this,s=e._self._c;return s("div",{staticClass:"replicator-set mb-2"},[s("div",{staticClass:"replicator-set-header p-0",class:{collapsed:e.collapsed}},[s("div",{staticClass:"flex items-center justify-between flex-1 px-2 py-1.5 replicator-set-header-inner cursor-pointer",on:{click:e.toggleCollapsed}},[s("label",{staticClass:"text-xs whitespace-nowrap mr-2"},[e._v(" "+e._s(e.field.display)+" ")]),s("div",{directives:[{name:"show",rawName:"v-show",value:e.collapsed,expression:"collapsed"}],staticClass:"flex-1 min-w-0 w-1 pr-8"},[s("div",{staticClass:"help-block mb-0 whitespace-nowrap overflow-hidden text-ellipsis",domProps:{innerHTML:e._s(e.previewText)}})]),s("button",{staticClass:"flex group items-center",attrs:{"aria-label":e.__("statamic-filter-builder::fieldtypes.sort_builder.delete_sort")},on:{click:function(i){return e.$emit("removed")}}},[s("svg-icon",{staticClass:"w-4 h-4 text-gray-600 group-hover:text-gray-900",attrs:{name:"micro/trash"}})],1)])]),s("div",{directives:[{name:"show",rawName:"v-show",value:!e.collapsed,expression:"!collapsed"}],staticClass:"replicator-set-body flex-1 publish-fields @container"},e._l(e.fields,function(i){return s("set-field",{directives:[{name:"show",rawName:"v-show",value:e.showField(i,e.fieldPath(i)),expression:"showField(field, fieldPath(field))"}],key:i.handle,attrs:{field:i,value:e.values[i.handle],meta:e.meta[i.handle],"parent-name":e.parentName,"set-index":e.index,errors:e.fieldErrors(i),"field-path":e.fieldPath(i),"read-only":e.isReadOnly,"show-field-previews":!0},on:{updated:function(a){return e.update(i.handle,a)},"meta-updated":function(a){return e.updateMeta(i.handle,a)},"replicator-preview-updated":function(a){return e.updatePreview(i.handle,a)}}})}),1)])},V=[],H=o(T,E,V,!1,null,null,null,null);const J=H.exports;const B={mixins:[Fieldtype,f],components:{SortItem:J}};var U=function(){var e=this,s=e._self._c;return s("div",[s("div",{},e._l(e.value,function(i,a){return s("sort-item",{key:i.id,attrs:{sort:i,field:e.fieldsObject[i.handle],values:i.values,fields:e.itemFields[i.type][i.handle],meta:e.itemMeta(i.id),previews:e.itemPreviews(i.id),"field-path-prefix":e.itemPath(a),"read-only":e.isReadOnly,"parent-name":e.name,index:a,collapsed:e.collapsed.includes(i.id)},on:{collapsed:function(l){return e.collapseItem(i.id)},expanded:function(l){return e.expandItem(i.id)},updated:function(l){return e.updateItem(a,l)},"meta-updated":function(l){return e.updateItemMeta(i.id,l)},removed:function(l){return e.removeItem(a)},"previews-updated":function(l){return e.updateItemPreviews(i.id,l)}}})}),1),s("div",{staticClass:"flex"},[s("v-select",{staticClass:"w-52",attrs:{"append-to-body":!0,placeholder:e.__("statamic-filter-builder::fieldtypes.sort_builder.add_sort"),options:e.fieldsOptions,reduce:i=>i.value,value:null},on:{input:function(i){return e.addItem("field",i)}}})],1)])},A=[],L=o(B,U,A,!1,null,null,null,null);const X=L.exports;Statamic.booting(()=>{Statamic.component("filter_builder-fieldtype",j),Statamic.component("sort_builder-fieldtype",X)});
