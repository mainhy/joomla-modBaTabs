// baPicker v1.0
// https://bestaddon.com
// BestAddon, MIT License

(function (W, D, M, fn) {
  function Main (obj, parent) {
    var width = 'offsetWidth'
    var height = 'offsetHeight'
    var rect = 'getBoundingClientRect'
    var P = D.createElement('div')
    P.id = 'picker_wrapper'
    P.innerHTML = '<div class="picker_container"><span class="picker_selector"><i></i></span><span class="picker_hue"><i></i></span><span class="picker_opacity"><i></i></span></div><input/></div>'
    var V = P.children[1]
    var C = P.children[0].children
    var SV = C[0]
    var H = C[1]
    var A = C[2]
    var svSlider = SV.children[0]
    var hSlider = H.children[0]
    var aSlider = A.children[0]
    var isOpen = false
    var pickerEdit = true
    var self = this
    self.set = function (color) { return (new Colour(color || obj.value || obj.getAttribute('data-color') || 'rgb(255, 0, 0)')) }
    self.color = { h: 0, s: 1, v: 1, a: 1 }
    self.points = {
      'picker_selector': { down: false },
      'picker_hue': { down: false, vertical: true },
      'picker_opacity': { down: false, vertical: true }
    }

    if (!obj.parentNode.classList.contains('ba-color-wrap')) {
      var oWrap = document.createElement('div')
      oWrap.setAttribute('class', 'ba-color-wrap')
      obj.parentNode.insertBefore(oWrap, obj)
      oWrap.appendChild(obj)
    }
    if (obj.previousElementSibling) obj.parentNode.removeChild(obj.previousElementSibling)
    obj.insertAdjacentHTML('beforebegin', '<b><i style="background-color:' + self.set().toRgbString() + '">&nbsp;</i></b>')
    if (pickerEdit) {
      obj.parentNode.addEventListener('click', function () { self.show() })
      V.addEventListener('keyup', function () { self.parseColor(V.value); self.updateSample(V.value) })
    } else {
      V.style.display = 'none';
      ['click', 'input'].forEach(function (event) {
        obj.addEventListener(event, function () {
          self.show()
        })
      })
    }

    self.parseColor = function (setColor) {
      self.color = setColor !== void 0 ? self.set(setColor).toHsv() : self.set().toHsv()
      SV.style.backgroundColor = 'hsl(' + self.color.h + ', 100%, 50%)'
      svSlider.style.right = (SV[width] - (svSlider[width] / 2) - (SV[width] * +self.color.s)) + 'px'
      svSlider.style.top = (SV[height] - (svSlider[height] / 2) - (SV[height] * +self.color.v)) + 'px'
      hSlider.style.top = (H[height] - (hSlider[height] / 2) - (H[height] * +self.color.h / 360)) + 'px'
      aSlider.style.top = (A[height] - (aSlider[height] / 2) - (A[height] * +self.color.a)) + 'px'
    }

    self.show = function () {
      if (!isOpen) {
        self.hide(D.getElementById(P.id));
        (parent || D.body).appendChild(P)
        if (parent === void 0) {
          if (obj[rect]().left + P[width] > W.innerWidth) { P.style.right = '50px'; P.style.left = 'auto' } else { P.style.left = (obj[rect]().left + window.scrollX) + 'px' }
          P.style.top = (obj[rect]().bottom + window.scrollY) + 'px'
        }
        isOpen = true
      }
      if (self.set().isValid()) {
        self.parseColor()
        self.bindEvents()
        self.updateSample()
      }
    }

    self.hide = function (el) {
      el = (el !== void 0) ? el : P
      if (el && el.parentNode) el.parentNode.removeChild(el);
      [].forEach.call(C, function (el) { el.onmousemove = null; el.onmousedown = null; el.children[0].onmousedown = null })
      isOpen = false
    }

    // Update the saturation and value variables.
    self.update_picker_selector = function (el, x, y) {
      self.color.s = x / (el[width] - 2)
      self.color.v = 1 - y / (el[height] - 2)
      self.updateSample()
    }

    // Update the hue variable.
    self.update_picker_hue = function (el, x, y) {
      self.color.h = (1 - y / (el[height] - 2)) * 360
      SV.style.backgroundColor = 'hsl(' + self.color.h + ', 100%, 50%)'
      self.updateSample()
    }

    // Update the alpha variable.
    self.update_picker_opacity = function (el, x, y) {
      self.color.a = 1 - y / (el[height] - 2)
      self.updateSample()
    }

    self.colorFormat = function (color) {
      var colorFormat = color !== void 0 ? self.set(color).getFormat() : self.set().getFormat()
      var preColor = self.set(self.color)
      var o = color !== void 0 ? obj.value : obj.value = V.value
      if (colorFormat === 'hsl') {
        o.value = preColor.toHslString()
      } else if (colorFormat === 'hex' || colorFormat === 'name') {
        if (preColor.getAlpha() === 1) o.value = preColor.toHexString()
        else o.value = preColor.toRgbString()
      } else {
        o.value = V.value = preColor.toRgbString()
      }
    }

    // Update the selected color sample. The on_change function is called here.
    self.updateSample = function (val) {
      var preColor = self.set(self.color)
      obj.previousElementSibling.children[0].style.backgroundColor = preColor.toHslString()
      var colorFormat = val !== void 0 ? self.set(val).getFormat() : self.set().getFormat()
      if (colorFormat === 'hsl') {
        if (val !== void 0) obj.value = preColor.toHslString()
        else obj.value = V.value = preColor.toHslString()
      } else if (colorFormat === 'hex' || colorFormat === 'name') {
        if (preColor.getAlpha() === 1) {
          if (val !== void 0) obj.value = preColor.toHexString()
          else obj.value = V.value = preColor.toHexString()
        } else {
          if (val !== void 0) obj.value = preColor.toRgbString()
          else obj.value = V.value = preColor.toRgbString()
        }
      } else {
        if (val !== void 0) obj.value = preColor.toRgbString()
        else obj.value = V.value = preColor.toRgbString()
      }
      trigger(obj, 'input')
      if (self.on_change) {
        self.on_change(self.color)
      }
    }

    // Handle slider movements.
    self.mouseMove = function (e, el, self, override) {
      if (override || (self.points[el.className] && self.points[el.className].down)) {
        var x = e.screenX - el[rect]().left
        var y = e.clientY - el[rect]().top
        var sliderInfo = self.points[el.className]
        var slider = el.children[0] || el.children[0]
        if (!slider) return
        if (!sliderInfo.vertical) {
          x = M.min(M.max(x - slider[width] / 2, -(slider[width] / 2)), el[width] - slider[width] / 2 - 2)
          slider.style.left = x + 'px'
        }
        y = M.min(M.max(y - slider[height] / 2, -(slider[height] / 2)), el[height] - slider[height] / 2 - 2)
        slider.style.top = y + 'px'
        if (self['update_' + el.className]) {
          self['update_' + el.className](el, x + slider[width] / 2, y + slider[height] / 2)
        }
      }
    }

    // Bind mouse events.
    self.bindEvents = function () {
      [].forEach.call(C, function (el) {
        el.onmousedown = function (e) {
          D.onmousemove = function (e) { self.mouseMove(e, el, self) }
          self.points[this.className].down = true
          self.mouseMove(e, this, self, true)
          return false
        }
      })
      D.onmousedown = function (e) {
        if (e.target && !closest(e.target, '#' + P.id) && e.target !== obj) {
          self.hide()
        }
      }
      D.onmouseup = function (e) {
        for (var name in self.points) { self.points[name].down = false }
        D.onmousemove = null
      };
      [].forEach.call(D.querySelectorAll('iframe'), function (el) {
        (el.contentDocument || el.contentWindow.document).onmouseup = function (e) {
          for (var name in self.points) { self.points[name].down = false }
          self.hide()
        }
      })
    }

    /* event functions */
    self.on_done = null
    self.on_change = null
  }

  function trigger (el, ev) { var e = D.createEvent('HTMLEvents'); e.initEvent(ev, true, false); el.dispatchEvent(e) }
  // Closest to element
  function closest (e, n) { do { if (e.matches(n)) return e; e = e.parentElement || e.parentNode } while (e !== null && e.nodeType === 1);return null }
  var counter = 0

  // 'Colour' function modified from: https://github.com/bgrins/TinyColor
  function Colour (color) {
    color = (color) || ''
    // If input is already a Colour, return itself
    if (color instanceof Colour) return color
    // If we are called as a function, call using new instead
    if (!(this instanceof Colour)) return new Colour(color)

    var rgb = inputToRGB(color)
    this._originalInput = color
    this._r = rgb.r
    this._g = rgb.g
    this._b = rgb.b
    this._a = rgb.a
    this._roundA = M.round(100 * this._a) / 100

    // Don't let the range of [0,255] come back in [0,1].
    // Potentially lose a little bit of precision here, but will fix issues where
    // .5 gets interpreted as half of the total, instead of half of 1
    // If it was supposed to be 128, this was already taken care of by `inputToRgb`
    if (this._r < 1) { this._r = M.round(this._r) }
    if (this._g < 1) { this._g = M.round(this._g) }
    if (this._b < 1) { this._b = M.round(this._b) }
    this._ok = rgb.ok
    this._id = counter++
  }

  Colour.prototype = {
    isValid: function () {
      return this._ok
    },
    getOriginalInput: function () {
      return this._originalInput
    },
    getFormat: function () {
      return this._format
    },
    getAlpha: function () {
      return this._a
    },
    setAlpha: function (value) {
      this._a = boundAlpha(value)
      this._roundA = M.round(100 * this._a) / 100
      return this
    },
    toHsv: function () {
      var hsv = rgbToHsv(this._r, this._g, this._b)
      return { h: hsv.h * 360, s: hsv.s, v: hsv.v, a: this._a }
    },
    toHsvString: function () {
      var hsv = rgbToHsv(this._r, this._g, this._b)
      var h = M.round(hsv.h * 360); var s = M.round(hsv.s * 100); var v = M.round(hsv.v * 100)
      return (this._a === 1)
        ? 'hsv(' + h + ', ' + s + '%, ' + v + '%)'
        : 'hsva(' + h + ', ' + s + '%, ' + v + '%, ' + this._roundA + ')'
    },
    toHsl: function () {
      var hsl = rgbToHsl(this._r, this._g, this._b)
      return { h: hsl.h * 360, s: hsl.s, l: hsl.l, a: this._a }
    },
    toHslString: function () {
      var hsl = rgbToHsl(this._r, this._g, this._b)
      var h = M.round(hsl.h * 360); var s = M.round(hsl.s * 100); var l = M.round(hsl.l * 100)
      return (this._a === 1)
        ? 'hsl(' + h + ', ' + s + '%, ' + l + '%)'
        : 'hsla(' + h + ', ' + s + '%, ' + l + '%, ' + this._roundA + ')'
    },
    toHex: function (allow3Char) {
      return rgbToHex(this._r, this._g, this._b, allow3Char)
    },
    toHexString: function (allow3Char) {
      return '#' + this.toHex(allow3Char)
    },
    toHex8: function (allow4Char) {
      return rgbaToHex(this._r, this._g, this._b, this._a, allow4Char)
    },
    toHex8String: function (allow4Char) {
      return '#' + this.toHex8(allow4Char)
    },
    toRgb: function () {
      return { r: M.round(this._r), g: M.round(this._g), b: M.round(this._b), a: this._a }
    },
    toRgbString: function () {
      return (this._a === 1)
        ? 'rgb(' + M.round(this._r) + ', ' + M.round(this._g) + ', ' + M.round(this._b) + ')'
        : 'rgba(' + M.round(this._r) + ', ' + M.round(this._g) + ', ' + M.round(this._b) + ', ' + this._roundA + ')'
    },
    toPercentageRgb: function () {
      return { r: M.round(bound01(this._r, 255) * 100) + '%', g: M.round(bound01(this._g, 255) * 100) + '%', b: M.round(bound01(this._b, 255) * 100) + '%', a: this._a }
    },
    toPercentageRgbString: function () {
      return (this._a === 1)
        ? 'rgb(' + M.round(bound01(this._r, 255) * 100) + '%, ' + M.round(bound01(this._g, 255) * 100) + '%, ' + M.round(bound01(this._b, 255) * 100) + '%)'
        : 'rgba(' + M.round(bound01(this._r, 255) * 100) + '%, ' + M.round(bound01(this._g, 255) * 100) + '%, ' + M.round(bound01(this._b, 255) * 100) + '%, ' + this._roundA + ')'
    },
    toName: function () {
      if (this._a === 0) return 'transparent'
      if (this._a < 1) return false
      return hexNames[rgbToHex(this._r, this._g, this._b, true)] || false
    }
  }

  // Given a string or object, convert that input to RGB
  // Possible string inputs:
  //
  //     "red"
  //     "#f00" or "f00"
  //     "#ff0000" or "ff0000"
  //     "#ff000000" or "ff000000"
  //     "rgb 255 0 0" or "rgb (255, 0, 0)"
  //     "rgb 1.0 0 0" or "rgb (1, 0, 0)"
  //     "rgba (255, 0, 0, 1)" or "rgba 255, 0, 0, 1"
  //     "rgba (1.0, 0, 0, 1)" or "rgba 1.0, 0, 0, 1"
  //     "hsl(0, 100%, 50%)" or "hsl 0 100% 50%"
  //     "hsla(0, 100%, 50%, 1)" or "hsla 0 100% 50%, 1"
  //     "hsv(0, 100%, 100%)" or "hsv 0 100% 100%"
  //
  function inputToRGB (color) {
    var rgb = { r: 0, g: 0, b: 0 }
    var a = 1
    var s = null
    var v = null
    var l = null
    var ok = false
    var format = false
    if (typeof color === 'string') {
      color = stringInputToObject(color)
    }
    if (typeof color === 'object') {
      if (isValidCSSUnit(color.r) && isValidCSSUnit(color.g) && isValidCSSUnit(color.b)) {
        rgb = rgbToRgb(color.r, color.g, color.b)
        ok = true
        format = String(color.r).substr(-1) === '%' ? 'prgb' : 'rgb'
      } else if (isValidCSSUnit(color.h) && isValidCSSUnit(color.s) && isValidCSSUnit(color.v)) {
        s = convertToPercentage(color.s)
        v = convertToPercentage(color.v)
        rgb = hsvToRgb(color.h, s, v)
        ok = true
        format = 'hsv'
      } else if (isValidCSSUnit(color.h) && isValidCSSUnit(color.s) && isValidCSSUnit(color.l)) {
        s = convertToPercentage(color.s)
        l = convertToPercentage(color.l)
        rgb = hslToRgb(color.h, s, l)
        ok = true
        format = 'hsl'
      }
      if (color.hasOwnProperty('a')) {
        a = color.a
      }
    }
    a = boundAlpha(a)
    return {
      ok: ok,
      format: color.format || format,
      r: M.min(255, M.max(rgb.r, 0)),
      g: M.min(255, M.max(rgb.g, 0)),
      b: M.min(255, M.max(rgb.b, 0)),
      a: a
    }
  }

  // Conversion Functions
  // --------------------

  // Handle bounds / percentage checking to conform to CSS color spec
  // <http://www.w3.org/TR/css3-color/>
  // *Assumes:* r, g, b in [0, 255] or [0, 1]
  // *Returns:* { r, g, b } in [0, 255]
  function rgbToRgb (r, g, b) {
    return {
      r: bound01(r, 255) * 255,
      g: bound01(g, 255) * 255,
      b: bound01(b, 255) * 255
    }
  }

  // Converts an RGB color value to HSL.
  // *Assumes:* r, g, and b are contained in [0, 255] or [0, 1]
  // *Returns:* { h, s, l } in [0,1]
  function rgbToHsl (r, g, b) {
    r = bound01(r, 255)
    g = bound01(g, 255)
    b = bound01(b, 255)
    var max = M.max(r, g, b); var min = M.min(r, g, b)
    var h; var s; var l = (max + min) / 2
    if (max === min) {
      h = s = 0 // achromatic
    } else {
      var d = max - min
      s = l > 0.5 ? d / (2 - max - min) : d / (max + min)
      switch (max) {
        case r: h = (g - b) / d + (g < b ? 6 : 0); break
        case g: h = (b - r) / d + 2; break
        case b: h = (r - g) / d + 4; break
      }
      h /= 6
    }
    return { h: h, s: s, l: l }
  }

  // Converts an HSL color value to RGB.
  // *Assumes:* h is contained in [0, 1] or [0, 360] and s and l are contained [0, 1] or [0, 100]
  // *Returns:* { r, g, b } in the set [0, 255]
  function hslToRgb (h, s, l) {
    var r, g, b
    h = bound01(h, 360)
    s = bound01(s, 100)
    l = bound01(l, 100)
    function hue2rgb (p, q, t) {
      if (t < 0) t += 1
      if (t > 1) t -= 1
      if (t < 1 / 6) return p + (q - p) * 6 * t
      if (t < 1 / 2) return q
      if (t < 2 / 3) return p + (q - p) * (2 / 3 - t) * 6
      return p
    }
    if (s === 0) {
      r = g = b = l // achromatic
    } else {
      var q = l < 0.5 ? l * (1 + s) : l + s - l * s
      var p = 2 * l - q
      r = hue2rgb(p, q, h + 1 / 3)
      g = hue2rgb(p, q, h)
      b = hue2rgb(p, q, h - 1 / 3)
    }
    return { r: r * 255, g: g * 255, b: b * 255 }
  }

  // Converts an RGB color value to HSV
  // *Assumes:* r, g, and b are contained in the set [0, 255] or [0, 1]
  // *Returns:* { h, s, v } in [0,1]
  function rgbToHsv (r, g, b) {
    r = bound01(r, 255)
    g = bound01(g, 255)
    b = bound01(b, 255)
    var max = M.max(r, g, b); var min = M.min(r, g, b)
    var h; var s; var v = max
    var d = max - min
    s = max === 0 ? 0 : d / max
    if (max === min) {
      h = 0 // achromatic
    } else {
      switch (max) {
        case r: h = (g - b) / d + (g < b ? 6 : 0); break
        case g: h = (b - r) / d + 2; break
        case b: h = (r - g) / d + 4; break
      }
      h /= 6
    }
    return { h: h, s: s, v: v }
  }

  // Converts an HSV color value to RGB.
  // *Assumes:* h is contained in [0, 1] or [0, 360] and s and v are contained in [0, 1] or [0, 100]
  // *Returns:* { r, g, b } in the set [0, 255]
  function hsvToRgb (h, s, v) {
    h = bound01(h, 360) * 6
    s = bound01(s, 100)
    v = bound01(v, 100)
    var i = M.floor(h)
    var f = h - i
    var p = v * (1 - s)
    var q = v * (1 - f * s)
    var t = v * (1 - (1 - f) * s)
    var mod = i % 6
    var r = [v, q, p, p, t, v][mod]
    var g = [t, v, v, q, p, p][mod]
    var b = [p, p, t, v, v, q][mod]
    return { r: r * 255, g: g * 255, b: b * 255 }
  }

  // Converts an RGB color to hex
  // Assumes r, g, and b are contained in the set [0, 255]
  // Returns a 3 or 6 character hex
  function rgbToHex (r, g, b, allow3Char) {
    var hex = [
      pad2(M.round(r).toString(16)),
      pad2(M.round(g).toString(16)),
      pad2(M.round(b).toString(16))
    ]
    // Return a 3 character hex if possible
    if (allow3Char && hex[0].charAt(0) === hex[0].charAt(1) && hex[1].charAt(0) === hex[1].charAt(1) && hex[2].charAt(0) === hex[2].charAt(1)) {
      return hex[0].charAt(0) + hex[1].charAt(0) + hex[2].charAt(0)
    }
    return hex.join('')
  }

  // Converts an RGBA color plus alpha transparency to hex
  // Assumes r, g, b are contained in the set [0, 255] and
  // a in [0, 1]. Returns a 4 or 8 character rgba hex
  function rgbaToHex (r, g, b, a, allow4Char) {
    var hex = [
      pad2(M.round(r).toString(16)),
      pad2(M.round(g).toString(16)),
      pad2(M.round(b).toString(16)),
      pad2(convertDecimalToHex(a))
    ]
    // Return a 4 character hex if possible
    if (allow4Char && hex[0].charAt(0) === hex[0].charAt(1) && hex[1].charAt(0) === hex[1].charAt(1) && hex[2].charAt(0) === hex[2].charAt(1) && hex[3].charAt(0) === hex[3].charAt(1)) {
      return hex[0].charAt(0) + hex[1].charAt(0) + hex[2].charAt(0) + hex[3].charAt(0)
    }
    return hex.join('')
  }

  // Big List of Colors
  // ------------------
  // <http://www.w3.org/TR/css3-color/#svg-color>
  var names = Colour.names = {
    aliceblue: 'f0f8ff',
    antiquewhite: 'faebd7',
    aqua: '0ff',
    aquamarine: '7fffd4',
    azure: 'f0ffff',
    beige: 'f5f5dc',
    bisque: 'ffe4c4',
    black: '000',
    blanchedalmond: 'ffebcd',
    blue: '00f',
    blueviolet: '8a2be2',
    brown: 'a52a2a',
    burlywood: 'deb887',
    burntsienna: 'ea7e5d',
    cadetblue: '5f9ea0',
    chartreuse: '7fff00',
    chocolate: 'd2691e',
    coral: 'ff7f50',
    cornflowerblue: '6495ed',
    cornsilk: 'fff8dc',
    crimson: 'dc143c',
    cyan: '0ff',
    darkblue: '00008b',
    darkcyan: '008b8b',
    darkgoldenrod: 'b8860b',
    darkgray: 'a9a9a9',
    darkgreen: '006400',
    darkgrey: 'a9a9a9',
    darkkhaki: 'bdb76b',
    darkmagenta: '8b008b',
    darkolivegreen: '556b2f',
    darkorange: 'ff8c00',
    darkorchid: '9932cc',
    darkred: '8b0000',
    darksalmon: 'e9967a',
    darkseagreen: '8fbc8f',
    darkslateblue: '483d8b',
    darkslategray: '2f4f4f',
    darkslategrey: '2f4f4f',
    darkturquoise: '00ced1',
    darkviolet: '9400d3',
    deeppink: 'ff1493',
    deepskyblue: '00bfff',
    dimgray: '696969',
    dimgrey: '696969',
    dodgerblue: '1e90ff',
    firebrick: 'b22222',
    floralwhite: 'fffaf0',
    forestgreen: '228b22',
    fuchsia: 'f0f',
    gainsboro: 'dcdcdc',
    ghostwhite: 'f8f8ff',
    gold: 'ffd700',
    goldenrod: 'daa520',
    gray: '808080',
    green: '008000',
    greenyellow: 'adff2f',
    grey: '808080',
    honeydew: 'f0fff0',
    hotpink: 'ff69b4',
    indianred: 'cd5c5c',
    indigo: '4b0082',
    ivory: 'fffff0',
    khaki: 'f0e68c',
    lavender: 'e6e6fa',
    lavenderblush: 'fff0f5',
    lawngreen: '7cfc00',
    lemonchiffon: 'fffacd',
    lightblue: 'add8e6',
    lightcoral: 'f08080',
    lightcyan: 'e0ffff',
    lightgoldenrodyellow: 'fafad2',
    lightgray: 'd3d3d3',
    lightgreen: '90ee90',
    lightgrey: 'd3d3d3',
    lightpink: 'ffb6c1',
    lightsalmon: 'ffa07a',
    lightseagreen: '20b2aa',
    lightskyblue: '87cefa',
    lightslategray: '789',
    lightslategrey: '789',
    lightsteelblue: 'b0c4de',
    lightyellow: 'ffffe0',
    lime: '0f0',
    limegreen: '32cd32',
    linen: 'faf0e6',
    magenta: 'f0f',
    maroon: '800000',
    mediumaquamarine: '66cdaa',
    mediumblue: '0000cd',
    mediumorchid: 'ba55d3',
    mediumpurple: '9370db',
    mediumseagreen: '3cb371',
    mediumslateblue: '7b68ee',
    mediumspringgreen: '00fa9a',
    mediumturquoise: '48d1cc',
    mediumvioletred: 'c71585',
    midnightblue: '191970',
    mintcream: 'f5fffa',
    mistyrose: 'ffe4e1',
    moccasin: 'ffe4b5',
    navajowhite: 'ffdead',
    navy: '000080',
    oldlace: 'fdf5e6',
    olive: '808000',
    olivedrab: '6b8e23',
    orange: 'ffa500',
    orangered: 'ff4500',
    orchid: 'da70d6',
    palegoldenrod: 'eee8aa',
    palegreen: '98fb98',
    paleturquoise: 'afeeee',
    palevioletred: 'db7093',
    papayawhip: 'ffefd5',
    peachpuff: 'ffdab9',
    peru: 'cd853f',
    pink: 'ffc0cb',
    plum: 'dda0dd',
    powderblue: 'b0e0e6',
    purple: '800080',
    rebeccapurple: '663399',
    red: 'f00',
    rosybrown: 'bc8f8f',
    royalblue: '4169e1',
    saddlebrown: '8b4513',
    salmon: 'fa8072',
    sandybrown: 'f4a460',
    seagreen: '2e8b57',
    seashell: 'fff5ee',
    sienna: 'a0522d',
    silver: 'c0c0c0',
    skyblue: '87ceeb',
    slateblue: '6a5acd',
    slategray: '708090',
    slategrey: '708090',
    snow: 'fffafa',
    springgreen: '00ff7f',
    steelblue: '4682b4',
    tan: 'd2b48c',
    teal: '008080',
    thistle: 'd8bfd8',
    tomato: 'ff6347',
    turquoise: '40e0d0',
    violet: 'ee82ee',
    wheat: 'f5deb3',
    white: 'fff',
    whitesmoke: 'f5f5f5',
    yellow: 'ff0',
    yellowgreen: '9acd32'
  }

  // Make it easy to access colors via `hexNames[hex]`
  var hexNames = Colour.hexNames = flip(names)

  // Utilities
  // ---------
  // `{ 'name1': 'val1' }` becomes `{ 'val1': 'name1' }`
  function flip (o) {
    var flipped = { }
    for (var i in o) {
      if (o.hasOwnProperty(i)) {
        flipped[o[i]] = i
      }
    }
    return flipped
  }

  // Return a valid alpha value [0,1] with all invalid values being set to 1
  function boundAlpha (a) {
    a = parseFloat(a)
    if (isNaN(a) || a < 0 || a > 1) a = 1
    return a
  }

  // Take input from [0, n] and return it as [0, 1]
  function bound01 (n, max) {
    if (isOnePointZero(n)) n = '100%'
    var processPercent = isPercentage(n)
    n = M.min(max, M.max(0, parseFloat(n)))
    // Automatically convert percentage into number
    if (processPercent) n = parseInt(n * max, 10) / 100
    // Handle floating point rounding errors
    if ((M.abs(n - max) < 0.000001)) return 1
    // Convert into [0, 1] range if it isn't already
    return (n % max) / parseFloat(max)
  }

  // Parse a base-16 hex value into a base-10 integer
  function parseIntFromHex (val) {
    return parseInt(val, 16)
  }

  // Need to handle 1.0 as 100%, since once it is a number, there is no difference between it and 1
  // <http://stackoverflow.com/questions/7422072/javascript-how-to-detect-number-as-a-decimal-including-1-0>
  function isOnePointZero (n) {
    return typeof n === 'string' && n.indexOf('.') !== -1 && parseFloat(n) === 1
  }

  // Check to see if string passed in is a percentage
  function isPercentage (n) {
    return typeof n === 'string' && n.indexOf('%') !== -1
  }

  // Force a hex value to have 2 characters
  function pad2 (c) {
    return c.length === 1 ? '0' + c : '' + c
  }

  // Replace a decimal with it's percentage value
  function convertToPercentage (n) {
    if (n <= 1) n = (n * 100) + '%'
    return n
  }

  // Converts a decimal to a hex value
  function convertDecimalToHex (d) {
    return M.round(parseFloat(d) * 255).toString(16)
  }
  // Converts a hex value to a decimal
  function convertHexToDecimal (h) {
    return (parseIntFromHex(h) / 255)
  }

  var matchers = (function () {
    // <http://www.w3.org/TR/css3-values/#integers>
    var CSS_INTEGER = '[-\\+]?\\d+%?'
    // <http://www.w3.org/TR/css3-values/#number-value>
    var CSS_NUMBER = '[-\\+]?\\d*\\.\\d+%?'
    // Allow positive/negative integer/number.  Don't capture the either/or, just the entire outcome.
    var CSS_UNIT = '(?:' + CSS_NUMBER + ')|(?:' + CSS_INTEGER + ')'
    // Actual matching.
    // Parentheses and commas are optional, but not required.
    // Whitespace can take the place of commas or opening paren
    var PERMISSIVE_MATCH3 = '[\\s|\\(]+(' + CSS_UNIT + ')[,|\\s]+(' + CSS_UNIT + ')[,|\\s]+(' + CSS_UNIT + ')\\s*\\)?'
    var PERMISSIVE_MATCH4 = '[\\s|\\(]+(' + CSS_UNIT + ')[,|\\s]+(' + CSS_UNIT + ')[,|\\s]+(' + CSS_UNIT + ')[,|\\s]+(' + CSS_UNIT + ')\\s*\\)?'
    return {
      CSS_UNIT: new RegExp(CSS_UNIT),
      rgb: new RegExp('rgb' + PERMISSIVE_MATCH3),
      rgba: new RegExp('rgba' + PERMISSIVE_MATCH4),
      hsl: new RegExp('hsl' + PERMISSIVE_MATCH3),
      hsla: new RegExp('hsla' + PERMISSIVE_MATCH4),
      hsv: new RegExp('hsv' + PERMISSIVE_MATCH3),
      hsva: new RegExp('hsva' + PERMISSIVE_MATCH4),
      hex3: /^#?([0-9a-fA-F]{1})([0-9a-fA-F]{1})([0-9a-fA-F]{1})$/,
      hex6: /^#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})$/,
      hex4: /^#?([0-9a-fA-F]{1})([0-9a-fA-F]{1})([0-9a-fA-F]{1})([0-9a-fA-F]{1})$/,
      hex8: /^#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})$/
    }
  })()

  // `isValidCSSUnit`
  // Take in a single string / number and check to see if it looks like a CSS unit
  // (see `matchers` above for definition).
  function isValidCSSUnit (color) {
    return !!matchers.CSS_UNIT.exec(color)
  }

  // `stringInputToObject`
  // Permissive string parsing.  Take in a number of formats, and output an object
  // based on detected format.  Returns `{ r, g, b }` or `{ h, s, l }` or `{ h, s, v}`
  function stringInputToObject (color) {
    color = color.trim().toLowerCase()
    var named = false
    if (names[color]) {
      color = names[color]
      named = true
    } else if (color === 'transparent') {
      return { r: 0, g: 0, b: 0, a: 0, format: 'name' }
    }
    // Try to match string input using regular expressions.
    // Keep most of the number bounding out of this function - don't worry about [0,1] or [0,100] or [0,360]
    // Just return an object and let the conversion functions handle that.
    // This way the result will be the same whether the Colour is initialized with string or object.
    var match
    if ((match = matchers.rgb.exec(color))) {
      return { r: match[1], g: match[2], b: match[3] }
    }
    if ((match = matchers.rgba.exec(color))) {
      return { r: match[1], g: match[2], b: match[3], a: match[4] }
    }
    if ((match = matchers.hsl.exec(color))) {
      return { h: match[1], s: match[2], l: match[3] }
    }
    if ((match = matchers.hsla.exec(color))) {
      return { h: match[1], s: match[2], l: match[3], a: match[4] }
    }
    if ((match = matchers.hsv.exec(color))) {
      return { h: match[1], s: match[2], v: match[3] }
    }
    if ((match = matchers.hsva.exec(color))) {
      return { h: match[1], s: match[2], v: match[3], a: match[4] }
    }
    if ((match = matchers.hex8.exec(color))) {
      return {
        r: parseIntFromHex(match[1]),
        g: parseIntFromHex(match[2]),
        b: parseIntFromHex(match[3]),
        a: convertHexToDecimal(match[4]),
        format: named ? 'name' : 'hex8'
      }
    }
    if ((match = matchers.hex6.exec(color))) {
      return {
        r: parseIntFromHex(match[1]),
        g: parseIntFromHex(match[2]),
        b: parseIntFromHex(match[3]),
        format: named ? 'name' : 'hex'
      }
    }
    if ((match = matchers.hex4.exec(color))) {
      return {
        r: parseIntFromHex(match[1] + '' + match[1]),
        g: parseIntFromHex(match[2] + '' + match[2]),
        b: parseIntFromHex(match[3] + '' + match[3]),
        a: convertHexToDecimal(match[4] + '' + match[4]),
        format: named ? 'name' : 'hex8'
      }
    }
    if ((match = matchers.hex3.exec(color))) {
      return {
        r: parseIntFromHex(match[1] + '' + match[1]),
        g: parseIntFromHex(match[2] + '' + match[2]),
        b: parseIntFromHex(match[3] + '' + match[3]),
        format: named ? 'name' : 'hex'
      }
    }
    return false
  }
  W[fn] = Main
})(window, document, Math, 'BaMainPicker')
