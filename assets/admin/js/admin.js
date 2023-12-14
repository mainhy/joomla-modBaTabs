/**
 * oAdmin - javascript setting
 * Copyright (c) 2011 BestAddon.com/vtem.net
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */
/* global jQuery, BaForm, tinymce, Joomla, modName,ajaxurl */
; (function (win, doc, $) {
  // BEGIN FUNCTION ///////////////////
  'use strict'
  var bf = new BaForm()
  var cms = JSON.parse($('.joomla-script-options').text())
  $.fn.oMain = function () {
    return this.each(function () {
      var Obj = $(this)
      var jsonData = bf.oGetJson()

      var disableArray = ['autoPlay', 'autoplayDelay', 'pauseOnHover', 'breakPoint', 'breakPointWidth', 'styleMode', 'background', 'color', 'borderColor', 'fontFamily', 'fontSize', 'fontWeight', 'textTransform']
      $.each(disableArray, function () {
        $('[data-name="' + this + '"]').closest('.ba-control').addClass('is-pro')
      })

      // SET values for the item(area,section,block) in dialog
      function oDialogSetValue (data, dialog) {
        $.each(data, function (i, item) {
          if (i !== 'children') {
            var oInput = $('[data-name="' + i + '"]', dialog)
            var oValue = item && item.split('§§')[$(dialog).hasClass('hover') ? 1 : 0]
            if (oInput.is('[data-group]')) { // Check the Element has attributes "data-group"
              var listInput = oValue.split('||')
              var equalInput = listInput.filter(function (val) { return val !== listInput[0] }) // return parseInt(val) !== parseInt(listInput[0])
              $.each(listInput, function (i, value) {
                var groupLink = oInput.closest('.list-flush').hasClass('group')
                var item = oInput.closest('.list-flush').children().eq(groupLink ? i + 1 : i).find('[data-name]')
                if (item.hasClass('switch') && value > 0) { item.attr('checked', true) } else { item.removeAttr('checked') }
                if (groupLink && equalInput.length) oInput.closest('.list-flush').addClass('unlinked')
                item.val(value)
              })
            } else {
              oInput.val(oInput.is('select[multiple]') ? oValue.split(',') : oValue)
            }
          }
        })
      }

      // GET values for the item(area,section,block) in dialog
      function oDialogGetValue (data, dialog) {
        $('.ba-input', dialog).each(function (i, item) {
          if ($(item).closest('[data-batype]').is(dialog) && data.id === $(dialog).attr('data-batype')) {
            var name = $(item).data('name')
            var oValue = data[name] ? data[name].split('§§') : []
            if (item.hasAttribute('data-group')) {
              var valGroup = $(item).closest('.list-flush').find('[data-name]').map(function (i, el) {
                return ($(el).hasClass('switch') ? (el.checked ? 1 : 0) : el.value)
              }).get().join('||')
              if (valGroup) oValue[$(dialog).hasClass('hover') ? 1 : 0] = valGroup
            } else {
              oValue[$(dialog).hasClass('hover') ? 1 : 0] = $(item).val()
            }
            data[name] = oValue.join('§§')
          }
        })
      }

      // Find a item(area,section,block) in JSON
      function oActionItem (jData, objectId, status) {
        var dialog = doc.querySelector('[data-batype*="' + objectId + '"]')
        jData.forEach(function (oList, idx) {
          for (var key in oList) {
            if (oList.hasOwnProperty(key) && oList[key] === objectId) {
              if (status === 'setValue') { oDialogSetValue(oList, dialog) }
              if (status === 'getValue') { oDialogGetValue(oList, dialog) }
            } else if (Array.isArray(oList[key])) {
              oActionItem(oList[key], objectId, status)
            }
          }
        })
      }

      // BEGIN SET VALUES FOR ALL PARAS ////////////
      $('[data-batype]', Obj).each(function (i, el) {
        oActionItem(jsonData, $(el).attr('data-batype'), 'setValue')
      })
      // END SET VALUES FOR ALL PARAS ////////////

      // BEGIN GET VALUES FROM ALL PARAS ////////////
      $('[data-name]', Obj).each(function (i, el) {
        $(el).on('input change touchend', function (e) {
          oActionItem(jsonData, $(e.target).closest('[data-batype]').not('.source-basic').attr('data-batype'), 'getValue')
          bf.oSetJson(jsonData)
        })
      })
      // END GET VALUES FROM ALL PARAS ////////////

      /* //////////////////
      //// BEGIN BASIC DATA IN ACCORDION /////////
      ////////////////// */
      var accordion = doc.querySelector('.ba__basic_data')
      var accordionFirstHtml = accordion.children[0].outerHTML
      $(accordion)
        .accordion({
          active: false,
          collapsible: true,
          animate: 500,
          header: '> div > h3',
          icons: false,
          activate: function (event, ui) {
            ui.newPanel.on('input change', '[data-name^=header]', function (e) {
              ui.newHeader.find('.ba__move + span').html(this.value)
            })
          }
        })
        .sortable({
          axis: 'y',
          handle: '.ba__move',
          placeholder: 'ba-sortable-placeholder',
          start: function (e, ui) {
            $('.ba-editor', ui.item).each(function (i, el) {
              tinymce.execCommand('mceRemoveEditor', false, el.id)
            })
          },
          stop: function (event, ui) {
            $(this).accordion('refresh')
            $('.ba-editor', ui.item).each(function (i, el) {
              tinymce.execCommand('mceAddEditor', false, el.id)
            })
          }
        })
        .parent().on('click touchstart', '.panel-heading > i', function (e) {
          /// /////////// DELETE ONE ITEM ///////////////////////
          if ($(e.target).hasClass('ba__remove') && accordion.children.length > 1) {
            $(this).closest('div').remove()
            $(accordion).accordion('refresh')
          }
          /// /////////// CLONE ONE ITEM ///////////////////////
          if ($(e.target).hasClass('ba__clone')) {
            if (win.wp) $('.ba-editor', accordion).each(function (i, el) { wp.editor.remove(el.id) })
            else tinymce.remove()
            var accordionItem = e.target.closest('.accordion-basic')
            $(accordionItem).after(accordionItem.outerHTML).find('.ba-editor').attr('id', 'ba' + Math.floor(Math.random() * 10000))
            $(accordion).accordion('refresh').accordion('option', 'active', false)
          }
          if ($(e.target).hasClass('ba__basic-add')) {
            $(accordion).prepend(accordionFirstHtml).accordion('refresh').accordion('option', 'active', 0)
            $(accordion).children().first().find('.ba__move + span').empty().end()
              .find('[data-name^=header]').val('Title').end()
              .find('.ba-editor').attr('id', 'ba' + Math.floor(Math.random() * 10000))
          }
          formAction(accordion)
        })
      /* //////////////////
      //// END BASIC DATA IN ACCORDION /////////
      ////////////////// */

      // BEGIN SET VALUES FOR HOVER/ACTIVE ////////////
      $('legend i').on('click', function (e) {
        $(this).siblings().removeClass('active')
        $(this).addClass('active').closest('[data-batype]').toggleClass('hover')
        oActionItem(jsonData, $(this).closest('[data-batype]').attr('data-batype'), 'setValue')
        formAction(this.parentNode.parentNode.parentNode)
      })
      // END SET VALUES FOR HOVER/ACTIVE ////////////

      function formAction (selector) {
        bf.baRange(selector.querySelectorAll('.ba-range[data-rel="range"]'))
        bf.mediaModal(selector.querySelectorAll('.media-append'))
        bf.baSwitchOn(selector.querySelectorAll('.select-group'))
        bf.selectGroup(selector.querySelectorAll('select[data-rel]'))
        bf.baToggle(selector.querySelectorAll('[data-rel="accordionlist"]'))
        bf.baTabs(selector.querySelectorAll('[data-rel="tablist"]'))
        bf.baSpinner(selector.querySelectorAll('input[data-rel="spinner"]'))
        bf.baGroupLinked(selector.querySelectorAll('[data-rel="group-linked"]'))
        bf.baPicker(selector.querySelectorAll('.tinycolor'))
        bf.fontIconModal(selector)
        bf.tinymceInits()
      }

      if (jQuery().chosen) $('select').chosen('destroy')
      $('.ba-editor', this).each(function (i, el) {
        if (!el.hasAttribute('id')) el.id = 'ba-editor-' + Math.floor(Math.random() * 10000)
        // tinymce.execCommand('mceAddEditor', false, el.id)
      })
      formAction(this)
      Obj.css({ opacity: 1, height: 'auto', overflow: 'visible' })
    }) // END THIS.FOREACH /////////
  }

  // GET VALUES OF BASIC DATA IN ACCORDION AND SET IT TO TEXTAREA BEFORE SAVE MODULE
  $(doc).ready(function () {
    var applyButton = doc.querySelector('.button-apply')
    applyButton.removeAttribute('onclick')
    applyButton.parentNode.removeAttribute('task')
    applyButton.onclick = function () {
      var formBasic = bf.basicData('.accordion-basic')
      if (formBasic.length > 0) {
        var jsonData = bf.oGetJson()
        jsonData[0]['children'][0]['children'] = formBasic
        bf.oSetJson(jsonData)
        setTimeout(function () {
          Joomla.submitbutton('module.apply')
        }, 100)
      }
    }
    $('.ba-preview').on('click touchstart', function () {
      var formBasic = bf.basicData('.accordion-basic')
      if (formBasic.length > 0) {
        var jsonData = bf.oGetJson()
        jsonData[0]['children'][0]['children'] = formBasic
        bf.oSetJson(jsonData)
        runAjax()
      }
      return false
    })
  })

  win.onload = function () {
    $('.ba-manager').oMain() /* bf.basicData('.accordion-basic') */
    $('.ba-tip, .tooltip').tooltip({ classes: { 'ui-tooltip': 'ba---tooltip' }, track: true })
  }

  function runAjax () {
    var baFormContent = $('textarea#ba-form-content').val()
    if (typeof ajaxurl !== 'undefined') { // FOR Wordpress
      $.post(ajaxurl, { 'action': 'basic_save', 'id': $('#post_ID').val(), 'ba-form-content': baFormContent }, function (response) { })
    } else { // FOR Joomla
      $.post(cms['system.paths']['root'] + '/index.php', { 'option': 'com_ajax', 'module': modName.replace('mod_', ''), 'method': 'baPreview', 'format': 'raw', 'ba-form-content': baFormContent }, function (response) {
        $('#ba-modal-preview').html(response).dialog({
          modal: true,
          resizable: false,
          closeText: '×',
          classes: {
            'ui-dialog': 'ba-dialog',
            'ui-dialog-titlebar': 'ba-header',
            'ui-dialog-titlebar-close': 'ba-button ba-close',
            'ui-dialog-buttonpane': 'ba-footer'
          },
          close: function (event, ui) {
            $(this).dialog('close').dialog('destroy')
          }
        }).dialog('open')
      })
    }
  }
  // END FUNCTION ///////////////////
})(window, document, jQuery)
