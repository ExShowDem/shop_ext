(function(w){function D(b){var f=b.zoom,g=b.W,k=b.X;"body"!==f.options.appendSelector&&(g-=f.a.offset().left,k-=f.a.offset().top);var i=b.e,j=b.g;this.data=b;this.A=this.b=null;this.Ea=0;this.zoom=f;this.L=!0;this.s=this.interval=this.B=this.q=0;var c=this,l;c.b=w("<div class='"+b.O+"' style='position:absolute;overflow:hidden'  ></div>");var n=c.zoom.A;n.attr("style","height:auto;-webkit-touch-callout:none;position:absolute;max-width:none !important");n.attr("data-pin-no-hover","true");"inside"==f.options.position&&n.bind("touchstart",function(o){o.preventDefault();return !1});f.options.variableMagnification&&n.bind("mousewheel",function(p,o){c.zoom.oa(0.1*o);return !1});if(f.options.useParentProportions){i=f.parentBlock.outerWidth();j=f.parentBlock.outerHeight()}c.A=n;n.width(c.zoom.e);n.css(f.U,f.V);c.b.css(f.U,f.V);var m=c.b;m.append(n);var a=w("<div style='position:absolute;'></div>");b.caption?("html"==f.options.captionType?l=b.caption:"attr"==f.options.captionType&&(l=w("<div class='cloudzoom-caption'>"+b.caption+"</div>")),l.css("display","block"),a.css({width:i}),m.append(a),a.append(l),w(f.options.appendSelector).append(m),this.s=l.outerHeight(),"bottom"==f.options.captionPosition?a.css("top",j):(a.css("top",0),this.Ea=this.s)):w(f.options.appendSelector).append(m);m.css({opacity:0.1,width:i,height:parseInt(j)+parseInt(this.s)});this.zoom.I="auto"===f.options.minMagnification?Math.max(i/f.a.width(),j/f.a.height()):f.options.minMagnification;this.zoom.H="auto"===f.options.maxMagnification?n.width()/f.a.width():f.options.maxMagnification;b=m.height();this.L=!1;f.options.zoomFlyOut?(j=f.a.offset(),j.left+=f.d/2,j.top+=f.c/2,m.offset(j),m.width(0),m.height(0),m.animate({left:g,top:k,width:i,height:b,opacity:1},{duration:f.options.animationTime,complete:function(){c.L=!0}})):(m.offset({left:g,top:k}),m.width(i),m.height(b),m.animate({opacity:1},{duration:f.options.animationTime,complete:function(){c.L=!0}}))}function s(c,f,a){this.a=c;this.Na=c[0];this.sa=a;this.Ba=!0;var b=this;c.bind("error",function(){b.sa(c,{va:f})});c.bind("load",function(){b.Ba=!1;b.sa(c)});this.Na.src=f}function x(g,j){function a(){i.update();window.Ta(a)}function f(){var k;k=""!=j.image?j.image:""+g.attr("src");j.lazyLoadZoom?(i.G(k,null),g.bind("touchstart.preload "+i.options.mouseTriggerEvent+".preload",function(){i.a.unbind(".preload");i.G(null,j.zoomImage)})):i.G(k,j.zoomImage)}var i=this;j=w.extend({},w.fn.CloudZoom.defaults,j);var b=x.xa(g,w.fn.CloudZoom.attr);j=w.extend({},j,b);1>j.easing&&(j.easing=1);b=g.parent();b.is("a")&&""==j.zoomImage&&(j.zoomImage=b.attr("href"),b.removeAttr("href"));this.parentBlock=b;b=w("<div class='"+j.zoomClass+"'</div>");w("body").append(b);this.V="translateZ(0)";this.U="-webkit-transform";this.ca=b.width();this.ba=b.height();j.zoomWidth&&(this.ca=j.zoomWidth,this.ba=j.zoomHeight);b.remove();this.options=j;this.a=g;this.A=null;this.g=this.e=this.d=this.c=0;this.K=this.m=null;this.j=this.n=0;this.w={x:0,y:0};this.Ya=this.caption="";this.ga={x:0,y:0};this.k=[];this.wa=0;this.fa="";this.b=this.D=this.C=null;this.na="";this.ma=this.P=this.Y=this.ea=!1;this.l=null;this.id=++x.id;this.M=this.Aa=this.za=0;this.o=this.h=null;this.Ca=this.H=this.I=this.f=this.i=this.pa=0;this.ua(g);this.ta=!1;this.v=0;this.J=!1;this.la=0;this.r="";this.R=!1;this.Q=this.ha=0;if(g.is(":hidden")){var c=setInterval(function(){g.is(":hidden")||(clearInterval(c),f())},100)}else{f()}this.Ha();a()}function t(b,c){var a=c.uriEscapeMethod;return"escape"==a?escape(b):"encodeURI"==a?encodeURI(b):b}function z(a){return a}function u(g){var i=g||window.event,a=[].slice.call(arguments,1),f=0,b=0,c=0;g=w.event.fix(i);g.type="mousewheel";i.wheelDelta&&(f=i.wheelDelta/120);i.detail&&(f=-i.detail/3);c=f;void 0!==i.axis&&i.axis===i.HORIZONTAL_AXIS&&(c=0,b=-1*f);void 0!==i.wheelDeltaY&&(c=i.wheelDeltaY/120);void 0!==i.wheelDeltaX&&(b=-1*i.wheelDeltaX/120);a.unshift(g,f,b,c);return(w.event.dispatch||w.event.handle).apply(this,a)}var e=["DOMMouseScroll","mousewheel"];if(w.event.fixHooks){for(var B=e.length;B;){w.event.fixHooks[e[--B]]=w.event.mouseHooks}}w.event.special.mousewheel={setup:function(){if(this.addEventListener){for(var a=e.length;a;){this.addEventListener(e[--a],u,!1)}}else{this.onmousewheel=u}},teardown:function(){if(this.removeEventListener){for(var a=e.length;a;){this.removeEventListener(e[--a],u,!1)}}else{this.onmousewheel=null}}};w.fn.extend({mousewheel:function(a){return a?this.bind("mousewheel",a):this.trigger("mousewheel")},unmousewheel:function(a){return this.unbind("mousewheel",a)}});window.Ta=function(){return window.requestAnimationFrame||window.webkitRequestAnimationFrame||window.mozRequestAnimationFrame||window.oRequestAnimationFrame||window.msRequestAnimationFrame||function(a){window.setTimeout(a,20)}}();var B=document.getElementsByTagName("script"),r=B[B.length-1].src.lastIndexOf("/");"undefined"!=typeof window.CloudZoom||B[B.length-1].src.slice(0,r);var B=window,A=B.Function,h=!0,C=!1,d="NOTAPP",r="LIVE".length,v=!1,y=!1;5==r?y=!0:4==r&&(v=false);x.ia=w(window).width();w(window).bind("resize.cloudzoom orientationchange.cloudzoom",function(){x.ia=w(this).width()});x.prototype.N=function(){return"inside"===this.options.zoomPosition||x.ia<=this.options.autoInside?!0:!1};x.prototype.update=function(){var a=this.h;if(this.R){var b=(new Date).getTime();this.Q+=b-this.ha;this.ha=b}null!=a&&(this.p(this.w,0),this.f!=this.i&&(this.i+=(this.f-this.i)/this.options.easing,0.0001>Math.abs(this.f-this.i)&&(this.i=this.f),this.Ra()),a.update())};x.id=0;x.prototype.La=function(b){var c=this.na.replace(/^\/|\/$/g,"");if(0==this.k.length){return{href:this.options.zoomImage,title:this.a.attr("title")}}if(void 0!=b){return this.k}b=[];for(var a=0;a<this.k.length&&this.k[a].href.replace(/^\/|\/$/g,"")!=c;a++){}for(c=0;c<this.k.length;c++){b[c]=this.k[a],a++,a>=this.k.length&&(a=0)}return b};x.prototype.getGalleryList=x.prototype.La;x.prototype.T=function(){clearTimeout(this.pa);null!=this.o&&this.o.remove()};x.prototype.G=function(c,f){var a=this;null!==f&&(this.T(),w(this.options.appendSelector).children(".cloudzoom-fade-"+a.id).remove(),null!=this.D&&(this.D.cancel(),this.D=null),this.na=""!=f&&void 0!=f?f:c,this.Y=!1,this.Qa());if(null!==c){!a.options.galleryFade||!a.ea||a.N()&&null!=a.h||(a.l=w(new Image).css({position:"absolute",left:0,top:0}),a.l.attr("src",a.a.attr("src")),a.l.width(a.a.width()),a.l.height(a.a.height()),"body"===a.options.appendSelector&&a.l.offset(a.a.offset()),a.l.addClass("cloudzoom-fade-"+a.id),w(a.options.appendSelector).append(a.l));this.P=!1;null!=this.C&&(this.C.cancel(),this.C=null);var b=w(new Image);this.C=new s(b,c,function(g,i){a.C=null;w(a.options.appendSelector).children(".cloudzoom-fade-"+a.id).fadeOut(a.options.fadeTime,function(){w(this).remove();a.l=null});void 0!==i?(a.fa="",a.T(),a.options.errorCallback({$element:a.a,type:"IMAGE_NOT_FOUND",data:i.va})):(a.P=!0,a.a.attr("src",b.attr("src")),a.ka(),a.ya())})}};x.prototype.Qa=function(){var a=this,b=250;a.options.lazyLoadZoom&&(b=0);a.pa=setTimeout(function(){a.o=w("<div class='cloudzoom-ajax-loader' style='position:absolute;left:0px;top:0px'/>");w(a.options.appendSelector).append(a.o);var f=a.o.width(),c=a.o.height(),f=a.a.offset().left+a.a.width()/2-f/2,c=a.a.offset().top+a.a.height()/2-c/2;"body"!==a.options.appendSelector&&(f-=a.a.offset().left,c-=a.a.offset().top);a.o.css({left:f,top:c})},b);this.A=b=w(new Image);b.attr("id","cloudzoom-zoom-image-"+a.id);this.D=new s(b,this.na,function(f,c){a.e=f[0].width;a.g=f[0].height;a.D=null;x.browser.Ka&&-1<navigator.userAgent.toLowerCase().indexOf("firefox/35")&&(console.log("FF35"),f.css({opacity:0,width:"1px",height:"auto",position:"absolute",top:w(window).scrollTop()+"px",left:w(window).scrollLeft()+"px"}),w("body").append(f));void 0!==c?(a.T(),a.options.errorCallback({$element:a.a,type:"IMAGE_NOT_FOUND",data:c.va})):(a.Y=!0,a.ya())})};x.prototype.loadImage=x.prototype.G;x.prototype.Ga=function(){alert("Cloud Zoom API OK")};x.prototype.apiTest=x.prototype.Ga;x.prototype.t=function(){null!=this.h&&(this.options.touchStartDelay&&(this.J=!0),this.h.da(),this.a.trigger("cloudzoom_end_zoom"));this.h=null};x.prototype.da=function(){this.Xa();this.a.unbind();null!=this.b&&(this.b.unbind(),this.t());this.a.removeData("CloudZoom");w(this.options.appendSelector).children(".cloudzoom-fade-"+this.id).remove();this.ta=!0};x.prototype.destroy=x.prototype.da;x.prototype.Ia=function(){var a=this;if(!a.options.hoverIntentDelay){return !1}if(a.v){return !0}a.v=setTimeout(function(){a.v=!1;a.F();a.u();a.p(a.w,0)},parseInt(a.options.hoverIntentDelay));return !0};x.prototype.$=function(){var b=this,a;this.r="";if(b.options.useParentProportions){a=b.parentBlock}else{a=b.a}a.bind(b.options.mouseTriggerEvent+".trigger",function(f){if("touch"!==b.r&&(b.r="mouse",!b.aa()&&null==b.b&&!b.Ia())){var c=b.options.useParentProportions?b.parentBlock.offset:b.a.offset();f=new x.S(f.pageX-c.left,f.pageY-c.top);b.F();b.u();b.p(f,0);b.w=f}})};x.prototype.aa=function(){if(this.ta||!this.Y||!this.P||x.ia<=this.options.disableOnScreenWidth||"touch"===this.r&&this.J){return !0}if(!1===this.options.disableZoom){return !1}if(!0===this.options.disableZoom){return !0}if("auto"==this.options.disableZoom){if(!isNaN(this.options.maxMagnification)&&1<this.options.maxMagnification){return !1}if(this.a.width()>=this.e){return !0}}return !1};x.prototype.Xa=function(){w(document).unbind("."+this.id)};x.prototype.Ha=function(){var a=this;w(document).bind("MSPointerUp."+this.id+" pointerup."+this.id+" mouseover."+this.id+" mousemove."+this.id,function(f){var b=!0,c=a.options.useParentProportions?a.parentBlock.offset():a.a.offset(),c=new x.S(f.pageX-Math.floor(c.left),f.pageY-Math.floor(c.top));if(-1>c.x||c.x>a.d||0>c.y||c.y>a.c){a.v&&(clearTimeout(a.v),a.v=0),b=!1,a.options.permaZoom||null===a.b||(a.b.remove(),a.t(),a.b=null)}a.ma=!1;if("MSPointerUp"===f.type||"pointerup"===f.type){a.ma=!0}b&&(a.w=c);b&&!a.R&&(a.ha=(new Date).getTime(),a.Q=0);a.R=b})};x.prototype.ya=function(){var g=this;if(g.Y&&g.P){this.ra();g.e=g.a.width()*this.i;g.g=g.a.height()*this.i;this.T();null!=g.h&&(g.t(),g.u(),g.K.attr("src",t(this.a.attr("src"),this.options)),g.p(g.ga,0));if(!g.ea){g.ea=!0;g.$();var i=0,a=0,f=0,b=function(j,k){return Math.sqrt((j.pageX-k.pageX)*(j.pageX-k.pageX)+(j.pageY-k.pageY)*(j.pageY-k.pageY))};g.a.css({"-ms-touch-action":"none","-ms-user-select":"none","-webkit-user-select":"none","-webkit-touch-callout":"none"});var c=!1;g.a.bind("touchstart touchmove touchend",function(j){"touchstart"==j.type&&(c=!0);"touchmove"==j.type&&(c=!1);"touchend"==j.type&&c&&(g.Da(),c=!1)});g.options.touchStartDelay&&(g.J=!0,g.a.bind("touchstart touchmove touchend",function(j){if(g.J){g.r="touch";if("touchstart"===j.type){clearTimeout(g.la),g.la=setTimeout(function(){g.J=!1;g.a.trigger(j)},100)}else{if(clearTimeout(g.la),"touchend"===j.type){return g.options.propagateTouchEvents}}return !0}}));g.a.bind("touchstart touchmove touchend",function(j){g.r="touch";if(g.aa()){return !0}var k=j.originalEvent,m=g.a.offset(),l={x:0,y:0},n=k.type;if("touchend"==n&&0==k.touches.length){return g.ja(n,l),g.options.propagateTouchEvents}l=new x.S(k.touches[0].pageX-Math.floor(m.left),k.touches[0].pageY-Math.floor(m.top));g.w=l;if("touchstart"==n&&1==k.touches.length&&null==g.b){return g.ja(n,l),g.options.propagateTouchEvents}2>i&&2==k.touches.length&&(a=g.f,f=b(k.touches[0],k.touches[1]));i=k.touches.length;2==i&&g.options.variableMagnification&&(k=b(k.touches[0],k.touches[1])/f,g.f=g.N()?a*k:a/k,g.f<g.I&&(g.f=g.I),g.f>g.H&&(g.f=g.H));g.ja("touchmove",l);if(g.options.propagateTouchEvents){return !0}j.preventDefault();j.stopPropagation();return j.returnValue=!1});if(g.R){if(g.aa()){return}g.Q>parseInt(g.options.hoverIntentDelay)&&(g.F(),g.u(),g.p(g.w,0))}}g.a.trigger("cloudzoom_ready")}};x.prototype.ja=function(a,b){switch(a){case"touchstart":if(null!=this.b){break}clearTimeout(this.interval);this.F();this.u();this.p(b,this.j/2);this.update();break;case"touchend":clearTimeout(this.interval);null==this.b||this.options.permaZoom||(this.b.remove(),this.b=null,this.t());break;case"touchmove":null==this.b&&(clearTimeout(this.interval),this.F(),this.u())}};x.prototype.Ra=function(){var a=this.i;if(null!=this.b){var b=this.h;this.n=b.b.width()/(this.a.width()*a)*this.a.width();this.j=b.b.height()/(this.a.height()*a)*this.a.height();this.j-=b.s/a;this.m.width(this.n);this.m.height(this.j);this.p(this.ga,0)}};x.prototype.oa=function(a){this.f+=a;this.f<this.I&&(this.f=this.I);this.f>this.H&&(this.f=this.H)};x.prototype.ua=function(a){this.caption=null;"attr"==this.options.captionType?(a=a.attr(this.options.captionSource),""!=a&&void 0!=a&&(this.caption=a)):"html"==this.options.captionType&&(a=w(this.options.captionSource),a.length&&(this.caption=a.clone(),a.css("display","none")))};x.prototype.Ma=function(b,c){if("html"==c.captionType){var a;a=w(c.captionSource);a.length&&a.css("display","none")}};x.prototype.ra=function(){this.f=this.i="auto"===this.options.startMagnification?this.e/this.a.width():this.options.startMagnification};x.prototype.u=function(){var b=this;w(window).unbind("contextmenu.cloudzoom");b.options.touchStartDelay&&w(window).bind("contextmenu.cloudzoom",function(q){var p=w(q.target);if(p.parent().hasClass("cloudzoom-lens")||p.parent().hasClass("cloudzoom-zoom-inside")){return q.preventDefault(),!1}});b.a.trigger("cloudzoom_start_zoom");this.ra();b.e=b.a.width()*this.i;b.g=b.a.height()*this.i;var c=this.m;c.css(b.U,b.V);var g=b.d,k=b.c,i=b.e,j=b.g,f=b.caption;if(b.N()){c.width(b.d/b.e*b.d);c.height(b.c/b.g*b.c);c.css("display","none");var l=b.options.zoomOffsetX,o=b.options.zoomOffsetY;b.options.autoInside&&(l=o=0);b.h=new D({zoom:b,W:b.a.offset().left+l,X:b.a.offset().top+o,e:b.d,g:b.c,caption:f,O:b.options.zoomInsideClass});b.qa(b.h.b);b.h.b.bind("touchmove touchstart touchend",function(p){b.a.trigger(p);return !1})}else{if(isNaN(b.options.zoomPosition)){l=w(b.options.zoomPosition),c.width(l.width()/b.e*b.d),c.height(l.height()/b.g*b.c),c.fadeIn(b.options.fadeTime),b.options.zoomFullSize||"full"==b.options.zoomSizeMode?(c.width(b.d),c.height(b.c),c.css("display","none"),b.h=new D({zoom:b,W:l.offset().left,X:l.offset().top,e:b.e,g:b.g,caption:f,O:b.options.zoomClass})):b.h=new D({zoom:b,W:l.offset().left,X:l.offset().top,e:l.width(),g:l.height(),caption:f,O:b.options.zoomClass,Z:l})}else{var l=b.options.zoomOffsetX,o=b.options.zoomOffsetY,m=!1;if(this.options.lensWidth){var a=this.options.lensWidth,n=this.options.lensHeight;a>g&&(a=g);n>k&&(n=k);c.width(a);c.height(n)}i*=c.width()/g;j*=c.height()/k;a=b.options.zoomSizeMode;if(b.options.zoomFullSize||"full"==a){i=b.e,j=b.g,c.width(b.d),c.height(b.c),c.css("display","none"),m=!0}else{if(b.options.zoomMatchSize||"image"==a){c.width(b.d/b.e*b.d),c.height(b.c/b.g*b.c),i=b.d,j=b.c}else{if("zoom"===a||this.options.zoomWidth){c.width(b.ca/b.e*b.d),c.height(b.ba/b.g*b.c),i=b.ca,j=b.ba}}}g=[[g/2-i/2,-j],[g-i,-j],[g,-j],[g,0],[g,k/2-j/2],[g,k-j],[g,k],[g-i,k],[g/2-i/2,k],[0,k],[-i,k],[-i,k-j],[-i,k/2-j/2],[-i,0],[-i,-j],[0,-j]];l+=g[b.options.zoomPosition][0];o+=g[b.options.zoomPosition][1];m||c.fadeIn(b.options.fadeTime);b.h=new D({zoom:b,W:(b.options.useParentProportions?b.parentBlock.offset().left:b.a.offset().left)+l,X:(b.options.useParentProportions?b.parentBlock.offset().top:b.a.offset().top)+o,e:i,g:j,caption:f,O:b.options.zoomClass})}}b.h.q=void 0;b.n=c.width();b.j=c.height();this.options.variableMagnification&&b.m.bind("mousewheel",function(q,p){b.oa(0.1*p);return !1})};x.prototype.Pa=function(){return this.h?!0:!1};x.prototype.isZoomOpen=x.prototype.Pa;x.prototype.Ja=function(){this.a.unbind(this.options.mouseTriggerEvent+".trigger");var a=this;null!=this.b&&(this.b.remove(),this.b=null);this.t();setTimeout(function(){a.$()},1)};x.prototype.closeZoom=x.prototype.Ja;x.prototype.Da=function(){var a=this;this.a.unbind(a.options.mouseTriggerEvent+".trigger");this.a.trigger("click");setTimeout(function(){a.$()},1)};x.prototype.qa=function(a){var b=this;"mouse"===b.r&&a.bind("mousedown."+b.id+" mouseup."+b.id,function(c){"mousedown"===c.type?b.Ca=(new Date).getTime():(b.ma&&(b.b&&b.b.remove(),b.t(),b.b=null),250>=(new Date).getTime()-b.Ca&&b.Da())})};x.prototype.F=function(){5==d.length&&!1==C&&(h=!0);var c=this,g;c.ka();c.m=w("<div class='"+c.options.lensClass+"' style='overflow:hidden;display:none;position:absolute;top:0px;left:0px;'/>");var a=w('<img style="-webkit-touch-callout: none;position:absolute;left:0;top:0;max-width:none !important" src="'+t(this.a.attr("src"),this.options)+'">');a.css(c.U,c.V);a.width(this.a.width());a.height(this.a.height());c.K=a;c.K.attr("src",t(this.a.attr("src"),this.options));var b=c.m;c.b=w("<div class='cloudzoom-blank' style='position:absolute;left:0px;top:0px'/>");var f=c.b;g=w("<div class='cloudzoom-tint' style='background-color:"+c.options.tintColor+";width:100%;height:100%;'/>");g.css("opacity",c.options.tintOpacity);g.fadeIn(c.options.fadeTime);f.width(c.d);f.height(c.c);"body"===c.options.appendSelector&&f.offset(c.options.useParentProportions?c.parentBlock.offset():c.a.offset());w(c.options.appendSelector).append(f);f.append(g);f.append(b);f.bind("touchmove touchstart touchend",function(i){c.a.trigger(i);return !1});b.append(a);c.M=parseInt(b.css("borderTopWidth"),10);isNaN(c.M)&&(c.M=0);c.qa(c.b)};x.prototype.p=function(f,g){var a,b;this.ga=f;a=f.x;b=f.y;g=0;this.N()&&(g=0);a-=(this.n)/2+0;b-=this.j/2+g;a>this.d-this.n?a=this.d-this.n:0>a&&(a=0);b>this.c-this.j?b=this.c-this.j:0>b&&(b=0);var c=this.M;this.m.parent();this.m.css({left:Math.ceil(a)-c,top:Math.ceil(b)-c});a=-a;b=-b;if(this.options.useParentProportions){a=a+this.a.position().left+parseInt(this.a.css("marginLeft"),0);b=b+this.a.position().top+parseInt(this.a.css("marginTop"),0)}this.K.css({left:Math.floor(a)+"px",top:Math.floor(b)+"px"});this.za=a;this.Aa=b};x.xa=function(g,j){var a=null,b=g.attr(j);if("string"==typeof b){var b=w.trim(b),i=b.indexOf("{"),c=b.indexOf("}");c!=b.length-1&&(c=b.indexOf("};"));if(-1!=i&&-1!=c){b=b.substr(i,c-i+1);try{a=w.parseJSON(b)}catch(f){console.error("Invalid JSON in "+j+" attribute:"+b)}}else{a=(new A("return {"+b+"}"))()}}return a};x.S=function(a,b){this.x=a;this.y=b};x.point=x.S;s.prototype.cancel=function(){this.a.remove();this.a.unbind();this.Ba=!1};x.Va=function(){};x.setScriptPath=x.Va;x.Sa=function(){w(function(){w(".cloudzoom").CloudZoom();w(".cloudzoom-gallery").CloudZoom()})};x.quickStart=x.Sa;x.prototype.ka=function(){if(this.options.useParentProportions){this.d=this.parentBlock.outerWidth();this.c=this.parentBlock.outerHeight()}else{this.d=this.a.outerWidth();this.c=this.a.outerHeight()}};x.prototype.refreshImage=x.prototype.ka;x.version="3.1 rev 1507231015";x.Wa=function(){C=!0};x.Oa=function(){x.browser={};x.browser.webkit=/webkit/.test(navigator.userAgent.toLowerCase());x.browser.Ka=-1<navigator.userAgent.toLowerCase().indexOf("firefox");h=false};x.Ua=function(a){w.fn.CloudZoom.attr=a};x.setAttr=x.Ua;w.fn.CloudZoom=function(a){return this.each(function(){if(w(this).hasClass("cloudzoom-gallery")){var i=x.xa(w(this),w.fn.CloudZoom.attr),b=w(i.useZoom).data("CloudZoom");b.Ma(w(this),i);var f=w.extend({},b.options,i),g=w(this).parent(),c=f.zoomImage;g.is("a")&&(c=g.attr("href"));b.k.push({href:c,title:w(this).attr("title"),Fa:w(this)});w(this).bind(f.galleryEvent,function(){var k;for(k=0;k<b.k.length;k++){b.k[k].Fa.removeClass("cloudzoom-gallery-active")}w(this).addClass("cloudzoom-gallery-active");if(i.image==b.fa){return f.propagateGalleryEvent}b.fa=i.image;b.options=w.extend({},b.options,i);b.ua(w(this));var j=w(this).parent();j.is("a")&&(i.zoomImage=j.attr("href"));k="mouseover"==i.galleryEvent?b.options.galleryHoverDelay:1;clearTimeout(b.wa);b.wa=setTimeout(function(){b.G(i.image,i.zoomImage)},k);if(j.is("a")||w(this).is("a")){return f.propagateGalleryEvent}})}else{w(this).data("CloudZoom",new x(w(this),a))}})};w.fn.CloudZoom.attr="data-cloudzoom";w.fn.CloudZoom.defaults={image:"",zoomImage:"",tintColor:"#fff",tintOpacity:0.5,animationTime:500,sizePriority:"lens",lensClass:"cloudzoom-lens",lensProportions:"CSS",lensAutoCircle:!1,innerZoom:!1,galleryEvent:"click",easeTime:500,zoomSizeMode:"lens",useParentProportions:false,zoomMatchSize:!1,zoomPosition:3,zoomOffsetX:15,zoomOffsetY:0,zoomFullSize:!1,zoomFlyOut:!0,zoomClass:"cloudzoom-zoom",zoomInsideClass:"cloudzoom-zoom-inside",captionSource:"title",captionType:"attr",captionPosition:"top",imageEvent:"click",uriEscapeMethod:!1,errorCallback:function(){},variableMagnification:!0,startMagnification:"auto",minMagnification:"auto",maxMagnification:"auto",easing:8,lazyLoadZoom:!1,mouseTriggerEvent:"mousemove",disableZoom:!1,galleryFade:!0,galleryHoverDelay:200,permaZoom:!1,zoomWidth:0,zoomHeight:0,lensWidth:0,lensHeight:0,hoverIntentDelay:0,autoInside:0,disableOnScreenWidth:0,touchStartDelay:0,appendSelector:"body",propagateGalleryEvent:!1,propagateTouchEvents:!1};D.prototype.update=function(){var f=this.zoom,i,a;this.data.Z&&this.L&&(i=this.data.Z.offset().left,a=this.data.Z.offset().top,this.b.css({left:i+"px",top:a+"px"}));i=f.i;a=-f.za+f.n/2;var b=-f.Aa+f.j/2;void 0==this.q&&(this.q=a,this.B=b);this.q+=(a-this.q)/f.options.easing;this.B+=(b-this.B)/f.options.easing;a=-this.q*i;a+=f.n/2*i;var b=-this.B*i,b=b+f.j/2*i,c=f.a.width()*i,g=f.a.height()*i;if(!f.options.useParentProportions){0<a&&(a=0);0<b&&(b=0);a+c<this.b.width()&&(a+=this.b.width()-(a+c));b+g<this.b.height()-this.s&&(b+=this.b.height()-this.s-(b+g))}this.A.css({left:a+"px",top:b+this.Ea+"px",width:c})};D.prototype.da=function(){var a=this;a.b.bind("touchstart",function(){return !1});var b=this.zoom.a.offset();this.zoom.options.zoomFlyOut?this.b.animate({left:b.left+this.zoom.d/2,top:b.top+this.zoom.c/2,opacity:0,width:1,height:1},{duration:this.zoom.options.animationTime,step:function(){x.browser.webkit&&a.b.width(a.b.width())},complete:function(){a.b.remove()}}):this.b.animate({opacity:0},{duration:this.zoom.options.animationTime,complete:function(){a.b.remove()}})};B.CloudZoom=x;x.Oa()})(jQuery);