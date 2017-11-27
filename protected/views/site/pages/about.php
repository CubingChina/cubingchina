<?php $this->setPageTitle(array('About')); ?>
<?php $this->setTitle('About'); ?>
<?php $this->breadcrumbs = array(
    'About'
); ?>
<?php $this->setWeiboShareDefaultText('中国魔方速拧发展简介（Speedcubing in China）', false); ?>
<?php $this->setWeiboSharePic(Yii::app()->request->getBaseUrl(true) . '/f/images/weibo/about.png'); ?>
<?php $this->renderPartial('aboutSide', $_data_); ?>
<div class="content-wrapper col-md-10 col-sm-9">
  <?php if (substr(Yii::app()->language, 0, 2) == 'zh'): ?>
  <?php echo Yii::app()->controller->translateTWInNeed(
<<<EOT
  <p>魔方吧•中国魔方俱乐部（MF8）成立于2004年，是中国最早的魔方信息分享网站，现已成为国内最大最专业的魔方论坛。在过去十年间，众多魔方爱好者和专业玩家汇聚在魔方吧，共同分享魔方带给我们的乐趣，并向大众普及和传播魔方的巨大魅力。</p>
  <p>粗饼•中国魔方赛事网（Cubing China）由魔方吧创立人及站长霍先生、世界魔方协会成绩组专员董百强、世界魔方协会官方代表郑鸣于2014年共同创办，其前身为魔方吧的在线比赛报名系统。我们共同致力于为魔方玩家建立更好的魔方竞速比赛平台，进一步发展魔方竞速运动，推广国内的专业魔方赛事。</p>
  <hr>
  <ul class="timeline">
    <li>
      <div class="timeline-badge">2014</div>
    </li>
    <li class="timeline-inverted">
      <div class="timeline-badge">十月</div>
      <div class="timeline-panel">
        <div class="timeline-heading">
          <h4 class="timeline-title">世界魔方协会的中国选手达到五千人</h4>
        </div>
      </div>
    </li>
    <li>
      <div class="timeline-badge">六月</div>
      <div class="timeline-panel">
        <div class="timeline-heading">
          <h4 class="timeline-title">粗饼•中国魔方赛事网(Cubing China)正式上线</h4>
        </div>
      </div>
    </li>
    <li>
      <div class="timeline-badge warning">2013</div>
    </li>
    <li class="timeline-inverted">
      <div class="timeline-badge warning">七月</div>
      <div class="timeline-panel">
        <div class="timeline-heading">
          <h4 class="timeline-title">中国选手首次在世锦赛上获得SQ-1项目冠军</h4>
        </div>
      </div>
    </li>
    <li>
      <div class="timeline-badge danger">2012</div>
    </li>
    <li>
      <div class="timeline-badge danger">十月</div>
      <div class="timeline-panel">
        <div class="timeline-heading">
          <h4 class="timeline-title">在香港举办了大规模的亚洲魔方锦标赛</h4>
        </div>
      </div>
    </li>
    <li>
      <div class="timeline-badge info">2011</div>
    </li>
    <li class="timeline-inverted">
      <div class="timeline-badge info">十一月</div>
      <div class="timeline-panel">
        <div class="timeline-heading">
          <h4 class="timeline-title">中国举行的世界魔方协会比赛达到百场</h4>
        </div>
      </div>
    </li>
    <li>
      <div class="timeline-badge info">十月</div>
      <div class="timeline-panel">
        <div class="timeline-heading">
          <h4 class="timeline-title">中国选手首次参加世界魔方锦标赛</h4>
        </div>
      </div>
    </li>
    <li>
      <div class="timeline-badge success">2009</div>
    </li>
    <li class="timeline-inverted">
      <div class="timeline-badge success">五月</div>
      <div class="timeline-panel">
        <div class="timeline-heading">
          <h4 class="timeline-title">中国选手首次在官方比赛中打破世界纪录</h4>
        </div>
      </div>
    </li>
    <li>
      <div class="timeline-badge">2008</div>
    </li>
    <li>
      <div class="timeline-badge">七月</div>
      <div class="timeline-panel">
        <div class="timeline-heading">
          <h4 class="timeline-title">中国首位世界魔方协会官方代表上任</h4>
        </div>
      </div>
    </li>
    <li>
      <div class="timeline-badge warning">2007</div>
    </li>
    <li class="timeline-inverted">
      <div class="timeline-badge warning">十月</div>
      <div class="timeline-panel">
        <div class="timeline-heading">
          <h4 class="timeline-title">国内首次举办世界魔方协会认证魔方赛</h4>
        </div>
      </div>
    </li>
    <li>
      <div class="timeline-badge danger">2006</div>
    </li>
    <li>
      <div class="timeline-badge danger">八月</div>
      <div class="timeline-panel">
        <div class="timeline-heading">
          <h4 class="timeline-title">国内魔方爱好者首次自发组织魔方比赛</h4>
        </div>
      </div>
    </li>
    <li>
      <div class="timeline-badge info">2004</div>
    </li>
    <li class="timeline-inverted">
      <div class="timeline-badge info">元月</div>
      <div class="timeline-panel">
        <div class="timeline-heading">
          <h4 class="timeline-title">中国选手首次参加世界魔方协会比赛</h4>
        </div>
      </div>
    </li>
    <li>
      <div class="timeline-badge info">元月</div>
      <div class="timeline-panel">
        <div class="timeline-heading">
          <h4 class="timeline-title">国内首个魔方专业网站魔方吧创立</h4>
        </div>
      </div>
    </li>
    <li>
      <div class="timeline-badge success">1991</div>
    </li>
    <li class="timeline-inverted">
      <div class="timeline-badge success">十一月</div>
      <div class="timeline-panel">
        <div class="timeline-heading">
          <h4 class="timeline-title">中国国内首次魔方比赛在广州进行</h4>
        </div>
      </div>
    </li>
  </ul>
EOT
); ?>
  <?php else: ?>
  <p>Founded in 2004, MF8 Chinese Cube Club (bbs.mf8-china.com) is the earliest Chinese website specializing in Rubik’s Cubes and other puzzles. Now it has become the largest and most professional cubing forum in the Chinese cubing community. Over the past ten years, more and more puzzle fans and cubers have gathered here, sharing the fun of puzzles and popularizing 3x3x3 Cube.</p>
  <p>Cubing China (cubingchina.com) is established by Mr. Fok (the founder of MF8), Baiqiang Dong (WCA Results Team member), and Ming Zheng (WCA Delegate for China). The predecessor of Cubing China was the MF8 cubing forum and its online competition registration system. Now we are creating a dedicated online platform for speedcubing competitions, to develop cubing activities and promote cube competitions in China.</p>
  <hr>
  <ul class="timeline">
    <li>
      <div class="timeline-badge">2014</div>
    </li>
    <li class="timeline-inverted">
      <div class="timeline-badge">Oct</div>
      <div class="timeline-panel">
        <div class="timeline-heading">
          <h4 class="timeline-title">Chinese WCA competitors hit five thousand.</h4>
        </div>
      </div>
    </li>
    <li>
      <div class="timeline-badge">Jun</div>
      <div class="timeline-panel">
        <div class="timeline-heading">
          <h4 class="timeline-title">Cubing China, the official Chinese speedcubing website, goes online.</h4>
        </div>
      </div>
    </li>
    <li>
      <div class="timeline-badge warning">2013</div>
    </li>
    <li class="timeline-inverted">
      <div class="timeline-badge warning">Jul</div>
      <div class="timeline-panel">
        <div class="timeline-heading">
          <h4 class="timeline-title">Chinese competitor took first place in SQ-1 at the WCA World Championship.</h4>
        </div>
      </div>
    </li>
    <li>
      <div class="timeline-badge danger">2012</div>
    </li>
    <li>
      <div class="timeline-badge danger">Oct</div>
      <div class="timeline-panel">
        <div class="timeline-heading">
          <h4 class="timeline-title">The large-scale Asian Championship took place in Hong Kong.</h4>
        </div>
      </div>
    </li>
    <li>
      <div class="timeline-badge info">2011</div>
    </li>
    <li class="timeline-inverted">
      <div class="timeline-badge info">Nov</div>
      <div class="timeline-panel">
        <div class="timeline-heading">
          <h4 class="timeline-title">Over one hundred WCA competitions had been held in China.</h4>
        </div>
      </div>
    </li>
    <li>
      <div class="timeline-badge info">Oct</div>
      <div class="timeline-panel">
        <div class="timeline-heading">
          <h4 class="timeline-title">Chinese cubers attended the WCA World Championship for the first time.</h4>
        </div>
      </div>
    </li>
    <li>
      <div class="timeline-badge success">2009</div>
    </li>
    <li class="timeline-inverted">
      <div class="timeline-badge success">May</div>
      <div class="timeline-panel">
        <div class="timeline-heading">
          <h4 class="timeline-title">The first WCA world record set by a Chinese competitor.</h4>
        </div>
      </div>
    </li>
    <li>
      <div class="timeline-badge">2008</div>
    </li>
    <li>
      <div class="timeline-badge">Jul</div>
      <div class="timeline-panel">
        <div class="timeline-heading">
          <h4 class="timeline-title">The first official WCA delegate for China was selected.</h4>
        </div>
      </div>
    </li>
    <li>
      <div class="timeline-badge warning">2007</div>
    </li>
    <li class="timeline-inverted">
      <div class="timeline-badge warning">Oct</div>
      <div class="timeline-panel">
        <div class="timeline-heading">
          <h4 class="timeline-title">The first WCA competition in China took place in Guangzhou.</h4>
        </div>
      </div>
    </li>
    <li>
      <div class="timeline-badge danger">2006</div>
    </li>
    <li>
      <div class="timeline-badge danger">Aug</div>
      <div class="timeline-panel">
        <div class="timeline-heading">
          <h4 class="timeline-title">Chinese cubers organized the first modern speedcubing competition.</h4>
        </div>
      </div>
    </li>
    <li>
      <div class="timeline-badge info">2004</div>
    </li>
    <li class="timeline-inverted">
      <div class="timeline-badge info">Jan</div>
      <div class="timeline-panel">
        <div class="timeline-heading">
          <h4 class="timeline-title">The first Chinese competitor attended a WCA competition abroad.</h4>
        </div>
      </div>
    </li>
    <li>
      <div class="timeline-badge info">Jan</div>
      <div class="timeline-panel">
        <div class="timeline-heading">
          <h4 class="timeline-title">MF8, the first Chinese puzzles and cubes website, was founded.</h4>
        </div>
      </div>
    </li>
    <li>
      <div class="timeline-badge success">1991</div>
    </li>
    <li class="timeline-inverted">
      <div class="timeline-badge success">Nov</div>
      <div class="timeline-panel">
        <div class="timeline-heading">
          <h4 class="timeline-title">China’s first Rubik’s Cube competition was held in Guangzhou.</h4>
        </div>
      </div>
    </li>
  </ul>
  <?php endif; ?>
</div>
