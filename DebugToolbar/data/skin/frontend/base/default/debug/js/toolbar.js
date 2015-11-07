(function(window, document, version, callback) {
    var j, d;
    var loaded = false;
    if (!(j = window.jQuery) || version > j.fn.jquery || callback(j)) {
        var script = document.createElement("script");
        script.type = "text/javascript";
        script.src = DEBUG_TOOLBAR_MEDIA_URL + "js/jquery.js";
        script.onload = script.onreadystatechange = function() {
            if (!loaded && (!(d = this.readyState) || d == "loaded" || d == "complete")) {
                callback((j = window.jQuery).noConflict(1), loaded = true);
                j(script).remove();
            }
        };
        document.documentElement.childNodes[0].appendChild(script)
    }
})(window, document, "2.1.4", function($, jquery_loaded) {

    // jquery.cookie.js ------------------------------------------------------------------------------------------------
    $.cookie = function(name, value, options) { if (typeof value != 'undefined') { options = options || {}; if (value === null) { value = ''; options.expires = -1; } var expires = ''; if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) { var date; if (typeof options.expires == 'number') { date = new Date(); date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000)); } else { date = options.expires; } expires = '; expires=' + date.toUTCString(); } var path = options.path ? '; path=' + (options.path) : ''; var domain = options.domain ? '; domain=' + (options.domain) : ''; var secure = options.secure ? '; secure' : ''; document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join(''); } else { var cookieValue = null; if (document.cookie && document.cookie != '') { var cookies = document.cookie.split(';'); for (var i = 0; i < cookies.length; i++) { var cookie = $.trim(cookies[i]); if (cookie.substring(0, name.length + 1) == (name + '=')) { cookieValue = decodeURIComponent(cookie.substring(name.length + 1)); break; } } } return cookieValue; } };

    // jquery.json.js --------------------------------------------------------------------------------------------------
     var escape = /["\\\x00-\x1f\x7f-\x9f]/g,
        meta = {
            '\b': '\\b',
            '\t': '\\t',
            '\n': '\\n',
            '\f': '\\f',
            '\r': '\\r',
            '"': '\\"',
            '\\': '\\\\'
        },
        hasOwn = Object.prototype.hasOwnProperty;

    /**
     * jQuery.toJSON
     * Converts the given argument into a JSON representation.
     *
     * @param o {Mixed} The json-serializable *thing* to be converted
     *
     * If an object has a toJSON prototype, that will be used to get the representation.
     * Non-integer/string keys are skipped in the object, as are keys that point to a
     * function.
     *
     */
    $.toJSON = typeof JSON === 'object' && JSON.stringify ? JSON.stringify : function (o) {
        if (o === null) {
            return 'null';
        }

        var pairs, k, name, val,
            type = $.type(o);

        if (type === 'undefined') {
            return undefined;
        }

        // Also covers instantiated Number and Boolean objects,
        // which are typeof 'object' but thanks to $.type, we
        // catch them here. I don't know whether it is right
        // or wrong that instantiated primitives are not
        // exported to JSON as an {"object":..}.
        // We choose this path because that's what the browsers did.
        if (type === 'number' || type === 'boolean') {
            return String(o);
        }
        if (type === 'string') {
            return $.quoteString(o);
        }
        if (typeof o.toJSON === 'function') {
            return $.toJSON(o.toJSON());
        }
        if (type === 'date') {
            var month = o.getUTCMonth() + 1,
                day = o.getUTCDate(),
                year = o.getUTCFullYear(),
                hours = o.getUTCHours(),
                minutes = o.getUTCMinutes(),
                seconds = o.getUTCSeconds(),
                milli = o.getUTCMilliseconds();

            if (month < 10) {
                month = '0' + month;
            }
            if (day < 10) {
                day = '0' + day;
            }
            if (hours < 10) {
                hours = '0' + hours;
            }
            if (minutes < 10) {
                minutes = '0' + minutes;
            }
            if (seconds < 10) {
                seconds = '0' + seconds;
            }
            if (milli < 100) {
                milli = '0' + milli;
            }
            if (milli < 10) {
                milli = '0' + milli;
            }
            return '"' + year + '-' + month + '-' + day + 'T' +
                hours + ':' + minutes + ':' + seconds +
                '.' + milli + 'Z"';
        }

        pairs = [];

        if ($.isArray(o)) {
            for (k = 0; k < o.length; k++) {
                pairs.push($.toJSON(o[k]) || 'null');
            }
            return '[' + pairs.join(',') + ']';
        }

        // Any other object (plain object, RegExp, ..)
        // Need to do typeof instead of $.type, because we also
        // want to catch non-plain objects.
        if (typeof o === 'object') {
            for (k in o) {
                // Only include own properties,
                // Filter out inherited prototypes
                if (hasOwn.call(o, k)) {
                    // Keys must be numerical or string. Skip others
                    type = typeof k;
                    if (type === 'number') {
                        name = '"' + k + '"';
                    } else if (type === 'string') {
                        name = $.quoteString(k);
                    } else {
                        continue;
                    }
                    type = typeof o[k];

                    // Invalid values like these return undefined
                    // from toJSON, however those object members
                    // shouldn't be included in the JSON string at all.
                    if (type !== 'function' && type !== 'undefined') {
                        val = $.toJSON(o[k]);
                        pairs.push(name + ':' + val);
                    }
                }
            }
            return '{' + pairs.join(',') + '}';
        }
    };

    /**
     * jQuery.evalJSON
     * Evaluates a given json string.
     *
     * @param str {String}
     */
    $.evalJSON = typeof JSON === 'object' && JSON.parse ? JSON.parse : function (str) {
        /*jshint evil: true */
        return eval('(' + str + ')');
    };

    /**
     * jQuery.secureEvalJSON
     * Evals JSON in a way that is *more* secure.
     *
     * @param str {String}
     */
    $.secureEvalJSON = typeof JSON === 'object' && JSON.parse ? JSON.parse : function (str) {
        var filtered =
            str
                .replace(/\\["\\\/bfnrtu]/g, '@')
                .replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']')
                .replace(/(?:^|:|,)(?:\s*\[)+/g, '');

        if (/^[\],:{}\s]*$/.test(filtered)) {
            /*jshint evil: true */
            return eval('(' + str + ')');
        }
        throw new SyntaxError('Error parsing JSON, source is not valid.');
    };

    /**
     * jQuery.quoteString
     * Returns a string-repr of a string, escaping quotes intelligently.
     * Mostly a support function for toJSON.
     * Examples:
     * >>> jQuery.quoteString('apple')
     * "apple"
     *
     * >>> jQuery.quoteString('"Where are we going?", she asked.')
     * "\"Where are we going?\", she asked."
     */
    $.quoteString = function (str) {
        if (str.match(escape)) {
            return '"' + str.replace(escape, function (a) {
                    var c = meta[a];
                    if (typeof c === 'string') {
                        return c;
                    }
                    c = a.charCodeAt();
                    return '\\u00' + Math.floor(c / 16).toString(16) + (c % 16).toString(16);
                }) + '"';
        }
        return '"' + str + '"';
    };

    // toolbar.js ------------------------------------------------------------------------------------------------------
    $('head').append('<link rel="stylesheet" href="'+DEBUG_TOOLBAR_MEDIA_URL+'css/toolbar.css" type="text/css" />');
    var COOKIE_NAME = 'djdt';
    var djdt = {
        popup: null,
        inspector:false,
        initRemoteCallEvent: function(context) {
            var self = this;
            context = context || '#djDebug';

            $('a.remoteCall', context).click(function() {
                var popup = $('<div class="djDebugWindow panelContent"></div>').appendTo(context).show();
                popup.html('<div class="loader"></div>');
                popup.load(this.href, {}, function() {
                    $('a.djDebugBack', popup).click(function() {
                        popup.hide();
                        popup.remove();
                        return false;
                    });
                    self.initRemoteCallEvent(popup);
                });
                popup.show();
                return false;
            });

            self.initAutoselectedTexts();
        },
        initUtilsForm: function(){
            var self = this;
            $('#djDebug').find('#class-search-form').submit(function(){
                $.ajax({
                    type: "POST",
                    url: this.action,
                    dataType: "html",
                    data: $(this).serializeArray(),
                    success: function(data){
                        var results = $('#class-search-results');
                        results.html(data);
                        self.initRemoteCallEvent(results);
                    }
                });
                return false;
            });
        },
        initAutoselectedTexts: function(context){
            var self = this;
            context = context || '#djDebug';
            $('.autoselect', context).dblclick(function(){
                self.select(this);
            });
        },
        select: function(target) {
            var rng, sel;
            if ( document.createRange ) {
                rng = document.createRange();
                rng.selectNode( target )
                sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange( rng );
            } else {
                var rng = document.body.createTextRange();
                rng.moveToElementText( target );
                rng.select();
            }
        },
        init: function(context) {
            $('#djDebug').show();
            var current = null;
            $('#djDebugPanelList li a').click(function() {
                if (!this.className) {
                    return false;
                }
                current = $('#djDebug #' + this.className);
                if (current.is(':visible')) {
                    $(document).trigger('close.djDebug');
                    $(this).parent().removeClass('active');
                } else {
                    $('.panelContent').hide(); // Hide any that are already open
                    current.show();
                    $('#djDebugToolbar li').removeClass('active');
                    $(this).parent().addClass('active');
                }
                return false;
            });
            $('#djDebug a.djDebugClose').click(function() {
                $(document).trigger('close.djDebug');
                $('#djDebugToolbar li').removeClass('active');
                return false;
            });

            this.initRemoteCallEvent();
            this.initUtilsForm();

            $('#debug-panel-Layout a.djTemplateShowContext').click(function() {
                djdt.toggle_arrow($(this).children('.toggleArrow'))
                djdt.toggle_content($(this).parent().next());
                return false;
            });
            $('#djDebugSQLPanel a.djSQLShowStacktrace').click(function() {
                djdt.toggle_content($('.djSQLHideStacktraceDiv', $(this).parents('tr')));
                return false;
            });
            $('#djHideToolBarButton').click(function() {
                djdt.hide_toolbar(true);
                return false;
            });
            $('#djShowToolBarButton').click(function() {
                djdt.show_toolbar();
                return false;
            });
            $(document).bind('close.djDebug', function() {
                // If a sub-panel is open, close that
                if ($('.djDebugWindow').is(':visible')) {
                    $('.djDebugWindow').hide().remove();
                    return;
                }
                // If a panel is open, close that
                if ($('.panelContent').is(':visible')) {
                    $('.panelContent').hide();
                    return;
                }
                // Otherwise, just minimize the toolbar
                if ($('#djDebugToolbar').is(':visible')) {
                    djdt.hide_toolbar(true);
                    return;
                }
            });
            if ($.cookie(COOKIE_NAME)) {
                djdt.hide_toolbar(false);
            } else {
                djdt.show_toolbar(false);
            }
            this.initInspector(document);
        },
        toggle_content: function(elem) {
            if (elem.is(':visible')) {
                elem.hide();
            } else {
                elem.show();
            }
        },
        close: function() {
            $(document).trigger('close.djDebug');
            return false;
        },
        hide_toolbar: function(setCookie) {
            // close any sub panels
            $('.djDebugWindow').hide().remove();
            // close all panels
            $('.panelContent').hide();
            $('#djDebugToolbar li').removeClass('active');
            // finally close toolbar
            $('#djDebugToolbar').hide('fast');
            $('#djDebugToolbarHandle').show();
            // Unbind keydown
            $(document).unbind('keydown.djDebug');
            if (setCookie) {
                $.cookie(COOKIE_NAME, 'hide', {
                    path: '/',
                    expires: 10
                });
            }
        },
        show_toolbar: function(animate) {
            // Set up keybindings
            $(document).bind('keydown.djDebug', function(e) {
                if (e.keyCode == 27) {
                    djdt.close();
                }
            });
            $('#djDebugToolbarHandle').hide();
            if (animate) {
                $('#djDebugToolbar').show('fast');
            } else {
                $('#djDebugToolbar').show();
            }
            $.cookie(COOKIE_NAME, null, {
                path: '/',
                expires: -1
            });
        },
        toggle_arrow: function(elem) {
            var uarr = String.fromCharCode(0x25b6);
            var darr = String.fromCharCode(0x25bc);
            elem.html(elem.html() == uarr ? darr : uarr);
        },
        showPopup: function(elements,e) {
            var self = this;
            this.hidePopup();

            this.popup = $('<div class="djDebugToolbarPopup"><a href="javascript:;" class="btn-close">&nbsp;</a></div>').appendTo('body');

            var table = $('<table></table>').appendTo(this.popup);
            var j = 0;
            $(elements).each(function() {
                var block = $(this);
                var data = $(this).attr('data-debug');
                if (!$.isNumeric(data)) {
                    data = $.evalJSON(data);
                } else {
                    data = $(this).attr('debug');
                }

                for (var i = 0; i< data.length; i++) {
                    var d = data[i];

                    if (j) {
                        table.append('<tr><td colspan="2"> &darr; </td></tr>');
                    }
                    var a = $('<a class="highlightBtn">highlight</a>');
                    a.hover(function(){
                        block.addClass('djDebugHover');
                        self.popup.addClass('invisible');
                    }, function(){
                        block.removeClass('djDebugHover');
                        self.popup.removeClass('invisible');
                    });

                    var tr = $('<tr></tr>')
                    var td = $('<td></td>').append(a).appendTo(tr);
                    table.append(tr);
                    for (var k in d) {
                      table.append('<tr><td>'+ k +'</td><td><span>'+ d[k] +'</span></td></tr>');
                    }
                    j++;
                }
            });

            /*var offset = $(elements.get(0)).offset();
            $(this.popup).css({
                'left' : offset.left + 'px',
                'top' : offset.top + 'px'
            }).show();*/

            $(this.popup).on('dblclick', 'td span', function(e) {
                self.select(this);
            })
            $(this.popup).on('click', 'a.btn-close', function(e) {
                self.hidePopup(e);
            })
            self.position($(this.popup), e).show();
        },
        hidePopup: function(e){
             if (this.popup != null) {
                 $(this.popup).hide().remove();
                 this.popup = null;
             }
             $('.djDebugHover').removeClass('djDebugHover');
        },

        eachChildren: function(element, callback) {
            $(element).each(function(){
                var script = $(this);
                var endpointSelector = 'script[data-type="djDebug-end"][data-id="'+ script.attr('data-id') +'"]';
                script.nextUntil(endpointSelector).each(function(){
                    $.proxy(callback, this, script).call();
                });
                $(endpointSelector).remove();
                script.remove();
            });
        },
        setBlockHovers: function(element) {
            var self = this;
            var startSelector = 'script[data-type="djDebug-start"]';
            var endSelector = 'script[data-type="djDebug-end"]';

            this.eachChildren($(element).find(startSelector), function(script){
                var element = $(this);

                if (element.is('script,link,meta')) {
                    return;
                }

                self.addHover(element, script);
            });


        },
        addHover: function(element, script) {
            var data = script.attr('data-debug');
            element = $(element);

            if (typeof data == 'string') {
                data = $.evalJSON(data);
            }

            var existingData = element.data('debug');
            if (existingData) {
                existingData.push(data);
            } else {
                existingData = [data];
            }

            element.attr('data-debug',script.attr('data-id'));
            element.attr('data-debug', $.toJSON(existingData));
        },
        activateInspector: function() {
            $(window).bind('click', this.showInspector);
            this.inspector = true;
            $('#djDebugInspectButton').addClass('active');
        },
        deactivateInspector: function() {
            $(window).unbind('click', this.showInspector);
            this.inspector = false;
            $('#djDebugInspectButton').removeClass('active');
        },
        initInspector: function(element) {
            var self = this;
            this.setBlockHovers(element);

            this.showInspector = $.proxy(this.showInspector, this);

            $('#djDebugInspectButton').click(function(e){
                e.stopPropagation();
                if (self.inspector == false) {
                    self.activateInspector();
                } else {
                    self.deactivateInspector();
                }
                return false;
            });
        },
        showInspector: function(e) {
            if ($(e.target) == $('#djDebugInspectButton')) {
                return true;
            }

            var self = this;
            self.hidePopup(e);
            var parents = $(e.target).parents('[data-debug]');
            if (parents.length > 0) {
                self.showPopup(parents,e);
            }

            self.deactivateInspector();
            e.stopPropagation();
            return false;
        },
        highLighBlock: function(element){
            this.unhighLighBlock();
            $(element).addClass('djDebugHover-selected');
        },
        unhighLighBlock: function(){
            $('.djDebugHover-selected').removeClass('djDebugHover-selected');
        },
        position: function(element, e){
            element.css("position","absolute");

            var left =  Math.max(0, (($(window).width() - $(element).outerWidth()) / 2) + $(window).scrollLeft());
            var top =  Math.max(0, (($(window).height() - $(element).outerHeight()) / 2) + $(window).scrollTop());



            element.css("top", (top / 2) + "px");
            element.css("left", left + "px");

            /*var top = (e.pageY - $(element).outerHeight() / 2);
            if (top<=0) {
                top = 20;
            }

            var left = (e.pageX - $(element).outerWidth() / 2);
            if (left<=0) {
                left = 20;
            }
            element.css("top", top + "px");
            element.css("left", left + "px");*/
            return element;
        }

    };
    $(document).ready(function() {
        djdt.init();
    });
});

