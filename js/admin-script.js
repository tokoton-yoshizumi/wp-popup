jQuery(document).ready(function ($) {
  // 表示を切り替えるための関数
  function toggleTriggerFields() {
    // 現在選択されているトリガーの値を取得
    var selectedTrigger = $("#wpp_trigger_type").val();

    // いったん両方の設定項目を隠す
    $(".wpp-timer-seconds-row").hide();
    $(".wpp-scroll-pixels-row").hide();

    // 選択されたトリガーに応じて、対応する設定項目を表示する
    if (selectedTrigger === "timer") {
      $(".wpp-timer-seconds-row").show();
    } else if (selectedTrigger === "scroll") {
      $(".wpp-scroll-pixels-row").show();
    }
  }

  // 1. ページ読み込み時に一度、表示を切り替える
  toggleTriggerFields();

  // 2. 表示トリガーの選択が変更された時にも、表示を切り替える
  $("#wpp_trigger_type").on("change", function () {
    toggleTriggerFields();
  });

  // 不透明度スライダーと数値入力の値を同期させる
  var rangeInput = $(".wpp-opacity-range");
  var numberInput = $(".wpp-opacity-number");

  // スライダーを動かした時
  rangeInput.on("input", function () {
    numberInput.val($(this).val());
  });

  // 数値を入力した時
  numberInput.on("input", function () {
    rangeInput.val($(this).val());
  });
});
