($ => {
  $.fn.fakeData = function (modalId) {
    this.click(function () {
      const $button = $(this);
      $button.prop('disabled', true);

      $.ajax('/api/fake-data')
        .done(json => {
          Object.entries(json).forEach(kvPair => {
            const [selector, value] = kvPair;
            const $input = $(`#${selector}`);
            if (value === true || value === false) {
              $input.prop('checked', value);
            } else {
              $input.val(value);
            }
          });
        })
        .always(() => {
          $button.prop('disabled', false);

          const modal = bootstrap.Modal.getInstance(
            document.getElementById(modalId)
          );
          modal.hide();
        });
    });
    return this;
  };
})(jQuery);
