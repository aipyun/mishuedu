/*!
 * ====================================================
 * Kity Formula Parser - v1.0.0 - 2014-07-30
 * https://github.com/HanCong03/kityformula-editor
 * GitHub: https://github.com/kitygraph/kityformula-editor.git 
 * Copyright (c) 2014 Baidu Kity Group; Licensed MIT
 * ====================================================
 */
!function(){function a(a){b.r([c[a]])}var b={r:function(a){if(b[a].inited)return b[a].value;if("function"!=typeof b[a].value)return b[a].inited=!0,b[a].value;var c={exports:{}},d=b[a].value(null,c.exports,c);if(b[a].inited=!0,b[a].value=d,void 0!==d)return d;for(var e in c.exports)if(c.exports.hasOwnProperty(e))return b[a].inited=!0,b[a].value=c.exports,c.exports}};b[0]={value:function(){function a(a){this.formula=a}function b(a,e,f,g,i){var j,k=null,l=null,m=[],n=e.operand||[],o=null;if(f.operand=[],-1===e.name.indexOf("text")){for(var p=0,q=n.length;q>p;p++)k=n[p],k!==h?k?"string"==typeof k?(n[p]="brackets"===e.name&&2>p?k:"function"===e.name&&0===p?k:c("text",k),f.operand.push(n[p])):(f.operand.push({}),n[p]=b(a.operand[p],k,f.operand[f.operand.length-1],g,i)):(n[p]=c("empty"),f.operand.push(n[p])):(m.push(p),i.hasOwnProperty("startOffset")||(i.startOffset=p),i.endOffset=p,e.attr&&e.attr.id&&(i.groupId=e.attr.id));for(2===m.length&&(i.endOffset-=1);p=m.length;)p=m[p-1],n.splice(p,1),m.length--,a.operand.splice(p,1)}if(o=d(e.name),!o)throw new Error("operator type error: not found "+e.operator);j=function(){},j.prototype=o.prototype,l=new j,o.apply(l,n),f.func=l;for(var r in e.callFn)e.callFn.hasOwnProperty(r)&&l[r]&&l[r].apply(l,e.callFn[r]);return e.attr&&(e.attr.id&&(g[e.attr.id]={objGroup:l,strGroup:a}),e.attr["data-root"]&&(g.root={objGroup:l,strGroup:a}),l.setAttr(e.attr)),l}function c(a,b){switch(a){case"empty":return new kf.EmptyExpression;case"text":return new kf.TextExpression(b)}}function d(a){return g[a]||kf[a.replace(/^[a-z]/i,function(a){return a.toUpperCase()}).replace(/-([a-z])/gi,function(a,b){return b.toUpperCase()})+"Expression"]}function e(a){var b={};if("[object Array]"==={}.toString.call(a)){b=[];for(var c=0,d=a.length;d>c;c++)b[c]=f(a[c])}else for(var e in a)a.hasOwnProperty(e)&&(b[e]=f(a[e]));return b}function f(a){return a?"object"!=typeof a?a:e(a):a}var g={},h="\uf155";return a.prototype.generateBy=function(a){var c=a.tree,d={},f={},g={};if("string"==typeof c)throw new Error("Unhandled error");return this.formula.appendExpression(b(c,e(c),d,g,f)),{select:f,parsedTree:c,tree:d,mapping:g}},a.prototype.regenerateBy=function(a){return this.formula.clearExpressions(),this.generateBy(a)},a}},b[1]={value:function(){return{toRPNExpression:b.r(2),generateTree:b.r(3)}}},b[2]={value:function(){function a(b){var e=[],f=null;for(b=c(b);f=b.shift();)"combination"===f.name&&1===f.operand.length&&"brackets"===f.operand[0].name&&(f=f.operand[0]),e.push(d.isArray(f)?a(f):f);return e}function c(a){for(var b=[],c=null;void 0!==(c=a.pop());)if(!c||"object"!=typeof c||c.sign!==!1&&"function"!==c.name)b.push(c);else{var d=c.handler(c,[],b.reverse());b.unshift(d),b.reverse()}return b.reverse()}var d=b.r(4);return a}},b[3]={value:function(){function a(b){for(var e=null,f=[],g=0,h=b.length;h>g;g++)d.isArray(b[g])&&(b[g]=a(b[g]));for(;e=b.shift();)f.push("object"==typeof e&&e.handler?e.handler(e,f,b):e);return c(f)}var c=b.r(13),d=b.r(4);return a}},b[4]={value:function(){var a=b.r(7),c=b.r(6),d=b.r(15),e={getLatexType:function(b){return b=b.replace(/^\\/,""),a[b]?"operator":c[b]?"function":"text"},isArray:function(a){return a&&"[object Array]"===Object.prototype.toString.call(a)},getDefine:function(b){return e.extend({},a[b.replace("\\","")])},getFuncDefine:function(a){return{name:"function",params:a.replace(/^\\/,""),handler:d}},getBracketsDefine:function(b,c){return e.extend({params:[b,c]},a.brackets)},extend:function(a,b){for(var c in b)b.hasOwnProperty(c)&&(a[c]=b[c]);return a}};return e}},b[5]={value:function(){var a=!0;return{".":a,"{":a,"}":a,"[":a,"]":a,"(":a,")":a,"|":a}}},b[6]={value:function(){return{sin:1,cos:1,arccos:1,cosh:1,det:1,inf:1,limsup:1,Pr:1,tan:1,arcsin:1,cot:1,dim:1,ker:1,ln:1,sec:1,tanh:1,arctan:1,coth:1,exp:1,lg:1,log:1,arg:1,csc:1,gcd:1,lim:1,max:1,sinh:1,deg:1,hom:1,liminf:1,min:1,sup:1}}},b[7]={value:function(){var a=b.r(22),c=b.r(11);return{"^":{name:"superscript",type:c.OP,handler:a},_:{name:"subscript",type:c.OP,handler:a},frac:{name:"fraction",type:c.FN,sign:!1,handler:b.r(14)},sqrt:{name:"radical",type:c.FN,sign:!1,handler:b.r(23)},sum:{name:"summation",type:c.FN,traversal:"rtl",handler:b.r(24)},"int":{name:"integration",type:c.FN,traversal:"rtl",handler:b.r(16)},brackets:{name:"brackets",type:c.FN,handler:b.r(12)},mathcal:{name:"mathcal",type:c.FN,sign:!1,handler:b.r(19)},mathfrak:{name:"mathfrak",type:c.FN,sign:!1,handler:b.r(20)},mathbb:{name:"mathbb",type:c.FN,sign:!1,handler:b.r(18)},mathrm:{name:"mathrm",type:c.FN,sign:!1,handler:b.r(21)}}}},b[8]={value:function(){return{"int":b.r(26),quot:b.r(27)}}},b[9]={value:function(){return{combination:b.r(29),fraction:b.r(30),"function":b.r(31),integration:b.r(32),subscript:b.r(39),superscript:b.r(41),script:b.r(37),radical:b.r(38),summation:b.r(40),brackets:b.r(28),mathcal:b.r(34),mathfrak:b.r(35),mathbb:b.r(33),mathrm:b.r(36)}}},b[10]={value:function(){return{"#":1,$:1,"%":1,_:1,"&":1,"{":1,"}":1,"^":1,"~":1}}},b[11]={value:function(){return{OP:1,FN:2}}},b[12]={value:function(){var a=b.r(5);return function(b,c,d){for(var e=0,f=b.params.length;f>e;e++)if(!(b.params[e]in a))throw new Error("Brackets: invalid params");return b.operand=b.params,b.params[2]=d.shift(),delete b.handler,delete b.params,b}}},b[13]={value:function(){return function(){return{name:"combination",operand:arguments[0]||[]}}}},b[14]={value:function(){return function(a,b,c){var d=c.shift(),e=c.shift();if(void 0===d||void 0===e)throw new Error("Frac: Syntax Error");return a.operand=[d,e],delete a.handler,a}}},b[15]={value:function(){var a=b.r(17);return function(b,c,d){var e=a.exec(d);return b.operand=[b.params,e.expr,e.superscript,e.subscript],delete b.params,delete b.handler,b}}},b[16]={value:function(){var a=b.r(17),c=b.r(11).FN;return function(b,d,e){var f=e.shift(),g=a.exec(e);return g.expr&&g.expr.type===c&&g.expr.handler&&(g.expr=g.expr.handler(g.expr,d,e)),b.operand=[g.expr,g.superscript,g.subscript],b.callFn={setType:[0|f]},delete b.handler,b}}},b[17]={value:function(){function a(a){var c=b(a),d=null,e={superscript:null,subscript:null};if(!c)return e;if(d=b(a),e[c.type]=c.value||null,d){if(d.type===c.type)throw new Error("Script: syntax error!");e[d.type]=d.value||null}return e}function b(a){var b=a.shift();return b?"subscript"===b.name||"superscript"===b.name?{type:b.name,value:a.shift()}:(a.unshift(b),null):null}return{exec:function(b){var c=a(b),d=b.shift();if(d&&d.name&&-1!==d.name.indexOf("script"))throw new Error("Script: syntax error!");return c.expr=d||null,c}}}},b[18]={value:function(){return function(a,b,c){var d=c.shift();return"object"==typeof d&&"combination"===d.name&&(d=d.operand.join("")),a.name="text",a.attr={_reverse:"mathbb"},a.callFn={setFamily:["KF AMS BB"]},a.operand=[d],delete a.handler,a}}},b[19]={value:function(){return function(a,b,c){var d=c.shift();return"object"==typeof d&&"combination"===d.name&&(d=d.operand.join("")),a.name="text",a.attr={_reverse:"mathcal"},a.callFn={setFamily:["KF AMS CAL"]},a.operand=[d],delete a.handler,a}}},b[20]={value:function(){return function(a,b,c){var d=c.shift();return"object"==typeof d&&"combination"===d.name&&(d=d.operand.join("")),a.name="text",a.attr={_reverse:"mathfrak"},a.callFn={setFamily:["KF AMS FRAK"]},a.operand=[d],delete a.handler,a}}},b[21]={value:function(){return function(a,b,c){var d=c.shift();return"object"==typeof d&&"combination"===d.name&&(d=d.operand.join("")),a.name="text",a.attr={_reverse:"mathrm"},a.callFn={setFamily:["KF AMS ROMAN"]},a.operand=[d],delete a.handler,a}}},b[22]={value:function(){return function(a,b,c){var d=b.pop(),e=c.shift()||null;if(!e)throw new Error("Missing script");if(d=d||"",d.name===a.name||"script"===d.name)throw new Error("script error");return"subscript"===d.name?(d.name="script",d.operand[2]=d.operand[1],d.operand[1]=e,d):"superscript"===d.name?(d.name="script",d.operand[2]=e,d):(a.operand=[d,e],delete a.handler,a)}}},b[23]={value:function(){var a=b.r(13);return function(b,c,d){var e=d.shift(),f=null,g=null;if("["===e){for(e=[];(f=d.shift())&&"]"!==f;)e.push(f);e=0===e.length?null:a(e),g=d.shift()}else g=e,e=null;return b.operand=[g,e],delete b.handler,b}}},b[24]={value:function(){var a=b.r(17);return function(b,c,d){var e=a.exec(d);return b.operand=[e.expr,e.superscript,e.subscript],delete b.handler,b}}},b[25]={value:function(){function a(a){if(d(a))return a.substring(1);switch(m.getLatexType(a)){case"operator":return m.getDefine(a);case"function":return m.getFuncDefine(a);default:return c(a)}}function c(a){return 0===a.indexOf("\\")?a+"\\":a}function d(a){return 0===a.indexOf("\\")?!!l[a.substring(1)]:!1}function e(a){return a.replace(/\\\s+/,"").replace(/\s*([^a-z0-9\s])\s*/gi,function(a,b){return b})}var f=b.r(43).Parser,g=b.r(1),h=b.r(8),i=b.r(42),j=b.r(7),k=b.r(9),l=b.r(10),m=b.r(4),n="\ufff8",o="\ufffc",p=new RegExp(n+"|"+o,"g"),q=new RegExp(n,"g"),r=new RegExp(o,"g");f.register("latex",f.implement({parse:function(a){var b=this.split(this.format(a));return b=this.parseToGroup(b),b=this.parseToStruct(b),this.generateTree(b)},serialization:function(a,b){return i(a,b)},expand:function(a){var b=a.parse,c=null,d=a.pre,e=a.reverse;for(var f in b)b.hasOwnProperty(f)&&(c=f.replace(/\\/g,""),j[c]=b[f]);for(var f in e)e.hasOwnProperty(f)&&(k[f.replace(/\\/g,"")]=e[f]);if(d)for(var f in d)d.hasOwnProperty(f)&&(h[f.replace(/\\/g,"")]=d[f])},format:function(a){a=e(a),a=a.replace(p,"").replace(/\\{/gi,n).replace(/\\}/gi,o);for(var b in h)h.hasOwnProperty(b)&&(a=h[b](a));return a},split:function(a){var b=[],c=/(?:\\[^a-z]\s*)|(?:\\[a-z]+\s*)|(?:[{}]\s*)|(?:[^\\{}]\s*)/gi,d=/^\s+|\s+$/g,e=null;for(a=a.replace(d,"");e=c.exec(a);)e=e[0].replace(d,""),e&&b.push(e);return b},generateTree:function(a){for(var b=[],c=null;c=a.shift();)b.push(m.isArray(c)?this.generateTree(c):c);return b=g.toRPNExpression(b),g.generateTree(b)},parseToGroup:function(a){for(var b=[],c=[b],d=0,e=0,f=0,g=a.length;g>f;f++)switch(a[f]){case"{":d++,c.push(b),b.push([]),b=b[b.length-1];break;case"}":d--,b=c.pop();break;case"\\left":e++,c.push(b),b.push([[]]),b=b[b.length-1][0],b.type="brackets",f++,b.leftBrackets=a[f].replace(q,"{").replace(r,"}");break;case"\\right":e--,f++,b.rightBrackets=a[f].replace(q,"{").replace(r,"}"),b=c.pop();break;default:b.push(a[f].replace(q,"\\{").replace(r,"\\}"))}if(0!==d)throw new Error("Group Error!");if(0!==e)throw new Error("Brackets Error!");return c[0]},parseToStruct:function(b){for(var c=[],d=0,e=b.length;e>d;d++)m.isArray(b[d])?"brackets"===b[d].type?(c.push(m.getBracketsDefine(b[d].leftBrackets,b[d].rightBrackets)),c.push(this.parseToStruct(b[d]))):c.push(this.parseToStruct(b[d])):c.push(a(b[d]));return c}}))}},b[26]={value:function(){return function(a){return a.replace(/\\(i+)nt(\b|[^a-zA-Z])/g,function(a,b,c){return"\\int "+b.length+c})}}},b[27]={value:function(){return function(a){return a.replace(/``/g,"\u201c")}}},b[28]={value:function(){return function(a){return("{"===a[0]||"}"===a[0])&&(a[0]="\\"+a[0]),("{"===a[1]||"}"===a[1])&&(a[1]="\\"+a[1]),["\\left",a[0],a[2],"\\right",a[1]].join(" ")}}},b[29]={value:function(){return function(a){return this.attr["data-root"]||this.attr["data-placeholder"]?a.join(""):"{"+a.join("")+"}"}}},b[30]={value:function(){return function(a){return"\\frac "+a[0]+" "+a[1]}}},b[31]={value:function(){return function(a){var b=["\\"+a[0]];return a[2]&&b.push("^"+a[2]),a[3]&&b.push("_"+a[3]),a[1]&&b.push(" "+a[1]),b.join("")}}},b[32]={value:function(){return function(a){var b=["\\int "];if(this.callFn&&this.callFn.setType){b=["\\"];for(var c=0,d=this.callFn.setType;d>c;c++)b.push("i");b.push("nt ")}return a[1]&&b.push("^"+a[1]),a[2]&&b.push("_"+a[2]),a[0]&&b.push(" "+a[0]),b.join("")}}},b[33]={value:function(){return function(a){return"\\mathbb{"+a[0]+"}"}}},b[34]={value:function(){return function(a){return"\\mathcal{"+a[0]+"}"}}},b[35]={value:function(){return function(a){return"\\mathfrak{"+a[0]+"}"}}},b[36]={value:function(){return function(a){return"\\mathrm{"+a[0]+"}"}}},b[37]={value:function(){return function(a){return a[0]+"^"+a[1]+"_"+a[2]}}},b[38]={value:function(){return function(a){var b=["\\sqrt"];return a[1]&&b.push("["+a[1]+"]"),b.push(" "+a[0]),b.join("")}}},b[39]={value:function(){return function(a){return a[0]+"_"+a[1]}}},b[40]={value:function(){return function(a){var b=["\\sum "];return a[1]&&b.push("^"+a[1]),a[2]&&b.push("_"+a[2]),a[0]&&b.push(" "+a[0]),b.join("")}}},b[41]={value:function(){return function(a){return a[0]+"^"+a[1]}}},b[42]={value:function(){function a(b,e){var g=[],h=null,i=null;if("object"!=typeof b)return c(b)?"\\"+b+" ":b.replace(f,function(a,b){return b+" "});"combination"===b.name&&1===b.operand.length&&"combination"===b.operand[0].name&&(b=b.operand[0]),i=b.operand;for(var j=0,k=i.length;k>j;j++)g.push(i[j]?a(i[j]):i[j]);return h=b.attr&&b.attr._reverse?b.attr._reverse:b.name,d[h].call(b,g,e)}function c(a){return!!e[a]}var d=b.r(9),e=b.r(10),f=/(\\(?:[\w]+)|(?:[^a-z]))\\/gi;return function(b,c){return a(b,c)}}},b[43]={value:function(a,b,c){function d(a){this.impl=new a,this.conf={}}function e(){this.conf={}}var f={},g={},h={extend:function(a,b){var c=null;b=[].slice.call(arguments,1);for(var d=0,e=b.length;e>d;d++){c=b[d];for(var f in c)c.hasOwnProperty(f)&&(a[f]=c[f])}},setData:function(a,b,c){if("string"==typeof b)a[b]=c;else{if("object"!=typeof b)throw new Error("invalid option");for(c in b)b.hasOwnProperty(c)&&(a[c]=b[c])}}},i={use:function(a){if(!g[a])throw new Error("unknown parser type");return this.proxy(g[a])},config:function(a,b){return h.setData(f,a,b),this},register:function(a,b){return g[a.toLowerCase()]=b,this},implement:function(a){var b=function(){},c=a.constructor||function(){},d=function(){e.call(this),c.call(this)};b.prototype=e.prototype,d.prototype=new b,delete a.constructor;for(var f in a)"constructor"!==f&&a.hasOwnProperty(f)&&(d.prototype[f]=a[f]);return d},proxy:function(a){return new d(a)}};h.extend(d.prototype,{config:function(a,b){h.setData(this.conf,a,b)},set:function(a,b){this.impl.set(a,b)},parse:function(a){var b={config:{},tree:this.impl.parse(a)};return h.extend(b.config,f,this.conf),b},serialization:function(a,b){return this.impl.serialization(a,b)},expand:function(a){this.impl.expand(a)}}),h.extend(e.prototype,{set:function(a,b){h.extend(this.conf,a,b)},parse:function(){throw new Error("Abstract function")}}),c.exports={Parser:i,ParserInterface:e}}},b[44]={value:function(){var a=b.r(43).Parser;b.r(25),window.kf.Parser=a,window.kf.Assembly=b.r(0)}};var c={"kf.start":44};!function(){try{a("kf.start")}catch(b){}}(this)}();