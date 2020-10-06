(function ($) {
	function F(A) {
		var z = A.zoom, y = A.W, v = A.X;
		"body" !== z.options.appendSelector && (y -= z.a.offset().left, v -= z.a.offset().top);
		var x = A.e, w = A.g;
		this.data = A;
		this.A = this.b = null;
		this.Ea = 0;
		this.zoom = z;
		this.L = !0;
		this.s = this.interval = this.B = this.q = 0;
		var e = this, u;
		e.b = $("<div class='" + A.O + "' style='position:absolute;overflow:hidden'  ></div>");
		var s = e.zoom.A;
		s.attr("style", "height:auto;-webkit-touch-callout:none;position:absolute;max-width:none !important");
		s.attr("data-pin-no-hover", "true");
		"inside" == z.options.position && s.bind("touchstart", function (d) {
			d.preventDefault();
			return !1
		});
		z.options.variableMagnification && s.bind("mousewheel", function (B, d) {
			e.zoom.oa(0.1 * d);
			return !1
		});
		if (z.options.useParentProportions){
			x = z.parentBlock.outerWidth();
			w = z.parentBlock.outerHeight();
		}
		e.A = s;
		s.width(e.zoom.e);
		s.css(z.U, z.V);
		e.b.css(z.U, z.V);
		var t = e.b;
		t.append(s);
		var r = $("<div style='position:absolute;'></div>");
		A.caption ? ("html" == z.options.captionType ? u = A.caption : "attr" == z.options.captionType && (u = $("<div class='cloudzoom-caption'>" + A.caption + "</div>")), u.css("display", "block"), r.css({width: x}), t.append(r), r.append(u), $(z.options.appendSelector).append(t), this.s = u.outerHeight(), "bottom" == z.options.captionPosition ? r.css("top", w) : (r.css("top", 0), this.Ea = this.s)) : $(z.options.appendSelector).append(t);
		t.css({opacity: 0.1, width: x, height: parseInt(w) + parseInt(this.s)});
		this.zoom.I = "auto" === z.options.minMagnification ? Math.max(x / z.a.width(), w / z.a.height()) : z.options.minMagnification;
		this.zoom.H = "auto" === z.options.maxMagnification ? s.width() / z.a.width() : z.options.maxMagnification;
		A = t.height();
		this.L = !1;
		z.options.zoomFlyOut ? (w = z.a.offset(), w.left += z.d / 2, w.top += z.c / 2, t.offset(w), t.width(0), t.height(0), t.animate({
			left: y,
			top: v,
			width: x,
			height: A,
			opacity: 1
		}, {
			duration: z.options.animationTime, complete: function () {
				e.L = !0
			}
		})) : (t.offset({
			left: y,
			top: v
		}), t.width(x), t.height(A), t.animate({opacity: 1}, {
			duration: z.options.animationTime, complete: function () {
				e.L = !0
			}
		}))
	}

	function m(e, d, s) {
		this.a = e;
		this.Na = e[0];
		this.sa = s;
		this.Ba = !0;
		var r = this;
		e.bind("error", function () {
			r.sa(e, {va: d})
		});
		e.bind("load", function () {
			r.Ba = !1;
			r.sa(e)
		});
		this.Na.src = d
	}

	function i(r, d) {
		function v() {
			e.update();
			window.Ta(v)
		}

		function s() {
			var w;
			w = "" != d.image ? d.image : "" + r.attr("src");
			d.lazyLoadZoom ? (e.G(w, null), r.bind("touchstart.preload " + e.options.mouseTriggerEvent + ".preload", function () {
				e.a.unbind(".preload");
				e.G(null, d.zoomImage)
			})) : e.G(w, d.zoomImage)
		}

		var e = this;
		d = $.extend({}, $.fn.CloudZoom.defaults, d);
		var u = i.xa(r, $.fn.CloudZoom.attr);
		d = $.extend({}, d, u);
		1 > d.easing && (d.easing = 1);
		u = r.parent();
		u.is("a") && "" == d.zoomImage && (d.zoomImage = u.attr("href"), u.removeAttr("href"));
		this.parentBlock = u;
		u = $("<div class='" + d.zoomClass + "'</div>");
		$("body").append(u);
		this.V = "translateZ(0)";
		this.U = "-webkit-transform";
		this.ca = u.width();
		this.ba = u.height();
		d.zoomWidth && (this.ca = d.zoomWidth, this.ba = d.zoomHeight);
		u.remove();
		this.options = d;
		this.a = r;
		this.A = null;
		this.g = this.e = this.d = this.c = 0;
		this.K = this.m = null;
		this.j = this.n = 0;
		this.w = {x: 0, y: 0};
		this.Ya = this.caption = "";
		this.ga = {x: 0, y: 0};
		this.k = [];
		this.wa = 0;
		this.fa = "";
		this.b = this.D = this.C = null;
		this.na = "";
		this.ma = this.P = this.Y = this.ea = !1;
		this.l = null;
		this.id = ++i.id;
		this.M = this.Aa = this.za = 0;
		this.o = this.h = null;
		this.Ca = this.H = this.I = this.f = this.i = this.pa = 0;
		this.ua(r);
		this.ta = !1;
		this.v = 0;
		this.J = !1;
		this.la = 0;
		this.r = "";
		this.R = !1;
		this.Q = this.ha = 0;
		if (r.is(":hidden")) {
			var t = setInterval(function () {
				r.is(":hidden") || (clearInterval(t), s())
			}, 100)
		} else {
			s()
		}
		this.Ha();
		v()
	}

	function l(e, d) {
		var r = d.uriEscapeMethod;
		return "escape" == r ? escape(e) : "encodeURI" == r ? encodeURI(e) : e
	}

	function f(d) {
		return d
	}

	function k(r) {
		var e = r || window.event, v = [].slice.call(arguments, 1), s = 0, u = 0, t = 0;
		r = $.event.fix(e);
		r.type = "mousewheel";
		e.wheelDelta && (s = e.wheelDelta / 120);
		e.detail && (s = -e.detail / 3);
		t = s;
		void 0 !== e.axis && e.axis === e.HORIZONTAL_AXIS && (t = 0, u = -1 * s);
		void 0 !== e.wheelDeltaY && (t = e.wheelDeltaY / 120);
		void 0 !== e.wheelDeltaX && (u = -1 * e.wheelDeltaX / 120);
		v.unshift(r, s, u, t);
		return ($.event.dispatch || $.event.handle).apply(this, v)
	}

	var p = ["DOMMouseScroll", "mousewheel"];
	if ($.event.fixHooks) {
		for (var b = p.length; b;) {
			$.event.fixHooks[p[--b]] = $.event.mouseHooks
		}
	}
	$.event.special.mousewheel = {
		setup: function () {
			if (this.addEventListener) {
				for (var d = p.length; d;) {
					this.addEventListener(p[--d], k, !1)
				}
			} else {
				this.onmousewheel = k
			}
		}, teardown: function () {
			if (this.removeEventListener) {
				for (var d = p.length; d;) {
					this.removeEventListener(p[--d], k, !1)
				}
			} else {
				this.onmousewheel = null
			}
		}
	};
	$.fn.extend({
		mousewheel: function (d) {
			return d ? this.bind("mousewheel", d) : this.trigger("mousewheel")
		}, unmousewheel: function (d) {
			return this.unbind("mousewheel", d)
		}
	});
	window.Ta = function () {
		return window.requestAnimationFrame || window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame || window.oRequestAnimationFrame || window.msRequestAnimationFrame || function (d) {
				window.setTimeout(d, 20)
			}
	}();
	var b = document.getElementsByTagName("script"), n = b[b.length - 1].src.lastIndexOf("/");
	"undefined" != typeof window.CloudZoom || b[b.length - 1].src.slice(0, n);
	var b = window, c = b.Function, o = !0, a = !1, q = "NOTAPP", n = "LIVE".length, j = !1, g = !1;
	5 == n ? g = !0 : 4 == n && (j = false);
	i.ia = $(window).width();
	$(window).bind("resize.cloudzoom orientationchange.cloudzoom", function () {
		i.ia = $(this).width()
	});
	i.prototype.N = function () {
		return "inside" === this.options.zoomPosition || i.ia <= this.options.autoInside ? !0 : !1
	};
	i.prototype.update = function () {
		var e = this.h;
		if (this.R) {
			var d = (new Date).getTime();
			this.Q += d - this.ha;
			this.ha = d
		}
		null != e && (this.p(this.w, 0), this.f != this.i && (this.i += (this.f - this.i) / this.options.easing, 0.0001 > Math.abs(this.f - this.i) && (this.i = this.f), this.Ra()), e.update())
	};
	i.id = 0;
	i.prototype.La = function (e) {
		var d = this.na.replace(/^\/|\/$/g, "");
		if (0 == this.k.length) {
			return {href: this.options.zoomImage, title: this.a.attr("title")}
		}
		if (void 0 != e) {
			return this.k
		}
		e = [];
		for (var r = 0; r < this.k.length && this.k[r].href.replace(/^\/|\/$/g, "") != d; r++) {
		}
		for (d = 0; d < this.k.length; d++) {
			e[d] = this.k[r], r++, r >= this.k.length && (r = 0)
		}
		return e
	};
	i.prototype.getGalleryList = i.prototype.La;
	i.prototype.T = function () {
		clearTimeout(this.pa);
		null != this.o && this.o.remove()
	};
	i.prototype.G = function (e, d) {
		var s = this;
		null !== d && (this.T(), $(this.options.appendSelector).children(".cloudzoom-fade-" + s.id).remove(), null != this.D && (this.D.cancel(), this.D = null), this.na = "" != d && void 0 != d ? d : e, this.Y = !1, this.Qa());
		if (null !== e) {
			!s.options.galleryFade || !s.ea || s.N() && null != s.h || (s.l = $(new Image).css({
				position: "absolute",
				left: 0,
				top: 0
			}), s.l.attr("src", s.a.attr("src")), s.l.width(s.a.width()), s.l.height(s.a.height()), "body" === s.options.appendSelector && s.l.offset(s.a.offset()), s.l.addClass("cloudzoom-fade-" + s.id), $(s.options.appendSelector).append(s.l));
			this.P = !1;
			null != this.C && (this.C.cancel(), this.C = null);
			var r = $(new Image);
			this.C = new m(r, e, function (u, t) {
				s.C = null;
				$(s.options.appendSelector).children(".cloudzoom-fade-" + s.id).fadeOut(s.options.fadeTime, function () {
					$(this).remove();
					s.l = null
				});
				void 0 !== t ? (s.fa = "", s.T(), s.options.errorCallback({
					$element: s.a,
					type: "IMAGE_NOT_FOUND",
					data: t.va
				})) : (s.P = !0, s.a.attr("src", r.attr("src")), s.ka(), s.ya())
			})
		}
	};
	i.prototype.Qa = function () {
		var e = this, d = 250;
		e.options.lazyLoadZoom && (d = 0);
		e.pa = setTimeout(function () {
			e.o = $("<div class='cloudzoom-ajax-loader' style='position:absolute;left:0px;top:0px'/>");
			$(e.options.appendSelector).append(e.o);
			var r = e.o.width(), s = e.o.height(), r = e.a.offset().left + e.a.width() / 2 - r / 2, s = e.a.offset().top + e.a.height() / 2 - s / 2;
			"body" !== e.options.appendSelector && (r -= e.a.offset().left, s -= e.a.offset().top);
			e.o.css({left: r, top: s})
		}, d);
		this.A = d = $(new Image);
		d.attr("id", "cloudzoom-zoom-image-" + e.id);
		this.D = new m(d, this.na, function (r, s) {
			e.e = r[0].width;
			e.g = r[0].height;
			e.D = null;
			i.browser.Ka && -1 < navigator.userAgent.toLowerCase().indexOf("firefox/35") && (console.log("FF35"), r.css({
				opacity: 0,
				width: "1px",
				height: "auto",
				position: "absolute",
				top: $(window).scrollTop() + "px",
				left: $(window).scrollLeft() + "px"
			}), $("body").append(r));
			void 0 !== s ? (e.T(), e.options.errorCallback({
				$element: e.a,
				type: "IMAGE_NOT_FOUND",
				data: s.va
			})) : (e.Y = !0, e.ya())
		})
	};
	i.prototype.loadImage = i.prototype.G;
	i.prototype.Ga = function () {
		alert("Cloud Zoom API OK")
	};
	i.prototype.apiTest = i.prototype.Ga;
	i.prototype.t = function () {
		null != this.h && (this.options.touchStartDelay && (this.J = !0), this.h.da(), this.a.trigger("cloudzoom_end_zoom"));
		this.h = null
	};
	i.prototype.da = function () {
		this.Xa();
		this.a.unbind();
		null != this.b && (this.b.unbind(), this.t());
		this.a.removeData("CloudZoom");
		$(this.options.appendSelector).children(".cloudzoom-fade-" + this.id).remove();
		this.ta = !0
	};
	i.prototype.destroy = i.prototype.da;
	i.prototype.Ia = function () {
		var d = this;
		if (!d.options.hoverIntentDelay) {
			return !1
		}
		if (d.v) {
			return !0
		}
		d.v = setTimeout(function () {
			d.v = !1;
			d.F();
			d.u();
			d.p(d.w, 0)
		}, parseInt(d.options.hoverIntentDelay));
		return !0
	};
	i.prototype.$ = function () {
		var d = this, w;
		this.r = "";
		if (d.options.useParentProportions){
			w = d.parentBlock;
		}else{
			w = d.a;
		}
		w.bind(d.options.mouseTriggerEvent + ".trigger", function (e) {

			if ("touch" !== d.r && (d.r = "mouse", !d.aa() && null == d.b && !d.Ia())) {
				var r = d.options.useParentProportions ? d.parentBlock.offset : d.a.offset();
				e = new i.S(e.pageX - r.left, e.pageY - r.top);
				d.F();
				d.u();
				d.p(e, 0);
				d.w = e
			}
		})
	};
	i.prototype.aa = function () {
		if (this.ta || !this.Y || !this.P || i.ia <= this.options.disableOnScreenWidth || "touch" === this.r && this.J) {
			return !0
		}
		if (!1 === this.options.disableZoom) {
			return !1
		}
		if (!0 === this.options.disableZoom) {
			return !0
		}
		if ("auto" == this.options.disableZoom) {
			if (!isNaN(this.options.maxMagnification) && 1 < this.options.maxMagnification) {
				return !1
			}
			if (this.a.width() >= this.e) {
				return !0
			}
		}
		return !1
	};
	i.prototype.Xa = function () {
		$(document).unbind("." + this.id)
	};
	i.prototype.Ha = function () {
		var d = this;
		$(document).bind("MSPointerUp." + this.id + " pointerup." + this.id + " mouseover." + this.id + " mousemove." + this.id, function (e) {
			var s = !0, r = d.options.useParentProportions ? d.parentBlock.offset() : d.a.offset(), r = new i.S(e.pageX - Math.floor(r.left), e.pageY - Math.floor(r.top));
			if (-1 > r.x || r.x > d.d || 0 > r.y || r.y > d.c) {
				d.v && (clearTimeout(d.v), d.v = 0), s = !1, d.options.permaZoom || null === d.b || (d.b.remove(), d.t(), d.b = null)
			}
			d.ma = !1;
			if ("MSPointerUp" === e.type || "pointerup" === e.type) {
				d.ma = !0
			}
			s && (d.w = r);
			s && !d.R && (d.ha = (new Date).getTime(), d.Q = 0);
			d.R = s
		})
	};
	i.prototype.ya = function () {
		var r = this;
		if (r.Y && r.P) {
			this.ra();
			r.e = r.a.width() * this.i;
			r.g = r.a.height() * this.i;
			this.T();
			null != r.h && (r.t(), r.u(), r.K.attr("src", l(this.a.attr("src"), this.options)), r.p(r.ga, 0));
			if (!r.ea) {
				r.ea = !0;
				r.$();
				var d = 0, v = 0, s = 0, u = function (w, e) {
					return Math.sqrt((w.pageX - e.pageX) * (w.pageX - e.pageX) + (w.pageY - e.pageY) * (w.pageY - e.pageY))
				};
				r.a.css({
					"-ms-touch-action": "none",
					"-ms-user-select": "none",
					"-webkit-user-select": "none",
					"-webkit-touch-callout": "none"
				});
				var t = !1;
				r.a.bind("touchstart touchmove touchend", function (e) {
					"touchstart" == e.type && (t = !0);
					"touchmove" == e.type && (t = !1);
					"touchend" == e.type && t && (r.Da(), t = !1)
				});
				r.options.touchStartDelay && (r.J = !0, r.a.bind("touchstart touchmove touchend", function (e) {
					if (r.J) {
						r.r = "touch";
						if ("touchstart" === e.type) {
							clearTimeout(r.la), r.la = setTimeout(function () {
								r.J = !1;
								r.a.trigger(e)
							}, 100)
						} else {
							if (clearTimeout(r.la), "touchend" === e.type) {
								return r.options.propagateTouchEvents
							}
						}
						return !0
					}
				}));
				r.a.bind("touchstart touchmove touchend", function (x) {
					r.r = "touch";
					if (r.aa()) {
						return !0
					}
					var w = x.originalEvent, z = r.a.offset(), e = {x: 0, y: 0}, y = w.type;
					if ("touchend" == y && 0 == w.touches.length) {
						return r.ja(y, e), r.options.propagateTouchEvents
					}
					e = new i.S(w.touches[0].pageX - Math.floor(z.left), w.touches[0].pageY - Math.floor(z.top));
					r.w = e;
					if ("touchstart" == y && 1 == w.touches.length && null == r.b) {
						return r.ja(y, e), r.options.propagateTouchEvents
					}
					2 > d && 2 == w.touches.length && (v = r.f, s = u(w.touches[0], w.touches[1]));
					d = w.touches.length;
					2 == d && r.options.variableMagnification && (w = u(w.touches[0], w.touches[1]) / s, r.f = r.N() ? v * w : v / w, r.f < r.I && (r.f = r.I), r.f > r.H && (r.f = r.H));
					r.ja("touchmove", e);
					if (r.options.propagateTouchEvents) {
						return !0
					}
					x.preventDefault();
					x.stopPropagation();
					return x.returnValue = !1
				});
				if (r.R) {
					if (r.aa()) {
						return
					}
					r.Q > parseInt(r.options.hoverIntentDelay) && (r.F(), r.u(), r.p(r.w, 0))
				}
			}
			r.a.trigger("cloudzoom_ready")
		}
	};
	i.prototype.ja = function (e, d) {
		switch (e) {
			case"touchstart":
				if (null != this.b) {
					break
				}
				clearTimeout(this.interval);
				this.F();
				this.u();
				this.p(d, this.j / 2);
				this.update();
				break;
			case"touchend":
				clearTimeout(this.interval);
				null == this.b || this.options.permaZoom || (this.b.remove(), this.b = null, this.t());
				break;
			case"touchmove":
				null == this.b && (clearTimeout(this.interval), this.F(), this.u())
		}
	};
	i.prototype.Ra = function () {
		var e = this.i;
		if (null != this.b) {
			var d = this.h;
			this.n = d.b.width() / (this.a.width() * e) * this.a.width();
			this.j = d.b.height() / (this.a.height() * e) * this.a.height();
			this.j -= d.s / e;
			this.m.width(this.n);
			this.m.height(this.j);
			this.p(this.ga, 0)
		}
	};
	i.prototype.oa = function (d) {
		this.f += d;
		this.f < this.I && (this.f = this.I);
		this.f > this.H && (this.f = this.H)
	};
	i.prototype.ua = function (d) {
		this.caption = null;
		"attr" == this.options.captionType ? (d = d.attr(this.options.captionSource), "" != d && void 0 != d && (this.caption = d)) : "html" == this.options.captionType && (d = $(this.options.captionSource), d.length && (this.caption = d.clone(), d.css("display", "none")))
	};
	i.prototype.Ma = function (e, d) {
		if ("html" == d.captionType) {
			var r;
			r = $(d.captionSource);
			r.length && r.css("display", "none")
		}
	};
	i.prototype.ra = function () {
		this.f = this.i = "auto" === this.options.startMagnification ? this.e / this.a.width() : this.options.startMagnification
	};
	i.prototype.u = function () {
		var B = this;
		$(window).unbind("contextmenu.cloudzoom");
		B.options.touchStartDelay && $(window).bind("contextmenu.cloudzoom", function (C) {
			var d = $(C.target);
			if (d.parent().hasClass("cloudzoom-lens") || d.parent().hasClass("cloudzoom-zoom-inside")) {
				return C.preventDefault(), !1
			}
		});
		B.a.trigger("cloudzoom_start_zoom");
		this.ra();
		B.e = B.a.width() * this.i;
		B.g = B.a.height() * this.i;
		var A = this.m;
		A.css(B.U, B.V);
		var z = B.d, w = B.c, y = B.e, x = B.g, e = B.caption;
		if (B.N()) {
			A.width(B.d / B.e * B.d);
			A.height(B.c / B.g * B.c);
			A.css("display", "none");
			var v = B.options.zoomOffsetX, s = B.options.zoomOffsetY;
			B.options.autoInside && (v = s = 0);
			B.h = new F({
				zoom: B,
				W: B.a.offset().left + v,
				X: B.a.offset().top + s,
				e: B.d,
				g: B.c,
				caption: e,
				O: B.options.zoomInsideClass
			});
			B.qa(B.h.b);
			B.h.b.bind("touchmove touchstart touchend", function (d) {
				B.a.trigger(d);
				return !1
			})
		} else {
			if (isNaN(B.options.zoomPosition)) {
				v = $(B.options.zoomPosition), A.width(v.width() / B.e * B.d), A.height(v.height() / B.g * B.c), A.fadeIn(B.options.fadeTime), B.options.zoomFullSize || "full" == B.options.zoomSizeMode ? (A.width(B.d), A.height(B.c), A.css("display", "none"), B.h = new F({
					zoom: B,
					W: v.offset().left,
					X: v.offset().top,
					e: B.e,
					g: B.g,
					caption: e,
					O: B.options.zoomClass
				})) : B.h = new F({
					zoom: B,
					W: v.offset().left,
					X: v.offset().top,
					e: v.width(),
					g: v.height(),
					caption: e,
					O: B.options.zoomClass,
					Z: v
				})
			} else {
				var v = B.options.zoomOffsetX, s = B.options.zoomOffsetY, u = !1;
				if (this.options.lensWidth) {
					var r = this.options.lensWidth, t = this.options.lensHeight;
					r > z && (r = z);
					t > w && (t = w);
					A.width(r);
					A.height(t)
				}
				y *= A.width() / z;
				x *= A.height() / w;
				r = B.options.zoomSizeMode;
				if (B.options.zoomFullSize || "full" == r) {
					y = B.e, x = B.g, A.width(B.d), A.height(B.c), A.css("display", "none"), u = !0
				} else {
					if (B.options.zoomMatchSize || "image" == r) {
						A.width(B.d / B.e * B.d), A.height(B.c / B.g * B.c), y = B.d, x = B.c
					} else {
						if ("zoom" === r || this.options.zoomWidth) {
							A.width(B.ca / B.e * B.d), A.height(B.ba / B.g * B.c), y = B.ca, x = B.ba
						}
					}
				}
				z = [[z / 2 - y / 2, -x], [z - y, -x], [z, -x], [z, 0], [z, w / 2 - x / 2], [z, w - x], [z, w], [z - y, w], [z / 2 - y / 2, w], [0, w], [-y, w], [-y, w - x], [-y, w / 2 - x / 2], [-y, 0], [-y, -x], [0, -x]];
				v += z[B.options.zoomPosition][0];
				s += z[B.options.zoomPosition][1];
				u || A.fadeIn(B.options.fadeTime);
				B.h = new F({
					zoom: B,
					W: (B.options.useParentProportions ? B.parentBlock.offset().left : B.a.offset().left) + v,
					X: (B.options.useParentProportions ? B.parentBlock.offset().top : B.a.offset().top) + s,
					e: y,
					g: x,
					caption: e,
					O: B.options.zoomClass
				})
			}
		}
		B.h.q = void 0;
		B.n = A.width();
		B.j = A.height();
		this.options.variableMagnification && B.m.bind("mousewheel", function (d, C) {
			B.oa(0.1 * C);
			return !1
		})
	};
	i.prototype.Pa = function () {
		return this.h ? !0 : !1
	};
	i.prototype.isZoomOpen = i.prototype.Pa;
	i.prototype.Ja = function () {
		this.a.unbind(this.options.mouseTriggerEvent + ".trigger");
		var d = this;
		null != this.b && (this.b.remove(), this.b = null);
		this.t();
		setTimeout(function () {
			d.$()
		}, 1)
	};
	i.prototype.closeZoom = i.prototype.Ja;
	i.prototype.Da = function () {
		var d = this;
		this.a.unbind(d.options.mouseTriggerEvent + ".trigger");
		this.a.trigger("click");
		setTimeout(function () {
			d.$()
		}, 1)
	};
	i.prototype.qa = function (e) {
		var d = this;
		"mouse" === d.r && e.bind("mousedown." + d.id + " mouseup." + d.id, function (r) {
			"mousedown" === r.type ? d.Ca = (new Date).getTime() : (d.ma && (d.b && d.b.remove(), d.t(), d.b = null), 250 >= (new Date).getTime() - d.Ca && d.Da())
		})
	};
	i.prototype.F = function () {
		5 == q.length && !1 == a && (o = !0);
		var s = this, e;
		s.ka();
		s.m = $("<div class='" + s.options.lensClass + "' style='overflow:hidden;display:none;position:absolute;top:0px;left:0px;'/>");
		var u = $('<img style="-webkit-touch-callout: none;position:absolute;left:0;top:0;max-width:none !important" src="' + l(this.a.attr("src"), this.options) + '">');
		u.css(s.U, s.V);
		u.width(this.a.width());
		u.height(this.a.height());
		s.K = u;
		s.K.attr("src", l(this.a.attr("src"), this.options));
		var t = s.m;
		s.b = $("<div class='cloudzoom-blank' style='position:absolute;left:0px;top:0px'/>");
		var r = s.b;
		e = $("<div class='cloudzoom-tint' style='background-color:" + s.options.tintColor + ";width:100%;height:100%;'/>");
		e.css("opacity", s.options.tintOpacity);
		e.fadeIn(s.options.fadeTime);
		r.width(s.d);
		r.height(s.c);
		"body" === s.options.appendSelector && r.offset(s.options.useParentProportions ? s.parentBlock.offset() : s.a.offset());
		$(s.options.appendSelector).append(r);
		r.append(e);
		r.append(t);
		r.bind("touchmove touchstart touchend", function (d) {
			s.a.trigger(d);
			return !1
		});
		t.append(u);
		s.M = parseInt(t.css("borderTopWidth"), 10);
		isNaN(s.M) && (s.M = 0);
		s.qa(s.b)
	};
	i.prototype.p = function (s, r) {
		var v, u;
		this.ga = s;
		v = s.x;
		u = s.y;
		r = 0;
		this.N() && (r = 0);
		v -= (this.n) / 2 + 0;
		u -= this.j / 2 + r;
		v > this.d - this.n ? v = this.d - this.n : 0 > v && (v = 0);
		u > this.c - this.j ? u = this.c - this.j : 0 > u && (u = 0);
		var t = this.M;
		this.m.parent();
		this.m.css({left: Math.ceil(v) - t, top: Math.ceil(u) - t});
		v = -v;
		u = -u;
		if (this.options.useParentProportions) {
			v = v + this.a.position().left + parseInt(this.a.css('marginLeft'), 0);
			u = u + this.a.position().top + parseInt(this.a.css('marginTop'), 0);
		}
		this.K.css({left: Math.floor(v) + "px", top: Math.floor(u) + "px"});
		this.za = v;
		this.Aa = u
	};
	i.xa = function (s, e) {
		var w = null, v = s.attr(e);
		if ("string" == typeof v) {
			var v = $.trim(v), r = v.indexOf("{"), u = v.indexOf("}");
			u != v.length - 1 && (u = v.indexOf("};"));
			if (-1 != r && -1 != u) {
				v = v.substr(r, u - r + 1);
				try {
					w = $.parseJSON(v)
				} catch (t) {
					console.error("Invalid JSON in " + e + " attribute:" + v)
				}
			} else {
				w = (new c("return {" + v + "}"))()
			}
		}
		return w
	};
	i.S = function (e, d) {
		this.x = e;
		this.y = d
	};
	i.point = i.S;
	m.prototype.cancel = function () {
		this.a.remove();
		this.a.unbind();
		this.Ba = !1
	};
	i.Va = function () {
	};
	i.setScriptPath = i.Va;
	i.Sa = function () {
		$(function () {
			$(".cloudzoom").CloudZoom();
			$(".cloudzoom-gallery").CloudZoom()
		})
	};
	i.quickStart = i.Sa;
	i.prototype.ka = function () {
		if (this.options.useParentProportions){
			this.d = this.parentBlock.outerWidth();
			this.c = this.parentBlock.outerHeight();
		}else{
			this.d = this.a.outerWidth();
			this.c = this.a.outerHeight()
		}
	};
	i.prototype.refreshImage = i.prototype.ka;
	i.version = "3.1 rev 1507231015";
	i.Wa = function () {
		a = !0
	};
	i.Oa = function () {
		i.browser = {};
		i.browser.webkit = /webkit/.test(navigator.userAgent.toLowerCase());
		i.browser.Ka = -1 < navigator.userAgent.toLowerCase().indexOf("firefox");
		o = false
	};
	i.Ua = function (d) {
		$.fn.CloudZoom.attr = d
	};
	i.setAttr = i.Ua;
	$.fn.CloudZoom = function (d) {
		return this.each(function () {
			if ($(this).hasClass("cloudzoom-gallery")) {
				var e = i.xa($(this), $.fn.CloudZoom.attr), u = $(e.useZoom).data("CloudZoom");
				u.Ma($(this), e);
				var s = $.extend({}, u.options, e), r = $(this).parent(), t = s.zoomImage;
				r.is("a") && (t = r.attr("href"));
				u.k.push({href: t, title: $(this).attr("title"), Fa: $(this)});
				$(this).bind(s.galleryEvent, function () {
					var v;
					for (v = 0; v < u.k.length; v++) {
						u.k[v].Fa.removeClass("cloudzoom-gallery-active")
					}
					$(this).addClass("cloudzoom-gallery-active");
					if (e.image == u.fa) {
						return s.propagateGalleryEvent
					}
					u.fa = e.image;
					u.options = $.extend({}, u.options, e);
					u.ua($(this));
					var w = $(this).parent();
					w.is("a") && (e.zoomImage = w.attr("href"));
					v = "mouseover" == e.galleryEvent ? u.options.galleryHoverDelay : 1;
					clearTimeout(u.wa);
					u.wa = setTimeout(function () {
						u.G(e.image, e.zoomImage)
					}, v);
					if (w.is("a") || $(this).is("a")) {
						return s.propagateGalleryEvent
					}
				})
			} else {
				$(this).data("CloudZoom", new i($(this), d))
			}
		})
	};
	$.fn.CloudZoom.attr = "data-cloudzoom";
	$.fn.CloudZoom.defaults = {
		image: "",
		zoomImage: "",
		tintColor: "#fff",
		tintOpacity: 0.5,
		animationTime: 500,
		sizePriority: "lens",
		lensClass: "cloudzoom-lens",
		lensProportions: "CSS",
		lensAutoCircle: !1,
		innerZoom: !1,
		galleryEvent: "click",
		easeTime: 500,
		zoomSizeMode: "lens",
		useParentProportions: false,
		zoomMatchSize: !1,
		zoomPosition: 3,
		zoomOffsetX: 15,
		zoomOffsetY: 0,
		zoomFullSize: !1,
		zoomFlyOut: !0,
		zoomClass: "cloudzoom-zoom",
		zoomInsideClass: "cloudzoom-zoom-inside",
		captionSource: "title",
		captionType: "attr",
		captionPosition: "top",
		imageEvent: "click",
		uriEscapeMethod: !1,
		errorCallback: function () {
		},
		variableMagnification: !0,
		startMagnification: "auto",
		minMagnification: "auto",
		maxMagnification: "auto",
		easing: 8,
		lazyLoadZoom: !1,
		mouseTriggerEvent: "mousemove",
		disableZoom: !1,
		galleryFade: !0,
		galleryHoverDelay: 200,
		permaZoom: !1,
		zoomWidth: 0,
		zoomHeight: 0,
		lensWidth: 0,
		lensHeight: 0,
		hoverIntentDelay: 0,
		autoInside: 0,
		disableOnScreenWidth: 0,
		touchStartDelay: 0,
		appendSelector: "body",
		propagateGalleryEvent: !1,
		propagateTouchEvents: !1
	};
	F.prototype.update = function () {
		var S = this.zoom, r, v;
		this.data.Z && this.L && (r = this.data.Z.offset().left, v = this.data.Z.offset().top, this.b.css({
			left: r + "px",
			top: v + "px"
		}));
		r = S.i;
		v = -S.za + S.n / 2;
		var u = -S.Aa + S.j / 2;
		void 0 == this.q && (this.q = v, this.B = u);
		this.q += (v - this.q) / S.options.easing;
		this.B += (u - this.B) / S.options.easing;
		v = -this.q * r;
		v += S.n / 2 * r;
		var u = -this.B * r, u = u + S.j / 2 * r, t = S.a.width() * r, s = S.a.height() * r;
		if (!S.options.useParentProportions) {
			0 < v && (v = 0);
			0 < u && (u = 0);
			v + t < this.b.width() && (v += this.b.width() - (v + t));
			u + s < this.b.height() - this.s && (u += this.b.height() - this.s - (u + s));
		}
		this.A.css({left: v + "px", top: u + this.Ea + "px", width: t})
	};
	F.prototype.da = function () {
		var e = this;
		e.b.bind("touchstart", function () {
			return !1
		});
		var d = this.zoom.a.offset();
		this.zoom.options.zoomFlyOut ? this.b.animate({
			left: d.left + this.zoom.d / 2,
			top: d.top + this.zoom.c / 2,
			opacity: 0,
			width: 1,
			height: 1
		}, {
			duration: this.zoom.options.animationTime, step: function () {
				i.browser.webkit && e.b.width(e.b.width())
			}, complete: function () {
				e.b.remove()
			}
		}) : this.b.animate({opacity: 0}, {
			duration: this.zoom.options.animationTime, complete: function () {
				e.b.remove()
			}
		})
	};
	b.CloudZoom = i;
	i.Oa()
})(jQuery);