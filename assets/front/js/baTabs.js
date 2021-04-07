/*
 * BestAddonTabs - Javascript Accordion
 * Copyright (c) 2010 BestAddon.com
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */
/* global baTabs */
; (function (win, doc, ba) {
  'use strict'
  function isType (e) { return {}.toString.call(e).slice(8, -1).toLowerCase() }
  function on (el, ev, fn) { for (var i = 0, evs = ev.split(/\s+/); i < evs.length; ++i) { el.addEventListener(evs[i], fn, false) } } // element, events "click input", function callback
  function forEach (e, fn) { var i; if (isType(e) === 'object') { for (i in e) { fn(e[i], i) } } else { for (i = 0; i < e.length; i++) { fn(e[i], i) } } } // element, function callback
  function hasClass (e, c) { return !!e.className.match(new RegExp('(\\s|^)' + c + '(\\s|$)')) } // element, class name
  function addClass (e, c) { if (!hasClass(e, c)) e.className += ' ' + c } // element, class name
  function removeClass (e, c) { if (hasClass(e, c)) { e.className = e.className.replace(new RegExp('(\\s|^)' + c + '(\\s|$)'), ' ') } } // element, class name
  function wrap (el) { var w = doc.createElement('div'); el.parentNode.insertBefore(w, el); w.appendChild(el) }
  function extend (r) { r = r || {}; for (var n = 1; n < arguments.length; n++) if (arguments[n]) for (var e in arguments[n]) arguments[n].hasOwnProperty(e) && (r[e] = arguments[n][e]); return r };

  function Main (obj, options) {
    var defaults = { // set default options
      width: '100%',
      height: 'auto',
      orient: 'horizontal', // orientation: vertical | horizontal
      defaultid: 0,
      interval: 0, // 5000, If it > 0 is autoplay
      speed: '500ms',
      hoverpause: true,
      event: 'click', // click, mouseover, mouseenter
      tabNav: true,
      tabActiveClass: 'tab__active',
      panelActiveClass: 'panel__active',
      nextPrev: false,
      effect: 'fadeIn', // fadeIn, bounceIn, ...
      keyNav: true,
      breakPoint: 'default', // default | accordion | dropdown
      breakPointWidth: 576,
      style: 'style82'
    }

    // The element that is passed into the design
    var objPlay, tabStick
    var opts = extend(defaults, JSON.parse(obj.getAttribute('data-options')) || options)
    var filterElements = function (selector, match) {
      return [].filter.call(obj.querySelectorAll(selector), function (el) { return el.nodeName.toLowerCase() === match })
    }
    var slides = filterElements('.ba__panel-tabs > *', 'div')
    var pagerWrap = obj.querySelector('.ba__nav-tabs')
    var pager = pagerWrap.children
    var current = (opts.defaultid >= 0 && opts.defaultid < slides.length) ? opts.defaultid : 0
    var next = current + 1

    addClass(slides[current], opts.panelActiveClass)
    wrap(obj)
    var wrapper = obj.parentNode
    wrapper.style.width = opts.width
    addClass(obj.parentNode, ba + 'Warp ' + opts.style + ' ' + opts.orient + ' breakPoint' + opts.breakPoint)
    obj.style.setProperty('--ba-tab-speed', opts.speed)
    addClass(obj, 'ba__tabs')

    var tabClass = function (list, id, removeClassName, addClassName) {
      forEach(list, function (el) { removeClass(el, removeClassName || opts.tabActiveClass) })
      if (isType(id) === 'number') addClass(list[id], addClassName || opts.tabActiveClass)
    }

    // rotate to selected slide on pager click
    var pagination = function (pager) {
      if (pager.length && opts.tabNav) {
        tabClass(pager, current)
        forEach(pager, function (el) {
          on(el.firstChild, opts.event + ' touchstart', function (e) {
            e.preventDefault()
            clearTimeout(objPlay)
            next = [].indexOf.call(pager, this.parentNode)
            rotate()
          })
        })
      }
    }
    pagination(pager)

    var rotate = function () { // primary function to change slides
      if (tabStick) tabStick.innerHTML = pager[next].firstChild.innerHTML
      tabClass(slides, next, opts.panelActiveClass, 'animated ' + opts.effect + ' ' + opts.panelActiveClass)
      if (pager.length && opts.tabNav) { // update pager to reflect slide change
        tabClass(pager, next)
      }
      current = next
      next = current >= slides.length - 1 ? 0 : current + 1
    }

    if (opts.nextPrev) {
      obj.insertAdjacentHTML('afterend', '<span class="ba__arrows prev">&larr; Prev</span><span class="ba__arrows next">Next &rarr;</span>')
      forEach(obj.parentNode.querySelectorAll('.ba__arrows'), function (el) {
        on(el, 'click touchstart', function (e) {
          if (e.target.className.indexOf('prev') > -1) {
            clearTimeout(objPlay)
            next = current <= 0 ? slides.length - 1 : current - 1
            rotate()
          } else {
            clearTimeout(objPlay)
            rotate()
          }
        })
      })
    }

    if (opts.keyNav) { // Add keyboard navigation
      on(document, 'keyup', function (e) {
        switch (e.which) {
          case 39: case 32: // right arrow & space
            clearTimeout(objPlay)
            rotate()
            break
          case 37: // left arrow
            clearTimeout(objPlay)
            next = current <= 0 ? slides.length - 1 : current - 1
            rotate()
            break
        }
      })
    }
  }

  // add to global namespace
  win[ba] = Main
})(window, document, 'baTabs');
(function (fn, d) { /c/.test(d.readyState) ? fn() : d.addEventListener('DOMContentLoaded', fn) })(function () {
  [].forEach.call(document.querySelectorAll('[data-ba-tabs]'), function (obj) {
    baTabs(obj)
  })
}, document)
