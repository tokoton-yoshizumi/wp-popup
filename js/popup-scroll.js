document.addEventListener("DOMContentLoaded", function () {
  const container = document.getElementById("wpp-container"); // ★ 変更: 対象を #wpp-container に
  const closeBtn = document.getElementById("wpp-closePopup");
  const threshold = wpp_scroll_vars ? wpp_scroll_vars.pixels : 200;
  let hasShownPopup = false;

  window.addEventListener("scroll", function () {
    if (!hasShownPopup && window.scrollY >= threshold) {
      if (container) {
        container.style.display = "block"; // ★ 変更
        hasShownPopup = true;
      }
    }
  });

  if (closeBtn) {
    closeBtn.addEventListener("click", function () {
      if (container) {
        container.style.display = "none"; // ★ 変更
      }
    });
  }
});
