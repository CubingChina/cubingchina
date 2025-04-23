<?php if (!Yii::app()->user->isGuest
  && ($this->user->isOrganizer() && isset($competition->organizers[$this->user->id])
  || $this->user->isAdministrator() || Yii::app()->user->checkPermission('caqa_member'))): ?>
<div class="col-lg-12">
  <?php echo CHtml::link(Yii::t('common', 'Edit'), ['/board/competition/edit', 'id'=>$competition->id], [
    'class'=>'btn btn-xs btn-theme',
  ]); ?>
</div>
<?php endif; ?>