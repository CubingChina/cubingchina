<?php

class Editor extends Widget {

	public function run() {
		$clientScript = Yii::app()->clientScript;
		$clientScript->registerScriptFile('/b/kindeditor/kindeditor-min.js');
		$uploadUrl = Yii::app()->createUrl('/board/upload/image');
		$clientScript->registerScript('editor',
<<<EOT
  var editors = window.editors = {};
  $('.editor').on('focus', function(e) {
    var that = $(this);
    var id = that.attr('id');
    if (id === undefined) {
      id = 'editor_' + that.index('.editor');
      that.attr('id', id);
    }
    if (editors[id]) {
      return;
    }
    var editor = KindEditor.create('#' + id, {
      height: 300,
      resizeType: 1,
      filterMode: false,
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
    this.blur();
    window.setTimeout(function() {
      editor.focus();
    }, 10);
    that.off('focus');
  });
EOT
		);
	}
}