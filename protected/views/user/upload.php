<script>
  var url = '<?php echo $url; ?>';
  var errorMsg = '<?php echo $errorMsg; ?>';
  var errorCode = '<?php echo $errorCode; ?>';
  parent.$('.user-avatar-container').removeClass('loading');
  if (errorCode == 0) {
    parent.$('.user-avatar').attr('src', url).parent().attr('href', url);
  } else {
    alert(errorMsg);
  }
</script>