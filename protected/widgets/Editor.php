<?php

class Editor extends Widget {

	public function run() {
		$clientScript = Yii::app()->clientScript;
		$clientScript->registerScriptFile('/b/kindeditor/kindeditor-min.js');
		$uploadUrl = Yii::app()->createUrl('/board/upload/image');
		$clientScript->registerScript('editor',
<<<EOT
  var editors = window.editors = {};
  $('.editor').each(function(i) {
    var that = $(this);
    var id = that.attr('id');
    if (id === undefined) {
      id = 'editor_' + i;
      that.attr('id', id);
    }
    var editor = KindEditor.create('#' + id, {
      height: 300,
      resizeType: 1,
      uploadJson: '{$uploadUrl}',
      allowFileManager: false,
      items: [
        'source', '|', 'cut', 'copy', 'paste', 'plainpaste', 'wordpaste', '|',
        'justifyleft', 'justifycenter', 'justifyright', 'justifyfull',
        'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent',
        'subscript', 'superscript', 'clearhtml', 'selectall', '|', 'fullscreen', '/',
        'undo', 'redo', '|', 'formatblock', 'fontname', 'fontsize', '|',
        'forecolor', 'hilitecolor', 'bold', 'italic', 'underline', 'strikethrough', 'removeformat', '|',
        'image', 'multiimage',  'table', 'hr', 'link', 'unlink', 'baidumap'
      ]
    });
    editors[id] = editor;
  });
EOT
		);
	}
}