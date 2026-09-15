<?php
$newSiteUrl = ArchiveMode::newSiteUrl();
$newSiteLink = ArchiveMode::newSiteLink($newSiteUrl);
?>
<style>
.archive-readonly-banner {
  background: #c0392b;
  color: #fff;
  text-align: center;
  padding: 10px 16px;
  font-size: 14px;
  line-height: 1.5;
  position: relative;
  z-index: 1080;
}
.archive-readonly-banner a {
  color: #ffe082;
  font-weight: 700;
  text-decoration: underline;
}
.archive-readonly-banner a:hover,
.archive-readonly-banner a:focus {
  color: #fff;
}
.nav-item.archive-new-site > a {
  color: #fff !important;
  background: #e67e22;
}
.nav-item.archive-new-site > a:hover,
.nav-item.archive-new-site > a:focus {
  background: #d35400 !important;
}
</style>
<div class="archive-readonly-banner">
  <?php echo Yii::t('common', 'This legacy version of Cubing China is now read-only. Please visit the new site at {url}.', [
    '{url}'=>$newSiteLink,
  ]); ?>
</div>
<div class="modal fade" id="archive-readonly-modal" tabindex="-1" role="dialog" aria-labelledby="archive-readonly-title">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="archive-readonly-title"><?php echo Yii::t('common', 'Read-only notice'); ?></h4>
      </div>
      <div class="modal-body">
        <p><?php echo Yii::t('common', 'This legacy version of Cubing China is now read-only. Please visit the new site at {url}.', [
          '{url}'=>$newSiteLink,
        ]); ?></p>
      </div>
      <div class="modal-footer">
        <?php echo ArchiveMode::newSiteLink(Yii::t('common', 'Go to the new Cubing China'), [
          'class'=>'btn btn-primary',
        ]); ?>
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo Yii::t('common', 'Continue browsing the old site'); ?></button>
      </div>
    </div>
  </div>
</div>
<?php
Yii::app()->clientScript->registerScript('archiveNotice',
<<<'EOT'
  if (!window.sessionStorage || !sessionStorage.getItem('archiveNoticeShown')) {
    $('#archive-readonly-modal').modal('show');
    if (window.sessionStorage) {
      sessionStorage.setItem('archiveNoticeShown', '1');
    }
  }
  $(document).on('submit', 'form', function (e) {
    if (this.id === 'login-form') {
      return;
    }
    e.preventDefault();
    $('#archive-readonly-modal').modal('show');
    return false;
  });
EOT
);
