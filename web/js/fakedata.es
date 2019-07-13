($ => {
  $.fn.fakeData = function () {
    this.click(function () {
      const $this = $(this);
      const json = JSON.parse($($this.data('data')).text());
      Object.entries(json).forEach(kvPair => {
        const [selector, value] = kvPair;
        const $input = $(selector);
        if (value === true || value === false) {
          $input.prop('checked', value);
        } else {
          $input.val(value);
        }
      });
    });
    return this;
  };
})(jQuery);
