"use strict";function _slicedToArray(t,r){return _arrayWithHoles(t)||_iterableToArrayLimit(t,r)||_unsupportedIterableToArray(t,r)||_nonIterableRest()}function _nonIterableRest(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}function _unsupportedIterableToArray(t,r){if(t){if("string"==typeof t)return _arrayLikeToArray(t,r);var e=Object.prototype.toString.call(t).slice(8,-1);return"Object"===e&&t.constructor&&(e=t.constructor.name),"Map"===e||"Set"===e?Array.from(t):"Arguments"===e||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(e)?_arrayLikeToArray(t,r):void 0}}function _arrayLikeToArray(t,r){(null==r||r>t.length)&&(r=t.length);for(var e=0,n=new Array(r);e<r;e++)n[e]=t[e];return n}function _iterableToArrayLimit(t,r){if("undefined"!=typeof Symbol&&Symbol.iterator in Object(t)){var e=[],n=!0,a=!1,o=void 0;try{for(var i,l=t[Symbol.iterator]();!(n=(i=l.next()).done)&&(e.push(i.value),!r||e.length!==r);n=!0);}catch(t){a=!0,o=t}finally{try{n||null==l.return||l.return()}finally{if(a)throw o}}return e}}function _arrayWithHoles(t){if(Array.isArray(t))return t}!function(u){function s(t,r){for(var e=0,n=Object.entries(r);e<n.length;e++){var a=_slicedToArray(n[e],2),o=a[0],i=a[1],l=u(o);l.length&&l.val(i?t[i]:"")}}u.fn.zipSearch=function(t,r,e,a){function n(){c&&(clearTimeout(c),c=null),c=setTimeout(function(){c=null,o.prop("disabled",!String(i.val()).match(/^\d{7}$/))},1e3/60)}var o=this,i=u(t),l=u(e),c=null;return i.keypress(function(){n()}).change(function(){n()}),n(),this.each(function(){var t=u(this);t.click(function(){t.text("問合せ中..."),u.post("/api/postal-code",{code:i.val()}).done(function(t){switch(t.length){case 0:u(".modal-body",l).text("郵便番号の検索結果が空でした"),l.modal();break;case 1:s(t[0],a);break;default:e=u(r),n=u(".list-group",e).empty(),t.forEach(function(t){var r=u('<a href="#" class="list-group-item list-group-item-action">');r.text("".concat(t.address1," ").concat(t.address2," ").concat(t.address3)),n.append(r),r.click(function(){return s(t,a),e.modal("hide"),!1})}),e.modal()}var e,n}).fail(function(){u(".modal-body",l).text("検索エラーが発生しました"),l.modal()}).always(function(){t.text("住所入力")})})}),this}}(jQuery);
