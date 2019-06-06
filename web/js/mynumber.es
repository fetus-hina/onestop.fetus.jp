((window, $) => {
  const getRandomDigit = () => Math.floor(Math.random() * 10);
  const calcCheckDigit = numbers => {
    let s = 0;
    for (let n = 1; n <= 11; ++n) {
      const p = numbers[11 - n];
      const q = (n <= 6) ? (n + 1) : (n - 5);
      s += p * q;
    }
    s %= 11;
    return (s <= 1) ? 0 : (11 - s);
  };
  const makeDummyMyNumber = () => {
    let numbers = '';
    for (let i = 0; i < 11; ++i) {
      numbers = numbers + String(getRandomDigit());
    }
    return numbers + String(calcCheckDigit(numbers));
  };

  $.fn.dummyMyNumber = function () {
    this.each(function () {
      $(this).text(makeDummyMyNumber());
    });
    return this;
  };
})(window, jQuery);
