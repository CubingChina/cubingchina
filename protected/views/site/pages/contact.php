<?php $this->setPageTitle(array('Contact')); ?>
<?php $this->setTitle('Contact'); ?>
<?php $this->breadcrumbs = array(
  'Contact'
); ?>
<?php $this->renderPartial('aboutSide', $_data_); ?>
<div class="content-wrapper col-md-10 col-sm-9">
  <?php if (substr(Yii::app()->language, 0, 2) == 'zh'): ?>
  <?php echo Yii::app()->controller->translateTWInNeed(
<<<EOT
  <p>
    如果你想采用我们的比赛管理系统并公示一场魔方竞速，或者给我们网站以更多更好的意见和建议，欢迎通过以下邮件联系我们：
    <br><i class="fa fa-envelope"></i> <a href="mailto:admin@cubingchina.com">admin@cubingchina.com</a>。
  </p>
  <p>
    如果你想举办一场WCA官方魔方比赛或举办相关活动，可以联系中国的世界魔方协会代表(WCA Delegates)：
    <br><i class="fa fa-envelope"></i> <a href="mailto:wcadaibiao@gmail.com">wcadaibiao@gmail.com</a>。
  </p>
  <p>
    如果你的所在地位于或者邻近以下城市列表中，你也可以直接与该地的世界魔方协会代表取得联系：
  </p>
  <p>
  </p>
  <div class="table-responsive">
    <table class="table table-bordered table-condensed table-striped table-hover">
      <tbody>
      <tr>
        <td><i class="fa fa-user"></i> 陈丹阳</td>
        <td><i class="fa fa-building"></i> 聊城</td>
        <td><i class="fa fa-envelope"></i> <a href="mailto:danjonopolis@gmail.com">danjonopolis@gmail.com</a></td>
      </tr>
      <tr>
        <td><i class="fa fa-user"></i> 常方圆</td>
        <td><i class="fa fa-building"></i> 北京</td>
        <td><i class="fa fa-envelope"></i> <a href="mailto:yuanyuan.2328@qq.com">yuanyuan.2328@qq.com</a></td>
      </tr>
      <tr>
        <td><i class="fa fa-user"></i> 郑鸣</td>
        <td><i class="fa fa-building"></i> 广州</td>
        <td><i class="fa fa-envelope"></i> <a href="mailto:zm@cubingchina.com">zm@cubingchina.com</a></td>
      </tr>
      <tr>
        <td><i class="fa fa-user"></i> 金晓波</td>
        <td><i class="fa fa-building"></i> 上海</td>
        <td><i class="fa fa-envelope"></i> <a href="mailto:wavelet1988@gmail.com">wavelet1988@gmail.com</a></td>
      </tr>
      <tr>
        <td><i class="fa fa-user"></i> 李冬雷</td>
        <td><i class="fa fa-building"></i> 西安</td>
        <td><i class="fa fa-envelope"></i> <a href="mailto:20003214@sina.com">20003214@sina.com</a></td>
      </tr>
      <tr>
        <td><i class="fa fa-user"></i> 王皓</td>
        <td><i class="fa fa-building"></i> 沈阳</td>
        <td><i class="fa fa-envelope"></i> <a href="mailto:wanghao@yoercn.com">wanghao@yoercn.com</a></td>
      </tr>
      <tr>
        <td><i class="fa fa-user"></i> 路义亮</td>
        <td><i class="fa fa-building"></i> 郑州</td>
        <td><i class="fa fa-envelope"></i> <a href="mailto:mangosteen@vip.sina.com">mangosteen@vip.sina.com</a></td>
      </tr>
      <tr>
        <td><i class="fa fa-user"></i> 陳德泉</td>
        <td><i class="fa fa-building"></i> 香港</td>
        <td><i class="fa fa-envelope"></i> <a href="mailto:kimchikoon@hkrcu.net">kimchikoon@hkrcu.net</a></td>
      </tr>
      </tbody>
    </table>
  </div>
EOT
); ?>
  <?php else: ?>
  <p>
    If you would like to use our website to announce a speedcubing competition and generate a competition webpage, or have any suggestions, feel free to contact the <u>Cubing China Administrator</u> via <br><i class="fa fa-envelope"></i> <a href="mailto:admin@cubingchina.com">admin@cubingchina.com</a>.
  </p>
  <p>
    If you would like to hold a WCA official speedcubing competition or organize a relevant acitivity, please contact the <u>Chinese WCA Delegates</u> via <br><i class="fa fa-envelope"></i> <a href="mailto:wcadaibiao@gmail.com">wcadaibiao@gmail.com</a>.
  </p>
  <p>
    If you are located in or near one of the following cities, you can also get in touch with the local WCA Delegate directly.
  </p>
  <p>
  </p>
  <div class="table-responsive">
    <table class="table table-bordered table-condensed table-striped table-hover">
      <tbody>
      <tr>
        <td><i class="fa fa-user"></i> Danyang Chen</td>
        <td><i class="fa fa-building"></i> Liaocheng</td>
        <td><i class="fa fa-envelope"></i> <a href="mailto:danjonopolis@gmail.com">danjonopolis@gmail.com</a></td>
      </tr>
      <tr>
        <td><i class="fa fa-user"></i> Fangyuan Chang</td>
        <td><i class="fa fa-building"></i> Beijing</td>
        <td><i class="fa fa-envelope"></i> <a href="mailto:yuanyuan.2328@qq.com">yuanyuan.2328@qq.com</a></td>
      </tr>
      <tr>
        <td><i class="fa fa-user"></i> Ming Zheng</td>
        <td><i class="fa fa-building"></i> Guangzhou</td>
        <td><i class="fa fa-envelope"></i> <a href="mailto:zm@cubingchina.com">zm@cubingchina.com</a></td>
      </tr>
      <tr>
        <td><i class="fa fa-user"></i> Xiaobo Jin</td>
        <td><i class="fa fa-building"></i> Shanghai</td>
        <td><i class="fa fa-envelope"></i> <a href="mailto:wavelet1988@gmail.com">wavelet1988@gmail.com</a></td>
      </tr>
      <tr>
        <td><i class="fa fa-user"></i> Donglei Li</td>
        <td><i class="fa fa-building"></i> Xi’an</td>
        <td><i class="fa fa-envelope"></i> <a href="mailto:20003214@sina.com">20003214@sina.com</a></td>
      </tr>
      <tr>
        <td><i class="fa fa-user"></i> Hao Wang</td>
        <td><i class="fa fa-building"></i> Shenyang</td>
        <td><i class="fa fa-envelope"></i> <a href="mailto:wanghao@yoercn.com">wanghao@yoercn.com</a></td>
      </tr>
      <tr>
        <td><i class="fa fa-user"></i> Yiliang Lu</td>
        <td><i class="fa fa-building"></i> Zhengzhou</td>
        <td><i class="fa fa-envelope"></i> <a href="mailto:mangosteen@vip.sina.com">mangosteen@vip.sina.com</a></td>
      </tr>
      <tr>
        <td><i class="fa fa-user"></i> Chan Tak Chuen (Kim)</td>
        <td><i class="fa fa-building"></i> Hong Kong</td>
        <td><i class="fa fa-envelope"></i> <a href="mailto:kimchikoon@hkrcu.net">kimchikoon@hkrcu.net</a></td>
      </tr>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>