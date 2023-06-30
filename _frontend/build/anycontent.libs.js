    /*!
     * jQuery blockUI plugin
     * Version 2.70.0-2014.11.23
     * Requires jQuery v1.7 or later
     *
     * Examples at: http://malsup.com/jquery/block/
     * Copyright (c) 2007-2013 M. Alsup
     * Dual licensed under the MIT and GPL licenses:
     * http://www.opensource.org/licenses/mit-license.php
     * http://www.gnu.org/licenses/gpl.html
     *
     * Thanks to Amir-Hossein Sobhi for some excellent contributions!
     */

    ; (function () {
        /*jshint eqeqeq:false curly:false latedef:false */
        "use strict";

        function setup($) {
            $.fn._fadeIn = $.fn.fadeIn;

            var noOp = $.noop || function () { };

            // this bit is to ensure we don't call setExpression when we shouldn't (with extra muscle to handle
            // confusing userAgent strings on Vista)
            var msie = /MSIE/.test(navigator.userAgent);
            var ie6 = /MSIE 6.0/.test(navigator.userAgent) && ! /MSIE 8.0/.test(navigator.userAgent);
            var mode = document.documentMode || 0;
            var setExpr = $.isFunction(document.createElement('div').style.setExpression);

            // global $ methods for blocking/unblocking the entire page
            $.blockUI = function (opts) { install(window, opts); };
            $.unblockUI = function (opts) { remove(window, opts); };

            // convenience method for quick growl-like notifications  (http://www.google.com/search?q=growl)
            $.growlUI = function (title, message, timeout, onClose, css) {
                var $m = $('<div class="growlUI"></div>');
                if (css == false) {
                    $m = $('<div class="growlUI_f"></div>');
                }
                if (title) $m.append('<h1>' + title + '</h1>');
                if (message) $m.append('<h2>' + message + '</h2>');
                if (timeout === undefined) timeout = 3000;

                // Added by konapun: Set timeout to 30 seconds if this growl is moused over, like normal toast notifications
                var callBlock = function (opts) {
                    opts = opts || {};

                    $.blockUI({
                        message: $m,
                        fadeIn: typeof opts.fadeIn !== 'undefined' ? opts.fadeIn : 700,
                        fadeOut: typeof opts.fadeOut !== 'undefined' ? opts.fadeOut : 1000,
                        timeout: typeof opts.timeout !== 'undefined' ? opts.timeout : timeout,
                        centerY: false,
                        showOverlay: false,
                        onUnblock: onClose,
                        css: $.blockUI.defaults.growlCSS
                    });
                };

                callBlock();
                var nonmousedOpacity = $m.css('opacity');
                $m.mouseover(function () {
                    callBlock({
                        fadeIn: 0,
                        timeout: 30000
                    });

                    var displayBlock = $('.blockMsg');
                    displayBlock.stop(); // cancel fadeout if it has started
                    displayBlock.fadeTo(300, 1); // make it easier to read the message by removing transparency
                }).mouseout(function () {
                    $('.blockMsg').fadeOut(1000);
                });
                // End konapun additions
            };

            // plugin method for blocking element content
            $.fn.block = function (opts) {
                if (this[0] === window) {
                    $.blockUI(opts);
                    return this;
                }
                var fullOpts = $.extend({}, $.blockUI.defaults, opts || {});
                this.each(function () {
                    var $el = $(this);
                    if (fullOpts.ignoreIfBlocked && $el.data('blockUI.isBlocked'))
                        return;
                    $el.unblock({ fadeOut: 0 });
                });

                return this.each(function () {
                    if ($.css(this, 'position') == 'static') {
                        this.style.position = 'relative';
                        $(this).data('blockUI.static', true);
                    }
                    this.style.zoom = 1; // force 'hasLayout' in ie
                    install(this, opts);
                });
            };

            // plugin method for unblocking element content
            $.fn.unblock = function (opts) {
                if (this[0] === window) {
                    $.unblockUI(opts);
                    return this;
                }
                return this.each(function () {
                    remove(this, opts);
                });
            };

            $.blockUI.version = 2.70; // 2nd generation blocking at no extra cost!

            // override these in your code to change the default behavior and style
            $.blockUI.defaults = {
                // message displayed when blocking (use null for no message)
                message: '<h1>Please wait...</h1>',

                title: null,		// title string; only used when theme == true
                draggable: true,	// only used when theme == true (requires jquery-ui.js to be loaded)

                theme: false, // set to true to use with jQuery UI themes

                // styles for the message when blocking; if you wish to disable
                // these and use an external stylesheet then do this in your code:
                // $.blockUI.defaults.css = {};
                css: {
                    padding: 0,
                    margin: 0,
                    width: '30%',
                    top: '40%',
                    left: '35%',
                    textAlign: 'center',
                    color: '#000',
                    border: '3px solid #aaa',
                    backgroundColor: '#fff',
                    cursor: 'wait'
                },

                // minimal style set used when themes are used
                themedCSS: {
                    width: '30%',
                    top: '40%',
                    left: '35%'
                },

                // styles for the overlay
                overlayCSS: {
                    backgroundColor: '#000',
                    opacity: 0.6,
                    cursor: 'wait'
                },

                // style to replace wait cursor before unblocking to correct issue
                // of lingering wait cursor
                cursorReset: 'default',

                // styles applied when using $.growlUI
                growlCSS: {
                    width: '350px',
                    top: '10px',
                    left: '',
                    right: '10px',
                    border: 'none',
                    padding: '5px',
                    opacity: 0.6,
                    cursor: 'default',
                    color: '#fff',
                    backgroundColor: '#000',
                    '-webkit-border-radius': '10px',
                    '-moz-border-radius': '10px',
                    'border-radius': '10px'
                },

                // IE issues: 'about:blank' fails on HTTPS and javascript:false is s-l-o-w
                // (hat tip to Jorge H. N. de Vasconcelos)
                /*jshint scripturl:true */
                iframeSrc: /^https/i.test(window.location.href || '') ? 'javascript:false' : 'about:blank',

                // force usage of iframe in non-IE browsers (handy for blocking applets)
                forceIframe: false,

                // z-index for the blocking overlay
                baseZ: 1000,

                // set these to true to have the message automatically centered
                centerX: true, // <-- only effects element blocking (page block controlled via css above)
                centerY: true,

                // allow body element to be stetched in ie6; this makes blocking look better
                // on "short" pages.  disable if you wish to prevent changes to the body height
                allowBodyStretch: true,

                // enable if you want key and mouse events to be disabled for content that is blocked
                bindEvents: true,

                // be default blockUI will supress tab navigation from leaving blocking content
                // (if bindEvents is true)
                constrainTabKey: true,

                // fadeIn time in millis; set to 0 to disable fadeIn on block
                fadeIn: 200,

                // fadeOut time in millis; set to 0 to disable fadeOut on unblock
                fadeOut: 400,

                // time in millis to wait before auto-unblocking; set to 0 to disable auto-unblock
                timeout: 0,

                // disable if you don't want to show the overlay
                showOverlay: true,

                // if true, focus will be placed in the first available input field when
                // page blocking
                focusInput: true,

                // elements that can receive focus
                focusableElements: ':input:enabled:visible',

                // suppresses the use of overlay styles on FF/Linux (due to performance issues with opacity)
                // no longer needed in 2012
                // applyPlatformOpacityRules: true,

                // callback method invoked when fadeIn has completed and blocking message is visible
                onBlock: null,

                // callback method invoked when unblocking has completed; the callback is
                // passed the element that has been unblocked (which is the window object for page
                // blocks) and the options that were passed to the unblock call:
                //	onUnblock(element, options)
                onUnblock: null,

                // callback method invoked when the overlay area is clicked.
                // setting this will turn the cursor to a pointer, otherwise cursor defined in overlayCss will be used.
                onOverlayClick: null,

                // don't ask; if you really must know: http://groups.google.com/group/jquery-en/browse_thread/thread/36640a8730503595/2f6a79a77a78e493#2f6a79a77a78e493
                quirksmodeOffsetHack: 4,

                // class name of the message block
                blockMsgClass: 'blockMsg',

                // if it is already blocked, then ignore it (don't unblock and reblock)
                ignoreIfBlocked: false
            };

            // private data and functions follow...

            var pageBlock = null;
            var pageBlockEls = [];

            function install(el, opts) {
                var css, themedCSS;
                var full = (el == window);
                var msg = (opts && opts.message !== undefined ? opts.message : undefined);
                opts = $.extend({}, $.blockUI.defaults, opts || {});

                if (opts.ignoreIfBlocked && $(el).data('blockUI.isBlocked'))
                    return;

                opts.overlayCSS = $.extend({}, $.blockUI.defaults.overlayCSS, opts.overlayCSS || {});
                css = $.extend({}, $.blockUI.defaults.css, opts.css || {});
                if (opts.onOverlayClick)
                    opts.overlayCSS.cursor = 'pointer';

                themedCSS = $.extend({}, $.blockUI.defaults.themedCSS, opts.themedCSS || {});
                msg = msg === undefined ? opts.message : msg;

                // remove the current block (if there is one)
                if (full && pageBlock)
                    remove(window, { fadeOut: 0 });

                // if an existing element is being used as the blocking content then we capture
                // its current place in the DOM (and current display style) so we can restore
                // it when we unblock
                if (msg && typeof msg != 'string' && (msg.parentNode || msg.jquery)) {
                    var node = msg.jquery ? msg[0] : msg;
                    var data = {};
                    $(el).data('blockUI.history', data);
                    data.el = node;
                    data.parent = node.parentNode;
                    data.display = node.style.display;
                    data.position = node.style.position;
                    if (data.parent)
                        data.parent.removeChild(node);
                }

                $(el).data('blockUI.onUnblock', opts.onUnblock);
                var z = opts.baseZ;

                // blockUI uses 3 layers for blocking, for simplicity they are all used on every platform;
                // layer1 is the iframe layer which is used to supress bleed through of underlying content
                // layer2 is the overlay layer which has opacity and a wait cursor (by default)
                // layer3 is the message content that is displayed while blocking
                var lyr1, lyr2, lyr3, s;
                if (msie || opts.forceIframe)
                    lyr1 = $('<iframe class="blockUI" style="z-index:' + (z++) + ';display:none;border:none;margin:0;padding:0;position:absolute;width:100%;height:100%;top:0;left:0" src="' + opts.iframeSrc + '"></iframe>');
                else
                    lyr1 = $('<div class="blockUI" style="display:none"></div>');

                if (opts.theme)
                    lyr2 = $('<div class="blockUI blockOverlay ui-widget-overlay" style="z-index:' + (z++) + ';display:none"></div>');
                else
                    lyr2 = $('<div class="blockUI blockOverlay" style="z-index:' + (z++) + ';display:none;border:none;margin:0;padding:0;width:100%;height:100%;top:0;left:0"></div>');

                if (opts.theme && full) {
                    s = '<div class="blockUI ' + opts.blockMsgClass + ' blockPage ui-dialog ui-widget ui-corner-all" style="z-index:' + (z + 10) + ';display:none;position:fixed">';
                    if (opts.title) {
                        s += '<div class="ui-widget-header ui-dialog-titlebar ui-corner-all blockTitle">' + (opts.title || '&nbsp;') + '</div>';
                    }
                    s += '<div class="ui-widget-content ui-dialog-content"></div>';
                    s += '</div>';
                }
                else if (opts.theme) {
                    s = '<div class="blockUI ' + opts.blockMsgClass + ' blockElement ui-dialog ui-widget ui-corner-all" style="z-index:' + (z + 10) + ';display:none;position:absolute">';
                    if (opts.title) {
                        s += '<div class="ui-widget-header ui-dialog-titlebar ui-corner-all blockTitle">' + (opts.title || '&nbsp;') + '</div>';
                    }
                    s += '<div class="ui-widget-content ui-dialog-content"></div>';
                    s += '</div>';
                }
                else if (full) {
                    s = '<div class="blockUI ' + opts.blockMsgClass + ' blockPage" style="z-index:' + (z + 10) + ';display:none;position:fixed"></div>';
                }
                else {
                    s = '<div class="blockUI ' + opts.blockMsgClass + ' blockElement" style="z-index:' + (z + 10) + ';display:none;position:absolute"></div>';
                }
                lyr3 = $(s);

                // if we have a message, style it
                if (msg) {
                    if (opts.theme) {
                        lyr3.css(themedCSS);
                        lyr3.addClass('ui-widget-content');
                    }
                    else
                        lyr3.css(css);
                }

                // style the overlay
                if (!opts.theme /*&& (!opts.applyPlatformOpacityRules)*/)
                    lyr2.css(opts.overlayCSS);
                lyr2.css('position', full ? 'fixed' : 'absolute');

                // make iframe layer transparent in IE
                if (msie || opts.forceIframe)
                    lyr1.css('opacity', 0.0);

                //$([lyr1[0],lyr2[0],lyr3[0]]).appendTo(full ? 'body' : el);
                var layers = [lyr1, lyr2, lyr3], $par = full ? $('body') : $(el);
                $.each(layers, function () {
                    this.appendTo($par);
                });

                if (opts.theme && opts.draggable && $.fn.draggable) {
                    lyr3.draggable({
                        handle: '.ui-dialog-titlebar',
                        cancel: 'li'
                    });
                }

                // ie7 must use absolute positioning in quirks mode and to account for activex issues (when scrolling)
                var expr = setExpr && (!$.support.boxModel || $('object,embed', full ? null : el).length > 0);
                if (ie6 || expr) {
                    // give body 100% height
                    if (full && opts.allowBodyStretch && $.support.boxModel)
                        $('html,body').css('height', '100%');

                    // fix ie6 issue when blocked element has a border width
                    if ((ie6 || !$.support.boxModel) && !full) {
                        var t = sz(el, 'borderTopWidth'), l = sz(el, 'borderLeftWidth');
                        var fixT = t ? '(0 - ' + t + ')' : 0;
                        var fixL = l ? '(0 - ' + l + ')' : 0;
                    }

                    // simulate fixed position
                    $.each(layers, function (i, o) {
                        var s = o[0].style;
                        s.position = 'absolute';
                        if (i < 2) {
                            if (full)
                                s.setExpression('height', 'Math.max(document.body.scrollHeight, document.body.offsetHeight) - (jQuery.support.boxModel?0:' + opts.quirksmodeOffsetHack + ') + "px"');
                            else
                                s.setExpression('height', 'this.parentNode.offsetHeight + "px"');
                            if (full)
                                s.setExpression('width', 'jQuery.support.boxModel && document.documentElement.clientWidth || document.body.clientWidth + "px"');
                            else
                                s.setExpression('width', 'this.parentNode.offsetWidth + "px"');
                            if (fixL) s.setExpression('left', fixL);
                            if (fixT) s.setExpression('top', fixT);
                        }
                        else if (opts.centerY) {
                            if (full) s.setExpression('top', '(document.documentElement.clientHeight || document.body.clientHeight) / 2 - (this.offsetHeight / 2) + (blah = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop) + "px"');
                            s.marginTop = 0;
                        }
                        else if (!opts.centerY && full) {
                            var top = (opts.css && opts.css.top) ? parseInt(opts.css.top, 10) : 0;
                            var expression = '((document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop) + ' + top + ') + "px"';
                            s.setExpression('top', expression);
                        }
                    });
                }

                // show the message
                if (msg) {
                    if (opts.theme)
                        lyr3.find('.ui-widget-content').append(msg);
                    else
                        lyr3.append(msg);
                    if (msg.jquery || msg.nodeType)
                        $(msg).show();
                }

                if ((msie || opts.forceIframe) && opts.showOverlay)
                    lyr1.show(); // opacity is zero
                if (opts.fadeIn) {
                    var cb = opts.onBlock ? opts.onBlock : noOp;
                    var cb1 = (opts.showOverlay && !msg) ? cb : noOp;
                    var cb2 = msg ? cb : noOp;
                    if (opts.showOverlay)
                        lyr2._fadeIn(opts.fadeIn, cb1);
                    if (msg)
                        lyr3._fadeIn(opts.fadeIn, cb2);
                }
                else {
                    if (opts.showOverlay)
                        lyr2.show();
                    if (msg)
                        lyr3.show();
                    if (opts.onBlock)
                        opts.onBlock.bind(lyr3)();
                }

                // bind key and mouse events
                bind(1, el, opts);

                if (full) {
                    pageBlock = lyr3[0];
                    pageBlockEls = $(opts.focusableElements, pageBlock);
                    if (opts.focusInput)
                        setTimeout(focus, 20);
                }
                else
                    center(lyr3[0], opts.centerX, opts.centerY);

                if (opts.timeout) {
                    // auto-unblock
                    var to = setTimeout(function () {
                        if (full)
                            $.unblockUI(opts);
                        else
                            $(el).unblock(opts);
                    }, opts.timeout);
                    $(el).data('blockUI.timeout', to);
                }
            }

            // remove the block
            function remove(el, opts) {
                var count;
                var full = (el == window);
                var $el = $(el);
                var data = $el.data('blockUI.history');
                var to = $el.data('blockUI.timeout');
                if (to) {
                    clearTimeout(to);
                    $el.removeData('blockUI.timeout');
                }
                opts = $.extend({}, $.blockUI.defaults, opts || {});
                bind(0, el, opts); // unbind events

                if (opts.onUnblock === null) {
                    opts.onUnblock = $el.data('blockUI.onUnblock');
                    $el.removeData('blockUI.onUnblock');
                }

                var els;
                if (full) // crazy selector to handle odd field errors in ie6/7
                    els = $('body').children().filter('.blockUI').add('body > .blockUI');
                else
                    els = $el.find('>.blockUI');

                // fix cursor issue
                if (opts.cursorReset) {
                    if (els.length > 1)
                        els[1].style.cursor = opts.cursorReset;
                    if (els.length > 2)
                        els[2].style.cursor = opts.cursorReset;
                }

                if (full)
                    pageBlock = pageBlockEls = null;

                if (opts.fadeOut) {
                    count = els.length;
                    els.stop().fadeOut(opts.fadeOut, function () {
                        if (--count === 0)
                            reset(els, data, opts, el);
                    });
                }
                else
                    reset(els, data, opts, el);
            }

            // move blocking element back into the DOM where it started
            function reset(els, data, opts, el) {
                var $el = $(el);
                if ($el.data('blockUI.isBlocked'))
                    return;

                els.each(function (i, o) {
                    // remove via DOM calls so we don't lose event handlers
                    if (this.parentNode)
                        this.parentNode.removeChild(this);
                });

                if (data && data.el) {
                    data.el.style.display = data.display;
                    data.el.style.position = data.position;
                    data.el.style.cursor = 'default'; // #59
                    if (data.parent)
                        data.parent.appendChild(data.el);
                    $el.removeData('blockUI.history');
                }

                if ($el.data('blockUI.static')) {
                    $el.css('position', 'static'); // #22
                }

                if (typeof opts.onUnblock == 'function')
                    opts.onUnblock(el, opts);

                // fix issue in Safari 6 where block artifacts remain until reflow
                var body = $(document.body), w = body.width(), cssW = body[0].style.width;
                body.width(w - 1).width(w);
                body[0].style.width = cssW;
            }

            // bind/unbind the handler
            function bind(b, el, opts) {
                var full = el == window, $el = $(el);

                // don't bother unbinding if there is nothing to unbind
                if (!b && (full && !pageBlock || !full && !$el.data('blockUI.isBlocked')))
                    return;

                $el.data('blockUI.isBlocked', b);

                // don't bind events when overlay is not in use or if bindEvents is false
                if (!full || !opts.bindEvents || (b && !opts.showOverlay))
                    return;

                // bind anchors and inputs for mouse and key events
                var events = 'mousedown mouseup keydown keypress keyup touchstart touchend touchmove';
                if (b)
                    $(document).bind(events, opts, handler);
                else
                    $(document).unbind(events, handler);

                // former impl...
                //		var $e = $('a,:input');
                //		b ? $e.bind(events, opts, handler) : $e.unbind(events, handler);
            }

            // event handler to suppress keyboard/mouse events when blocking
            function handler(e) {
                // allow tab navigation (conditionally)
                if (e.type === 'keydown' && e.keyCode && e.keyCode == 9) {
                    if (pageBlock && e.data.constrainTabKey) {
                        var els = pageBlockEls;
                        var fwd = !e.shiftKey && e.target === els[els.length - 1];
                        var back = e.shiftKey && e.target === els[0];
                        if (fwd || back) {
                            setTimeout(function () { focus(back); }, 10);
                            return false;
                        }
                    }
                }
                var opts = e.data;
                var target = $(e.target);
                if (target.hasClass('blockOverlay') && opts.onOverlayClick)
                    opts.onOverlayClick(e);

                // allow events within the message content
                if (target.parents('div.' + opts.blockMsgClass).length > 0)
                    return true;

                // allow events for content that is not being blocked
                return target.parents().children().filter('div.blockUI').length === 0;
            }

            function focus(back) {
                if (!pageBlockEls)
                    return;
                var e = pageBlockEls[back === true ? pageBlockEls.length - 1 : 0];
                if (e)
                    e.focus();
            }

            function center(el, x, y) {
                var p = el.parentNode, s = el.style;
                var l = ((p.offsetWidth - el.offsetWidth) / 2) - sz(p, 'borderLeftWidth');
                var t = ((p.offsetHeight - el.offsetHeight) / 2) - sz(p, 'borderTopWidth');
                if (x) s.left = l > 0 ? (l + 'px') : '0';
                if (y) s.top = t > 0 ? (t + 'px') : '0';
            }

            function sz(el, p) {
                return parseInt($.css(el, p), 10) || 0;
            }

        }


        /*global define:true */
        if (typeof define === 'function' && define.amd && define.amd.jQuery) {
            define(['jquery'], setup);
        } else {
            setup(jQuery);
        }

    })();
