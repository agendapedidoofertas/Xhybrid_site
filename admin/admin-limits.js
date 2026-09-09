(function () {
  function update(el) {
    var id = el.id;
    if (!id) return;
    var counter = document.querySelector('.admin-charlimit[data-for="' + id + '"]');
    if (!counter) return;
    var max = el.getAttribute("maxlength");
    var len = (el.value || "").length;
    counter.textContent = len + "/" + max;
    counter.classList.toggle("is-near", max && len / max >= 0.85);
  }

  document.querySelectorAll("input[maxlength], textarea[maxlength]").forEach(function (el) {
    update(el);
    el.addEventListener("input", function () {
      update(el);
    });
  });
})();
