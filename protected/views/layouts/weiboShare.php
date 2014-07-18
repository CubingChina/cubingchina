<div class="pull-left weibo-widget weibo-share hidden-xs">
  <wb:share-button appkey="2008685908" addition="number" type="button" ralateUid="5118940638" default_text="<?php echo $this->getWeiboShareDefaultText(); ?>" pic="<?php echo CHtml::encode($this->getWeiboSharePic()); ?>"></wb:share-button>
</div>
<?php
Yii::app()->clientScript->registerScriptFile('http://tjs.sjs.sinajs.cn/open/api/js/wb.js?appkey=2008685908');