/**
 * jquery.filterTable
 *
 * This plugin will add a search filter to tables. When typing in the filter,
 * any rows that do not contain the filter will be hidden.
 *
 * Utilizes bindWithDelay() if available. https://github.com/bgrins/bindWithDelay
 *
 * @version v1.5.7
 * @author Sunny Walker, swalker@hawaii.edu
 * @license MIT
 */
(function ($) {
    var jversion = $.fn.jquery.split('.'),
        jmajor = parseFloat(jversion[0]),
        jminor = parseFloat(jversion[1]);
    // build the pseudo selector for jQuery < 1.8
    if (jmajor < 2 && jminor < 8) {
        // build the case insensitive filtering functionality as a pseudo-selector expression
        $.expr[':'].filterTableFind = function (a, i, m) {
            return $(a).text().toUpperCase().indexOf(m[3].toUpperCase().replace(/"""/g, '"').replace(/"\\"/g, "\\")) >= 0;
        };
        // build the case insensitive all-words filtering functionality as a pseudo-selector expression
        $.expr[':'].filterTableFindAny = function (a, i, m) {
            // build an array of each non-falsey value passed
            var raw_args = m[3].split(/[\s,]/),
                args = [];
            $.each(raw_args, function (j, v) {
                var t = v.replace(/^\s+|\s$/g, '');
                if (t) {
                    args.push(t);
                }
            });
            // if there aren't any non-falsey values to search for, abort
            if (!args.length) {
                return false;
            }
            return function (a) {
                var found = false;
                $.each(args, function (j, v) {
                    if ($(a).text().toUpperCase().indexOf(v.toUpperCase().replace(/"""/g, '"').replace(/"\\"/g, "\\")) >= 0) {
                        found = true;
                        return false;
                    }
                });
                return found;
            };
        };
        // build the case insensitive all-words filtering functionality as a pseudo-selector expression
        $.expr[':'].filterTableFindAll = function (a, i, m) {
            // build an array of each non-falsey value passed
            var raw_args = m[3].split(/[\s,]/),
                args = [];
            $.each(raw_args, function (j, v) {
                var t = v.replace(/^\s+|\s$/g, '');
                if (t) {
                    args.push(t);
                }
            });
            // if there aren't any non-falsey values to search for, abort
            if (!args.length) {
                return false;
            }
            return function (a) {
                // how many terms were found?
                var found = 0;
                $.each(args, function (j, v) {
                    if ($(a).text().toUpperCase().indexOf(v.toUpperCase().replace(/"""/g, '"').replace(/"\\"/g, "\\")) >= 0) {
                        // found another term
                        found++;
                    }
                });
                return found === args.length; // did we find all of them in this cell?
            };
        };
    } else {
        // build the pseudo selector for jQuery >= 1.8
        $.expr[':'].filterTableFind = jQuery.expr.createPseudo(function (arg) {
            return function (el) {
                return $(el).text().toUpperCase().indexOf(arg.toUpperCase().replace(/"""/g, '"').replace(/"\\"/g, "\\")) >= 0;
            };
        });
        $.expr[':'].filterTableFindAny = jQuery.expr.createPseudo(function (arg) {
            // build an array of each non-falsey value passed
            var raw_args = arg.split(/[\s,]/),
                args = [];
            $.each(raw_args, function (i, v) {
                // trim the string
                var t = v.replace(/^\s+|\s$/g, '');
                if (t) {
                    args.push(t);
                }
            });
            // if there aren't any non-falsey values to search for, abort
            if (!args.length) {
                return false;
            }
            return function (el) {
                var found = false;
                $.each(args, function (i, v) {
                    if ($(el).text().toUpperCase().indexOf(v.toUpperCase().replace(/"""/g, '"').replace(/"\\"/g, "\\")) >= 0) {
                        found = true;
                        // short-circuit the searching since this cell has one of the terms
                        return false;
                    }
                });
                return found;
            };
        });
        $.expr[':'].filterTableFindAll = jQuery.expr.createPseudo(function (arg) {
            // build an array of each non-falsey value passed
            var raw_args = arg.split(/[\s,]/),
                args = [];
            $.each(raw_args, function (i, v) {
                // trim the string
                var t = v.replace(/^\s+|\s$/g, '');
                if (t) {
                    args.push(t);
                }
            });
            // if there aren't any non-falsey values to search for, abort
            if (!args.length) {
                return false;
            }
            return function (el) {
                // how many terms were found?
                var found = 0;
                $.each(args, function (i, v) {
                    if ($(el).text().toUpperCase().indexOf(v.toUpperCase().replace(/"""/g, '"').replace(/"\\"/g, "\\")) >= 0) {
                        // found another term
                        found++;
                    }
                });
                // did we find all of them in this cell?
                return found === args.length;
            };
        });
    }
    // define the filterTable plugin
    $.fn.filterTable = function (options) {
        // start off with some default settings
        var defaults = {
                // make the filter input field autofocused (not recommended for accessibility)
                autofocus: false,

                // callback function: function (term, table){}
                callback: null,

                // class to apply to the container
                containerClass: 'filter-table',

                // tag name of the container
                containerTag: 'p',

                // jQuery expression method to use for filtering
                filterExpression: 'filterTableFind',

                // if true, the table's tfoot(s) will be hidden when the table is filtered
                hideTFootOnFilter: false,

                // class applied to cells containing the filter term
                highlightClass: 'alt',

                // don't filter the contents of cells with this class
                ignoreClass: '',

                // don't filter the contents of these columns
                ignoreColumns: [],

                // use the element with this selector for the filter input field instead of creating one
                inputSelector: null,

                // name of filter input field
                inputName: '',

                // tag name of the filter input tag
                inputType: 'search',

                // text to precede the filter input tag
                label: 'Filter:',

                // filter only when at least this number of characters are in the filter input field
                minChars: 1,

                // don't show the filter on tables with at least this number of rows
                minRows: 8,

                // HTML5 placeholder text for the filter field
                placeholder: 'search this table',

                // prevent the return key in the filter input field from trigger form submits
                preventReturnKey: true,

                // list of phrases to quick fill the search
                quickList: [],

                // class of each quick list item
                quickListClass: 'quick',

                // quick list item label to clear the filter (e.g., '&times; Clear filter')
                quickListClear: '',

                // tag surrounding quick list items (e.g., ul)
                quickListGroupTag: '',

                // tag type of each quick list item (e.g., a or li)
                quickListTag: 'a',

                // class applied to visible rows
                visibleClass: 'visible'
            },
            // mimic PHP's htmlspecialchars() function
            hsc = function (text) {
                return text.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            },
            // merge the user's settings into the defaults
            settings = $.extend({}, defaults, options);

        // handle the actual table filtering
        var doFiltering = function (table, q) {
                // cache the tbody element
                var tbody = table.find('tbody');
                // if the filtering query is blank or the number of chars is less than the minChars option
                if (q === '' || q.length < settings.minChars) {
                    // show all rows
                    tbody.find('tr').show().addClass(settings.visibleClass);
                    // remove the row highlight from all cells
                    tbody.find('td').removeClass(settings.highlightClass);
                    // show footer if the setting was specified
                    if (settings.hideTFootOnFilter) {
                        table.find('tfoot').show();
                    }
                } else {
                    // if the filter query is not blank
                    var all_tds = tbody.find('td');
                    // hide all rows, assuming none were found
                    tbody.find('tr').hide().removeClass(settings.visibleClass);
                    // remove previous highlights
                    all_tds.removeClass(settings.highlightClass);
                    // hide footer if the setting was specified
                    if (settings.hideTFootOnFilter) {
                        table.find('tfoot').hide();
                    }
                    if (settings.ignoreColumns.length) {
                        var tds = [];
                        if (settings.ignoreClass) {
                            all_tds = all_tds.not('.' + settings.ignoreClass);
                        }
                        tds = all_tds.filter(':' + settings.filterExpression + '("' + q + '")');
                        tds.each(function () {
                            var t = $(this),
                                col = t.parent().children().index(t);
                            if ($.inArray(col, settings.ignoreColumns) === -1) {
                                t.addClass(settings.highlightClass).closest('tr').show().addClass(settings.visibleClass);
                            }
                        });
                    } else {
                        if (settings.ignoreClass) {
                            all_tds = all_tds.not('.' + settings.ignoreClass);
                        }
                        // highlight (class=alt) only the cells that match the query and show their rows
                        all_tds.filter(':' + settings.filterExpression + '("' + q + '")').addClass(settings.highlightClass).closest('tr').show().addClass(settings.visibleClass);
                    }
                }
                // call the callback function
                if (settings.callback) {
                    settings.callback(q, table);
                }
            }; // doFiltering()

        return this.each(function () {
            // cache the table
            var t = $(this),
                // cache the tbody
                tbody = t.find('tbody'),
                // placeholder for the filter field container DOM node
                container = null,
                // placeholder for the quick list items
                quicks = null,
                // placeholder for the field field DOM node
                filter = null,
                // was the filter created or chosen from an existing element?
                created_filter = true;

            // only if object is a table and there's a tbody and at least minRows trs and hasn't already had a filter added
            if (t[0].nodeName === 'TABLE' && tbody.length > 0 && (settings.minRows === 0 || (settings.minRows > 0 && tbody.find('tr').length >= settings.minRows)) && !t.prev().hasClass(settings.containerClass)) {
                // use a single existing field as the filter input field
                if (settings.inputSelector && $(settings.inputSelector).length === 1) {
                    filter = $(settings.inputSelector);
                    // container to hold the quick list options
                    container = filter.parent();
                    created_filter = false;
                } else {
                    // create the filter input field (and container)
                    // build the container tag for the filter field
                    container = $('<' + settings.containerTag + ' />');
                    // add any classes that need to be added
                    if (settings.containerClass !== '') {
                        container.addClass(settings.containerClass);
                    }
                    // add the label for the filter field
                    container.prepend(settings.label + ' ');
                    // build the filter field
                    filter = $('<input type="' + settings.inputType + '" placeholder="' + settings.placeholder + '" name="' + settings.inputName + '" />');
                    // prevent return in the filter field from submitting any forms
                    if (settings.preventReturnKey) {
                        filter.on('keydown', function (ev) {
                            if ((ev.keyCode || ev.which) === 13) {
                                ev.preventDefault();
                                return false;
                            }
                        });
                    }
                }

                // add the autofocus attribute if requested
                if (settings.autofocus) {
                    filter.attr('autofocus', true);
                }

                // does bindWithDelay() exist?
                if ($.fn.bindWithDelay) {
                    // bind doFiltering() to keyup (delayed)
                    filter.bindWithDelay('keyup', function () {
                        doFiltering(t, $(this).val());
                    }, 200);
                } else {
                    // just bind to onKeyUp
                    // bind doFiltering() to keyup
                    filter.bind('keyup', function () {
                        doFiltering(t, $(this).val());
                    });
                }

                // bind doFiltering() to additional events
                filter.bind('click search input paste blur', function () {
                    doFiltering(t, $(this).val());
                });

                // add the filter field to the container if it was created by the plugin
                if (created_filter) {
                    container.append(filter);
                }

                // are there any quick list items to add?
                if (settings.quickList.length > 0 || settings.quickListClear) {
                    quicks = settings.quickListGroupTag ? $('<' + settings.quickListGroupTag + ' />') : container;
                    // for each quick list item...
                    $.each(settings.quickList, function (index, value) {
                        // build the quick list item link
                        var q = $('<' + settings.quickListTag + ' class="' + settings.quickListClass + '" />');
                        // add the item's text
                        q.text(hsc(value));
                        if (q[0].nodeName === 'A') {
                            // add a (worthless) href to the item if it's an anchor tag so that it gets the browser's link treatment
                            q.attr('href', '#');
                        }
                        // bind the click event to it
                        q.bind('click', function (e) {
                            // stop the normal anchor tag behavior from happening
                            e.preventDefault();
                            // send the quick list value over to the filter field and trigger the event
                            filter.val(value).focus().trigger('click');
                        });
                        // add the quick list link to the quick list groups container
                        quicks.append(q);
                    });

                    // add the quick list clear item if a label has been specified
                    if (settings.quickListClear) {
                        // build the clear item
                        var q = $('<' + settings.quickListTag + ' class="' + settings.quickListClass + '" />');
                        // add the label text
                        q.html(settings.quickListClear);
                        if (q[0].nodeName === 'A') {
                            // add a (worthless) href to the item if it's an anchor tag so that it gets the browser's link treatment
                            q.attr('href', '#');
                        }
                        // bind the click event to it
                        q.bind('click', function (e) {
                            e.preventDefault();
                            // clear the quick list value and trigger the event
                            filter.val('').focus().trigger('click');
                        });
                        // add the clear item to the quick list groups container
                        quicks.append(q);
                    }

                    // add the quick list groups container to the DOM if it isn't already there
                    if (quicks !== container) {
                        container.append(quicks);
                    }
                }

                // add the filter field and quick list container to just before the table if it was created by the plugin
                if (created_filter) {
                    t.before(container);
                }
            }
        }); // return this.each
    }; // $.fn.filterTable
})(jQuery);
