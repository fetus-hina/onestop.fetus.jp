"use strict";(r=>{function d(t,e){for(var[o,a]of Object.entries(e)){const s=r(o);s.length&&s.val(a?t[a]:"")}}r.fn.zipSearch=function(t,e,o,s){const a=this,c=r(t),n=r(o);let l=null;const i=()=>{l&&(clearTimeout(l),l=null),l=setTimeout(()=>{l=null,a.prop("disabled",!String(c.val()).match(/^\d{7}$/))},1e3/60)};return c.keypress(()=>{i()}).change(()=>{i()}),i(),this.each(function(){const t=r(this);t.click(()=>{t.text("問合せ中..."),r.post("/api/postal-code",{code:c.val()}).done(t=>{switch(t.length){case 0:r(".modal-body",n).text("郵便番号の検索結果が空でした"),new bootstrap.Modal(n.get(0)).show();break;case 1:d(t[0],s);break;default:(()=>{const o=r(e),a=r(".list-group",o).empty();t.forEach(t=>{const e=r('<a href="#" class="list-group-item list-group-item-action">');e.text("".concat(t.address1," ").concat(t.address2," ").concat(t.address3)),a.append(e),e.click(()=>(d(t,s),o.modal("hide"),!1))}),new bootstrap.Modal(o.get(0)).show()})()}}).fail(()=>{r(".modal-body",n).text("検索エラーが発生しました"),new bootstrap.Modal(n.get(0)).show()}).always(()=>{t.text("住所入力")})})}),this}})(jQuery);
