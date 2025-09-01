jQuery(document).ready(function ($) {
  // 汎用的なメディアアップローダーの処理
  // .wpp-upload-button クラスを持つボタンがクリックされた時の処理
  $(document).on("click", ".wpp-upload-button", function (e) {
    e.preventDefault();
    var button = $(this);
    var fieldContainer = button.closest(".wpp-media-uploader-field");
    var inputField = fieldContainer.find(".wpp-image-url-input");
    var previewContainer = fieldContainer.find(".wpp-image-preview");

    var image = wp
      .media({
        title: "画像を選択",
        button: {
          text: "この画像を選択",
        },
        multiple: false,
      })
      .on("select", function () {
        var attachment = image.state().get("selection").first().toJSON();
        inputField.val(attachment.url); // 対応するinputにURLを設定
        previewContainer.html(
          '<img src="' +
            attachment.url +
            '" style="max-width:200px;height:auto;margin-top:10px;">'
        ); // 対応するプレビューを表示
      })
      .open();
  });

  // .wpp-remove-button クラスを持つボタンがクリックされた時の処理
  $(document).on("click", ".wpp-remove-button", function (e) {
    e.preventDefault();
    var button = $(this);
    var fieldContainer = button.closest(".wpp-media-uploader-field");
    var inputField = fieldContainer.find(".wpp-image-url-input");
    var previewContainer = fieldContainer.find(".wpp-image-preview");

    inputField.val(""); // 対応するinputを空に
    previewContainer.html(""); // 対応するプレビューを削除
  });
});
