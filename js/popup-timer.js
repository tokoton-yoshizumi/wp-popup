document.addEventListener("DOMContentLoaded", function () {
  const delay = wpp_timer_vars ? wpp_timer_vars.seconds * 1000 : 3000;
  const container = document.getElementById("wpp-container"); // ★ 変更: 対象を #wpp-container に

  setTimeout(function () {
    if (container) {
      container.style.display = "block"; // ★ 変更
    }
  }, delay);

  const closeBtn = document.getElementById("wpp-closePopup");
  if (closeBtn) {
    closeBtn.addEventListener("click", function () {
      if (container) {
        container.style.display = "none"; // ★ 変更
      }
    });
  }
});
