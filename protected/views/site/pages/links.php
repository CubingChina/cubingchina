<?php $this->setPageTitle(array('Links')); ?>
<?php $this->setTitle('Links'); ?>
<?php $this->breadcrumbs = array(
	'Links'
); ?>
<?php $this->renderPartial('aboutSide', $_data_); ?>
<div class="content-wrapper col-md-10 col-sm-9">
	<?php
	$links = include APP_PATH . '/protected/config/links.php';
	foreach ($links as $link):
		if ($link === '-') {
			echo CHtml::tag('hr');
			continue;
		}
	?>
	<h3><?php echo CHtml::link($this->translateTWInNeed($link[$this->getAttributeName('name')]), $link['url']); ?></h3>
	<p>
		<?php if (isset($link['logo'])): ?>
		<?php echo CHtml::image($link['logo'], $this->translateTWInNeed($link[$this->getAttributeName('name')])); ?><br>
		<?php endif; ?>
		<?php echo $this->translateTWInNeed($link[$this->getAttributeName('description')]); ?>
	</p>
	<?php endforeach; ?>
</div>