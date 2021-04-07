/*
 * oResizeDrag - jQuery Resize and Drag
 * Copyright (c) 2011 BestAddon.com/vtem.net
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */
/* global tinymce, jQuery, BaMainPicker, weeKit */

;(function (W, D, $, fn) {
  // BEGIN FUNCTION ///////////////////
  'use strict'
  var s = 'querySelector'
  var sa = s + 'All'

  var Main = function () {
    if (!(this instanceof Main)) return new Main()
  }

  Main.prototype = {
    // BEGIN FOR COLOR PICER
    baPicker: function (selector) {
      $(selector).each(function (i, el) { new BaMainPicker(el, el.parentNode) })
    },
    // BEGIN FOR SELECT[DATA-REL= "BUTTONS, DROPDOWN, LIST"] ELEMENT
    selectGroup: function (selector) {
      $(selector).each(function (i, item) {
        if (item.hasAttribute('data-rel')) {
          $(item).hide().next('ul').remove()
          $(item).prev('b').remove()
          var iconClass = $(item).attr('class').indexOf('fa') > -1 ? 'fas' : ($(item).attr('class').indexOf('ico') > -1 ? 'icofont' : '')
          var list = ''
          var nodeB = ''
          $(item).children().each(function (i, els) {
            if (els.hasAttribute('label')) { // FOR optgroup in LIST
              list += '<h3>' + $(els).attr('label') + '</h3>'
              $(els).children().each(function (i, el) {
                nodeB = '<b class="select select-dropdown ' + iconClass + '-bashow"><i class="' + item.value + '"></i><i class="hide">' + item.value + '</i></b>'
                list += '<li data-rel="' + el.value + '" ' + (el.value === item.value ? 'class="selected"' : '') + '>' + $('<div/>').html(el.innerHTML).text() + '</li>'
              })
            } else {
              nodeB = '<b class="select select-dropdown"><i class="' + item.value + '"></i><i class="hide">' + item.value + '</i></b>'
              list += '<li data-rel="' + els.value + '" ' + (els.value === item.value ? 'class="selected"' : '') + '>' + $('<div/>').html(els.innerHTML).text() + '</li>'
            }
          })
          $(item).after('<ul class="select-' + $(item).attr('data-rel') + ' ' + iconClass + '">' + list + '</ul>')
          if ($(item).attr('data-rel') === 'dropdown' || $(item).attr('data-rel') === 'modal') {
            $(item).before(nodeB)
            $(item).prev().on('click touchstart', function (e) {
              var self = this.parentNode
              if ($(self).children('ul').hasClass('dropdown-open')) $(self).children('ul').removeClass('dropdown-open')
              else $(self).children('ul').addClass('dropdown-open')
              $(D).on('click touchstart', function (e) { // Click outside of dropdown will close it
                if (self && !self.contains(e.target)) $(self).children('ul').removeClass('dropdown-open')
              })
            })
          }
          $(item).next().on('click touchstart', function (e) {
            var self = e.target
            if (!$(self).is('ul')) {
              var tagValid = !$(self).is('h3') ? ($(self).is('li') ? self : self.parentNode) : ''
              if (tagValid !== '') {
                $(item).next().children().removeClass('selected')
                $(tagValid).addClass('selected')
                item.value = $(tagValid).attr('data-rel')
                if ($(item).attr('data-rel') === 'dropdown' || $(item).attr('data-rel') === 'modal') {
                  $(item.parentNode).children('ul').removeClass('dropdown-open')
                  $(item).prev().html($(tagValid).html())
                }
                $(item).trigger('input')
              }
            }
          })
        }
      })
    },

    // BEGIN FOR SELECT CHANGER
    baSwitchOn: function (selector) {
      $(selector).each(function (i, item) {
        if ($(item).attr('data-rel') !== 'modal' || $(item).attr('data-rel') !== 'dropdown') elAction(item)
        $(item).on('input touchend', function () { elAction(item) })
      })
      function elAction (els) {
        var currentName = els.getAttribute('data-name') || els.getAttribute('name')
        var scope = $(els).closest('[data-batype]') || $(D)
        var elsVal = els.value
        $(els).children().each(function (i, el) {
          el.removeAttribute('selected')
          scope.find('.' + currentName + '-' + el.value).hide()
          if (el.value === elsVal) {
            scope.find('.' + currentName + '-' + el.value).show()
          }
        })
      }
    },

    // SETUP RANGE SLIDER FOR INPUT ELEMENT
    baRange: function (selector) {
      $(selector).each(function (i, item) {
        var mainInput = item.parentNode.nextElementSibling
        item.value = mainInput ? parseInt(mainInput.value) : 0
        $(D).on('mousedown touchend', function (e) {
          $(item).closest('.ba-range-wrap').removeClass('range-focus')
          $(e.target).closest('.ba-range-wrap').addClass('range-focus')
        })
        $(mainInput).on('input touchend', function () {
          item.value = parseInt(mainInput.value)
        })
        $(item).on('input touchend', function () {
          var arrVal = (mainInput.value || '0').match(/[a-z|%]+|[^a-z|%]+/gi)
          arrVal[0] = this.value
          arrVal[1] = item.hasAttribute('data-no-unit') ? '' : (item.getAttribute('data-unit') || arrVal[1] || 'px')
          mainInput.value = arrVal.join('')
          $(mainInput).trigger('input')
        })
      })
    },

    // SETUP SPINNER FOR INPUT[NUMBER] ELEMENT
    baSpinner: function (selector) {
      $(selector).each(function (i, item) {
        $(item).wrap('<div class="ba-spinner-warp" />').parent().append('<span class="spinner-up">&#9650;</span><span class="spinner-down">&#9660;</span>')
        $(item).parent().children('span').each(function (i, el) {
          $(el).on('click touchstart', function () {
            var self = this
            var arrVal = (item.value || '0').match(/[a-z|%]+|[^a-z|%]+/gi)
            arrVal[0] = i === 0 ? parseInt(arrVal[0]) + 1 : parseInt(arrVal[0]) - 1
            arrVal[1] = item.hasAttribute('data-no-unit') ? '' : (item.getAttribute('data-unit') || arrVal[1] || 'px')
            item.value = arrVal.join('')
            if ($(self).closest('.group').length > 0 && !$(self).closest('.list-flush').hasClass('unlinked')) {
              $(self).closest('.group').find('input').each(function (i, el) {
                el.value = self.parentNode[s]('input').value
              })
            }
            $(item).trigger('input')
          })
        })
      })
    },
    // baSpinner('input[data-rel=spinner]')

    // FOR GROUP LINKS //////
    baGroupLinked: function (selector) {
      $(selector).each(function (i, els) {
        var groupEl = els.parentNode.parentNode
        var noLinked = [].filter.call(groupEl[sa]('input'), function (el) { return el.value !== groupEl[s]('input').value })
        if (noLinked.length) $(groupEl).addClass('unlinked')
        $(els).off().on('click touchstart', function (e) {
          $(groupEl).toggleClass('unlinked')
        })
        $(groupEl).on('keyup touchend', function (e) {
          var self = this
          if ($(e.target).hasClass('ba-input') && !$(self).hasClass('unlinked')) {
            $(self).find('input').each(function (i, el) {
              el.value = e.target.value
              $(el).trigger('input')
            })
          }
        })
      })
    },
    // baGroupLinked ('button[data-rel=group-linked]')

    // BEGIN SETUP ACTION FOR INPUT RESPONSIVE ELEMENT
    deviceParas: function (selector) {
      $(selector).each(function (i, els) {
        $(els).children().each(function (i, el) {
          $(els).children().removeClass('active')
          if (el.children[0]) $(el.children[0]).addClass('active')
          if (i === 0) {
            $(el).on('click touchstart', function (e) {
              if (e.target.nodeName.toLowerCase() !== 'ul') {
                $('.best-preview').attr('data-rel', e.target.getAttribute('data-id'))
                $('[data-id=' + (e.target.getAttribute('data-id')) + ']').each(function (i, item) {
                  $(item.parentNode.children).each(function (i, el) { if (el !== item) $(el).removeClass('active') })
                  $(item).addClass('active')
                })
              }
            })
          }
        })
      })
    },

    /// /// ##### OPEN POPUP ##### ////////////////
    openMediaPopup: function (mediaID, el) {
      var jversion = parseInt($(el).closest('[data-jversion]').attr('data-jversion') || 0)
      if (el) $(el.children[1]).on('click touchstart', function () { modalVal() }); else modalVal()
      function modalVal () {
        if ($('body > .ba-modal').length <= 0) { $('body').append('<div class="ba-modal"><div class="ba-modal-body"><span class="close">&times;</span><iframe id="iframe' + mediaID + '"></iframe>' + (jversion >= 4 ? '<a class="btn btn-primary button-save-selected">Select</a>' : '') + '</div></div>') }
        var modal = $('.ba-modal')
        var mediaIframe = modal.find('iframe')
        mediaIframe.attr('src', 'index.php?option=com_media&view=' + (jversion >= 4 ? 'media' : 'images') + '&tmpl=component&fieldid=' + mediaID)
        modal.show()
        mediaIframe.on('load', function () {
          var frameDoc = this.contentWindow.document
          $(jversion >= 4 ? D : frameDoc).find('.button-save-selected').on('click touchstart', function () {
            if (jversion >= 4) {
              var bgUrl = frameDoc[s]('.media-browser-item.selected .image-cropped').style.backgroundImage
              bgUrl = /^url\((['"]?)(.*)\1\)$/.exec(bgUrl)
              bgUrl = bgUrl ? bgUrl[2] : ''
              $('#' + mediaID).attr('value', bgUrl)
            } else $('#' + mediaID).attr('value', frameDoc.getElementById('f_url').value)
            modal.hide()
            $('#' + mediaID).trigger('input')
          })
        })
        modal.onclick = function (e) {
          if (e.target.parentNode !== modal) { modal.hide() } // Disable click on the '.ba-modal-body' tag
        }
      }
    },

    /// /////////// CREATE MEDIA MODAL FOR IMAGES ///////////////////////
    mediaModal: function (selector) {
      var self = this
      $(selector).each(function (i, el) {
        var mediaID = 'ba-media-' + Math.floor(Math.random() * 10000)
        el.children[0].setAttribute('id', mediaID)
        self.openMediaPopup(mediaID, el)
      })
    },

    // FOR ACCORDION
    baToggle: function (selector, firstOpen) {
      firstOpen = (typeof firstOpen !== 'undefined') ? firstOpen : true
      $(selector).each(function (i, els) {
        $(els).children().removeClass('active')
        if (firstOpen) $(els.children[0]).addClass('active')
        $(els.children).each(function (i, el) {
          if ($(el).is('h3')) {
            $(el).on('click touchstart', function (e) {
              $(els).children().removeClass('active')
              $(this).addClass('active')
            })
          }
        })
      })
    },

    // FOR TABS
    baTabs: function (selector, isOpen) {
      isOpen = (typeof isOpen !== 'undefined' && !isNaN(isOpen)) ? isOpen : 0
      $(selector).each(function (i, els) {
        var nav = $(els).children('ul')
        var content = $(els).children('div')
        content.removeClass('active')
        nav.children().removeClass('active')
        content.eq(isOpen).addClass('active')
        nav.children().eq(isOpen).addClass('active')
        if (nav.length > 0) {
          nav.children().each(function (i, el) {
            $(el).on('click touchstart', function (e) {
              var tabIndex = [].indexOf.call(this.parentNode.children, this)
              e.preventDefault()
              if ($(this).closest('.ba---devices').length > 0) {
                $('.ba---devices').removeClass('active')
                $('.ba---devices').eq(tabIndex).addClass('active')
              } else {
                content.removeClass('active')
                nav.children().removeClass('active')
                content.eq(tabIndex).addClass('active')
                nav.children().eq(tabIndex).addClass('active')
              }
            })
          })
        }
      })
    },

    // FOR NAVIGATION POSITIONS ELEMENT
    baPosInput: function (selector) {
      $(selector).each(function (i, item) {
        var checkArea = $(item).prev().children()
        checkArea.each(function (i, el) {
          if (el.getAttribute('data-area') === item.value) el.setAttribute('data-active', true)
          $(el).on('click touchstart', function () {
            checkArea.removeAttr('data-active')
            item.value = this.getAttribute('data-area')
            this.setAttribute('data-active', true)
            $(el.parentNode).next().trigger('input')
          })
        })
      })
    },

    // CHECK IF SECTION IS NOT CHILDRENS
    itemEmpty: function (selector) {
      $(selector).each(function (i, el) {
        if (el.children.length <= 0) { el.classList.add('ba---empty') } else { el.classList.remove('ba---empty') }
      })
    },

    /// /////////// CREATE TINYMCE INIT ///////////////////////
    tinymceInits: function (el) {
      var self = this
      tinymce.init({
        selector: el || '.ba-editor',
        file_picker_types: 'image',
        file_picker_callback: function (callback, value, meta) {
          self.openMediaPopup(callback)
          return false
        },
        menubar: false,
        statusbar: false,
        toolbar_item_size: 'small',
        relative_urls: true,
        entity_encoding: 'raw',
        plugins: 'autolink,lists,image,charmap,print,preview,anchor,pagebreak,code,save,importcss,searchreplace,insertdatetime,link,fullscreen,table,emoticons,media,hr,directionality,paste,visualchars,visualblocks,nonbreaking,template,print,wordcount,advlist,autosave',
        toolbar: 'bold italic numlist bullist alignleft aligncenter alignright outdent indent link image table | forecolor backcolor fontselect fontsizeselect formatselect underline strikethrough alignjustify | removeformat subscript superscript charmap hr searchreplace ltr rtl media anchor code visualchars visualblocks nonbreaking undo redo'
      })
      if (typeof jQuery !== 'undefined' && jQuery.ui && jQuery.ui.dialog) {
        jQuery.widget('ui.dialog', jQuery.ui.dialog, {
          _allowInteraction: function (event) {
            return !!jQuery(event.target).closest('[class*="tinymce"]').length || this._super(event)
          }
        })
      }
    },

    // GET VALUES OF BASIC DATA IN ACCORDION
    basicData: function (selector) {
      return [].map.call(selector + '' === selector ? D[sa](selector) : selector, function (els) {
        return [].reduce.call(els[sa]('.ba-input'), function (data, el) {
          data[el.getAttribute('data-name') || el.name] = el.className.indexOf('ba-editor') >= 0 && tinymce.get(el.id) ? tinymce.get(el.id).getContent() : el.value
          return data
        }, {})
      }, [])
    },

    // GET DATA JSON
    oGetJson: function (selector) {
      selector = (typeof selector !== 'undefined') ? selector : '#ba-form-content'
      return JSON.parse((selector + '' === selector ? D[s](selector) : selector).value) // JSON.parse=json_decode, JSON.stringify=json_encode
    },

    // SET DATA JSON
    oSetJson: function (jsonData, selector) {
      selector = (typeof selector !== 'undefined') ? selector : '#ba-form-content';
      (selector + '' === selector ? D[s](selector) : selector).innerHTML = JSON.stringify(jsonData || [])
    },

    setCSS: function () {
      function oGroup (obj) {
        return [].map.call($(obj).closest(obj.getAttribute('data-name').indexOf('hadow') > -1 ? '.list-flush' : '.group')[sa]('.ba-input'), function (el) {
          return $(el).hasClass('switch') ? (el.checked ? 'inset' : '') : el.value
        }, []).join(' ')
      }
      function oBackground (obj) {
        if ($(obj).closest('.ba-background').length > 0) {
          var bgs = ''; var gradient = []; var bgImage = []
          var bgType = $(obj).closest('.ba-background').find('[data-name="backgroundType"]').attr('value')
          $(obj).closest('.ba-background').find('.backgroundType-' + bgType + ' .ba-input').each(function (i, el) {
            if (bgType === 'color') { bgs = el.value }
            if (bgType === 'gradient') { gradient.push(el.value) }
            if (bgType === 'image') { bgImage.push(el.value) }
          })
          if (bgType === 'gradient') {
            bgs = gradient[4] + '-gradient(' + (gradient[4] === 'linear' ? gradient[5] : 'at ' + gradient[6]) + ', ' + gradient[0] + ' ' + gradient[2] + ',' + gradient[1] + ' ' + gradient[3] + ')'
          }
          if (bgType === 'image') {
            bgs = (bgImage[0] !== '' ? 'url(' + bgImage[0] + ')' : '') + (bgImage[1] !== '' ? ' ' + bgImage[1] : ' 0 0') + (bgImage[4] !== '' ? '/' + bgImage[4] : '') + ' ' + bgImage[2] + ' ' + bgImage[3]
          }
          return bgs
        }
      }
      $('.skins-source [data-rel="accordionlist"] .ba-input').each(function (i, el) {
        $(el).on('input change touchend', function (e) {
          var hover = $(this).closest('[data-batype]').hasClass('hover')
          var action = $(this).closest('.ba-action')
          var items = $((!hover && action ? ':not(.active)' : (hover && action ? '.active' : '')) + '.ba--' + $(this).closest('[data-batype]').attr('data-batype'))
          items.each(function (i, el) {
            var dataName = e.target.getAttribute('data-name')
            dataName = dataName.indexOf('icon') > -1 ? dataName.slice(5) : dataName
            if (dataName in document.body.style) {
              el.style[dataName] = $(e.target).closest('.group').length > 0 || dataName.indexOf('hadow') > -1 ? oGroup(e.target) : e.target.value
            } else {
              if ($(e.target).closest('.ba-background').length > 0) {
                el.style.background = oBackground(e.target)
              }
            }
          })
        })
      })
    }

  // END PROTOTYPE OF MAIN CLASS
  }
  W[fn] = Main
  // END FUNCTION ///////////////////
})(window, document, window.weeKit ? weeKit : jQuery, 'BaForm')
