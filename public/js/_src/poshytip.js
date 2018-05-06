/*
 * Poshy Tip jQuery plugin v1.2+
 * http://vadikom.com/tools/poshy-tip-jquery-plugin-for-stylish-tooltips/
 * Copyright 2010-2013, Vasil Dinkov, http://vadikom.com/
 */

 (function ($) {
    var tips = [],
        reBgImage = /^url\(["']?([^"'\)]*)["']?\);?$/i,
        rePNG = /\.png$/i,
		ie6 = !!window.createPopup && document.documentElement.currentStyle.minWidth == 'undefined';

    // make sure the tips' position is updated on resize
    function handleWindowResize() {
        $.each(tips, function() {
            this.refresh(true);
        });
    }
    $(window).resize(handleWindowResize);

    $.Poshytip = function(elm, options) {
        this.$elm = $(elm);
        this.opts = $.extend({}, $.fn.poshytip.defaults, options);
        var idNameHtml = (('' != this.opts.idName) ? ('id='+this.opts.idName) : '');
        this.$tip = $(['<div ', idNameHtml,' class="',this.opts.className,' popover">',
                '<div class="arrow"></div>',
                '<div class="popover-content"></div>',
                // '<div class="tip-arrow tip-arrow-top tip-arrow-right tip-arrow-bottom tip-arrow-left"></div>',
			'</div>'].join(''));
        // this.$arrow = this.$tip.find('div.tip-arrow');
        this.$inner = this.$tip.find('div.popover-content');
        this.disabled = false;
        this.content = null;
        this.init();
    };

    $.Poshytip.hideAll = function() {
        $.each(tips, function() {
            this.hide();
        });
    };

    $.Poshytip.prototype = {
        init: function() {
            tips.push(this);

            // save the original title and a reference to the Poshytip object
            var title = this.$elm.attr('title');
            this.$elm.data('title.poshytip', title !== undefined ? title : null)
                .data('poshytip', this);

            // hook element events
            if (this.opts.showOn != 'none') {
                this.$elm.on({
                    // 'click.poshytip': $.proxy(this.mouseenter, this),
                    'mouseenter.poshytip': $.proxy(this.mouseenter, this),
                    'mouseleave.poshytip': $.proxy(this.mouseleave, this),
                    'touchstart.poshytip': $.proxy(this.mouseenter, this),
                    'touchend.poshytip'  : $.proxy(this.mouseleave, this),
                });
                switch (this.opts.showOn) {
                    case 'hover':
                        if (this.opts.alignTo == 'cursor')
                            this.$elm.on('mousemove.poshytip', $.proxy(this.mousemove, this));
                        if (this.opts.allowTipHover)
                            this.$tip.hover($.proxy(this.clearTimeouts, this), $.proxy(this.mouseleave, this));

                        // this.$tip.on("click",$.proxy(this.clearTimeouts, this));

                        break;
                    case 'focus':
                        this.$elm.on({
                            'focus.poshytip': $.proxy(this.showDelayed, this),
                            'blur.poshytip': $.proxy(this.hideDelayed, this)
                        });
                        break;
                }
            }
        },
        mouseenter: function(e) {
            e.preventDefault();

            if (this.disabled)
                return true;

            //this.updateCursorPos(e);

            this.$elm.attr('title', '');
            if (this.opts.showOn == 'focus')
                return true;

            this.showDelayed();
        },
        mouseleave: function(e) {
            if (this.disabled || this.asyncAnimating && (this.$tip[0] === e.relatedTarget || jQuery.contains(this.$tip[0], e.relatedTarget)))
                return true;

            if (!this.$tip.data('active')) {
                var title = this.$elm.data('title.poshytip');
                if (title !== null)
                    this.$elm.attr('title', title);
            }
            if (this.opts.showOn == 'focus')
                return true;

            this.hideDelayed();
        },
        mousemove: function(e) {
            if (this.disabled)
                return true;

			this.eventX = e.pageX;
			this.eventY = e.pageY;
            if (this.opts.followCursor && this.$tip.data('active')) {
                this.calcPos();
                this.$tip.css({left: this.pos.l, top: this.pos.t});
                // if (this.pos.arrow)
                    // this.$arrow[0].className = 'tip-arrow tip-arrow-' + this.pos.arrow;
            }
        },
        show: function() {
            if (this.disabled || this.$tip.data('active'))
                return;

            this.reset();
            this.update();

            // don't proceed if we didn't get any content in update() (e.g. the element has an empty title attribute)
            if (!this.content)
                return;

            this.display();
            if (this.opts.timeOnScreen)
                this.hideDelayed(this.opts.timeOnScreen);
        },
        showDelayed: function(timeout) {
            this.clearTimeouts();
            this.showTimeout = setTimeout($.proxy(this.show, this), typeof timeout == 'number' ? timeout : this.opts.showTimeout);
        },
        hide: function() {
            if (this.disabled || !this.$tip.data('active'))
                return;

            this.display(true);
        },
        hideDelayed: function(timeout) {
            this.clearTimeouts();
            this.hideTimeout = setTimeout($.proxy(this.hide, this), typeof timeout == 'number' ? timeout : this.opts.hideTimeout);
        },
        reset: function() {
            this.$tip.queue([]).detach().css('visibility', 'hidden').data('active', false);
            this.$inner.find('*').poshytip('hide');
            if (this.opts.fade)
                this.$tip.css('opacity', this.opacity);
            // this.$arrow[0].className = 'tip-arrow tip-arrow-top tip-arrow-right tip-arrow-bottom tip-arrow-left';
            this.asyncAnimating = false;
        },
        update: function(content, dontOverwriteOption) {
            if (this.disabled)
                return;

            var async = content !== undefined;
            if (async) {
                if (!dontOverwriteOption)
                    this.opts.content = content;
                if (!this.$tip.data('active'))
                    return;
            } else {
                content = this.opts.content;
            }

            // update content only if it has been changed since last time
            var self = this,
                newContent = typeof content == 'function' ?
                    content.call(this.$elm[0], function(newContent) {
                        self.update(newContent);
                    }) :
                    content == '[title]' ? this.$elm.data('title.poshytip') : content;
            if (this.content !== newContent) {
                this.$inner.empty().append(newContent);
                this.content = newContent;
            }

            this.refresh(async);
        },
        refresh: function(async) {
            if (this.disabled)
                return;

            if (async) {
                if (!this.$tip.data('active'))
                    return;
                // save current position as we will need to animate
                var currPos = {left: this.$tip.css('left'), top: this.$tip.css('top')};
            }

            // reset position to avoid text wrapping, etc.
            this.$tip.css({left: 0, top: 0}).appendTo(document.body);

            // save default opacity
            if (this.opacity === undefined)
                this.opacity = this.$tip.css('opacity');

            this.tipOuterW = this.$tip.outerWidth();
            this.tipOuterH = this.$tip.outerHeight();

            this.calcPos();

            // position and show the arrow image
            if (this.pos.arrow) {
                this.$tip.removeClass('left right top bottom');
                var _arrow = {'left':'right','top':'bottom','right':'left','bottom':'top'};
                this.$tip.addClass(_arrow[this.pos.arrow]);
            }

            if (async && this.opts.refreshAniDuration) {
                this.asyncAnimating = true;
                var self = this;
                this.$tip.css(currPos).css({left:this.pos.l,top:this.pos.t}).show();
                //this.$tip.css(currPos).animate({left: this.pos.l, top: this.pos.t}, this.opts.refreshAniDuration, function() { self.asyncAnimating = false; });
            } else {
                this.$tip.css({left: this.pos.l, top: this.pos.t});
            }
        },
        display: function(hide) {
            var active = this.$tip.data('active');
            if (active && !hide || !active && hide)
                return;

            this.$tip.stop();
            if ((this.opts.slide && this.pos.arrow || this.opts.fade) && (hide && this.opts.hideAniDuration || !hide && this.opts.showAniDuration)) {
                var from = {}, to = {};
                // this.pos.arrow is only undefined when alignX == alignY == 'center' and we don't need to slide in that rare case
                if (this.opts.slide && this.pos.arrow) {
                    var prop, arr;
                    if (this.pos.arrow == 'bottom' || this.pos.arrow == 'top') {
                        prop = 'top';
                        arr = 'bottom';
                    } else {
                        prop = 'left';
                        arr = 'right';
                    }
                    var val = parseInt(this.$tip.css(prop));
                    from[prop] = val + (hide ? 0 : (this.pos.arrow == arr ? -this.opts.slideOffset : this.opts.slideOffset));
                    to[prop] = val + (hide ? (this.pos.arrow == arr ? this.opts.slideOffset : -this.opts.slideOffset) : 0) + 'px';
                }
                if (this.opts.fade) {
                    from.opacity = hide ? this.$tip.css('opacity') : 0;
                    to.opacity = hide ? 0 : this.opacity;
                }
                this.$tip.css(from).animate(to, this.opts[hide ? 'hideAniDuration' : 'showAniDuration']);
            }
            hide ? this.$tip.queue($.proxy(this.reset, this)) : this.$tip.css('visibility', 'inherit');
            if (active) {
                var title = this.$elm.data('title.poshytip');
                if (title !== null)
                    this.$elm.attr('title', title);
            }
            this.$tip.data('active', !active);
        },
        disable: function() {
            this.reset();
            this.disabled = true;
        },
        enable: function() {
            this.disabled = false;
        },
        destroy: function() {
            this.reset();
            this.$tip.remove();
            delete this.$tip;
            this.content = null;
            this.$elm.off('.poshytip').removeData('title.poshytip').removeData('poshytip');
            tips.splice($.inArray(this, tips), 1);
        },
        clearTimeouts: function() {
            if (this.showTimeout) {
                clearTimeout(this.showTimeout);
                this.showTimeout = 0;
            }
            if (this.hideTimeout) {
                clearTimeout(this.hideTimeout);
                this.hideTimeout = 0;
            }
        },
        updateCursorPos: function(e) {
            this.eventX = e.pageX;
            this.eventY = e.pageY;
        },
        calcPos: function() {
            var pos = {l: 0, t: 0, arrow: ''},
                $win = $(window),
                win = {
                    l: $win.scrollLeft(),
                    t: $win.scrollTop(),
                    w: $win.width(),
                    h: $win.height()
                }, xL, xC, xR, yT, yC, yB;
            if (this.opts.alignTo == 'cursor') {
                xL = xC = xR = this.eventX;
                yT = yC = yB = this.eventY;
            } else { // this.opts.alignTo == 'target'
                var elmOffset = this.$elm.offset(),
                    elm = {
                        l: elmOffset.left,
                        t: elmOffset.top,
                        w: this.$elm.outerWidth(),
                        h: this.$elm.outerHeight()
                    };
                xL = elm.l + (this.opts.alignX != 'inner-right' ? 0 : elm.w);   // left edge
                xC = xL + Math.floor(elm.w / 2);                // h center
                xR = xL + (this.opts.alignX != 'inner-left' ? elm.w : 0);   // right edge
                yT = elm.t + (this.opts.alignY != 'inner-bottom' ? 0 : elm.h);  // top edge
                yC = yT + Math.floor(elm.h / 2);                // v center
                yB = yT + (this.opts.alignY != 'inner-top' ? elm.h : 0);    // bottom edge
            }

            // keep in viewport and calc arrow position
            switch (this.opts.alignX) {
                case 'right':
                case 'inner-left':
                    pos.l = xR + this.opts.offsetX;
                    if (this.opts.keepInViewport && pos.l + this.tipOuterW > win.l + win.w)
                        pos.l = win.l + win.w - this.tipOuterW;
                    if (this.opts.alignX == 'right' || this.opts.alignY == 'center')
                        pos.arrow = 'left';
                    break;
                case 'center':
                    pos.l = xC - Math.floor(this.tipOuterW / 2);
                    if (this.opts.keepInViewport) {
                        if (pos.l + this.tipOuterW > win.l + win.w)
                            pos.l = win.l + win.w - this.tipOuterW;
                        else if (pos.l < win.l)
                            pos.l = win.l;
                    }
                    break;
                default: // 'left' || 'inner-right'
                    pos.l = xL - this.tipOuterW - this.opts.offsetX;
                    if (this.opts.keepInViewport && pos.l < win.l)
                        pos.l = win.l;
                    if (this.opts.alignX == 'left' || this.opts.alignY == 'center')
                        pos.arrow = 'right';
            }
            switch (this.opts.alignY) {
                case 'bottom':
                case 'inner-top':
                    pos.t = yB + this.opts.offsetY;
                    // 'left' and 'right' need priority for 'target'
                    if (!pos.arrow || this.opts.alignTo == 'cursor')
                        pos.arrow = 'top';
                    if (this.opts.keepInViewport && pos.t + this.tipOuterH > win.t + win.h) {
                        pos.t = yT - this.tipOuterH - this.opts.offsetY;
                        if (pos.arrow == 'top')
                            pos.arrow = 'bottom';
                    }
                    break;
                case 'center':
                    pos.t = yC - Math.floor(this.tipOuterH / 2);
                    if (this.opts.keepInViewport) {
                        if (pos.t + this.tipOuterH > win.t + win.h)
                            pos.t = win.t + win.h - this.tipOuterH;
                        else if (pos.t < win.t)
                            pos.t = win.t;
                    }
                    break;
                default: // 'top' || 'inner-bottom'
                    pos.t = yT - this.tipOuterH - this.opts.offsetY;
                    // 'left' and 'right' need priority for 'target'
                    if (!pos.arrow || this.opts.alignTo == 'cursor')
                        pos.arrow = 'bottom';
                    if (this.opts.keepInViewport && pos.t < win.t) {
                        pos.t = yB + this.opts.offsetY;
                        if (pos.arrow == 'bottom')
                            pos.arrow = 'top';
                    }
            }
            this.pos = pos;
        }
    };

    $.fn.poshytip = function(options) {

        if (typeof options == 'string') {
            var args = arguments,
                method = options;
            Array.prototype.shift.call(args);
            // unhook live events if 'destroy' is called
            if (method == 'destroy') {
                this.die ?
                    this.die('mouseenter.poshytip')
                    .die('touchstart.poshytip')
                    // .die('click.poshytip')
                    .die('focus.poshytip'):
                    $(document).off(this.selector, 'mouseenter.poshytip')
                    // .off(this.selector, 'click.poshytip')
                    .off(this.selector, 'touchstart.poshytip')
                    .off(this.selector, 'focus.poshytip');
            }
            return this.each(function() {
                var poshytip = $(this).data('poshytip');
                if (poshytip && poshytip[method])
                    poshytip[method].apply(poshytip, args);
            });
        }

        var opts = $.extend({}, $.fn.poshytip.defaults, options);
        // check if we need to hook live events
        if (opts.liveEvents && opts.showOn != 'none') {
            var handler,
                deadOpts = $.extend({}, opts, { liveEvents: false });
            switch (opts.showOn) {
                case 'hover':
                    handler = function() {
                        var $this = $(this);
                        if (!$this.data('poshytip'))
                            $this.poshytip(deadOpts).poshytip('mouseenter');
                            $this.poshytip(deadOpts).poshytip('touchstart');
                            $this.poshytip(deadOpts).poshytip('click');
                    };
                    // support 1.4.2+ & 1.9+
                    this.live ?
                        this.live('mouseenter.poshytip,touchstart.poshytip,click.poshytip', handler) :
                        $(document).on(this.selector, 'mouseenter.poshytip,touchstart.poshytip,click.poshytip', handler);
                    break;
                case 'focus':
                    handler = function() {
                        var $this = $(this);
                        if (!$this.data('poshytip'))
                            $this.poshytip(deadOpts).poshytip('showDelayed');
                    };
                    this.live ?
                        this.live('focus.poshytip', handler) :
                        $(document).on(this.selector, 'focus.poshytip', handler);
                    break;
            }
            // return this;
        }
        return this.each(function() {
            new $.Poshytip(this, opts);
        });
    }

    // default settings
    $.fn.poshytip.defaults = {
        content:        '[title]',  // content to display ('[title]', 'string', element, function(updateCallback){...}, jQuery)
        className:      'tip-yellow',   // class for the tips
        idName:         '',     // id for the tip
        showTimeout:        500,        // timeout before showing the tip (in milliseconds 1000 == 1 second)
        hideTimeout:        100,        // timeout before hiding the tip
        timeOnScreen:       0,      // timeout before automatically hiding the tip after showing it (set to > 0 in order to activate)
        showOn:         'hover',    // handler for showing the tip ('hover', 'focus', 'none') - use 'none' to trigger it manually
        liveEvents:     false,      // use live events
        alignTo:        'cursor',   // align/position the tip relative to ('cursor', 'target')
        alignX:         'right',    // horizontal alignment for the tip relative to the mouse cursor or the target element
                            // ('right', 'center', 'left', 'inner-left', 'inner-right') - 'inner-*' matter if alignTo:'target'
        alignY:         'top',      // vertical alignment for the tip relative to the mouse cursor or the target element
                            // ('bottom', 'center', 'top', 'inner-bottom', 'inner-top') - 'inner-*' matter if alignTo:'target'
        offsetX:        -22,        // offset X pixels from the default position - doesn't matter if alignX:'center'
        offsetY:        18,     // offset Y pixels from the default position - doesn't matter if alignY:'center'
        keepInViewport:     true,       // reposition the tooltip if needed to make sure it always appears inside the viewport
        allowTipHover:      true,       // allow hovering the tip without hiding it onmouseout of the target - matters only if showOn:'hover'
        followCursor:       false,      // if the tip should follow the cursor - matters only if showOn:'hover' and alignTo:'cursor'
        fade:           true,       // use fade animation
        slide:          true,       // use slide animation
        slideOffset:        8,      // slide animation offset
        showAniDuration:    300,        // show animation duration - set to 0 if you don't want show animation
        hideAniDuration:    300,        // hide animation duration - set to 0 if you don't want hide animation
        refreshAniDuration: 200     // refresh animation duration - set to 0 if you don't want animation when updating the tooltip asynchronously
    };

})(jQuery);
