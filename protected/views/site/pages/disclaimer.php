<?php $config = Config::getConfig('disclaimer'); ?>
<?php $this->setPageTitle(array($config->getAttributeValue('title'))); ?>
<?php $this->setTitle($config->getAttributeValue('title')); ?>
<?php $this->breadcrumbs = array(
  $config->getAttributeValue('title'),
); ?>
<?php $this->renderPartial('aboutSide', $_data_); ?>
<div class="content-wrapper col-md-10 col-sm-9">
  <?php echo $config->getAttributeValue('content'); ?>
</div>