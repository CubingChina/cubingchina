<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>成绩证书</title>
<style>
  @font-face {
    font-family: sszh;
    src: url('../../fonts/sszh.ttf');
  }
  @font-face {
    font-family: msyh;
    src: url('../../fonts/msyh.ttf');
  }
  @font-face {
    font-family: DejaVuSansMono;
    src: url('../../fonts/DejaVuSansMono.ttf');
  }
  body {
    font-family: "Helvetica Neue", Tahoma, Helvetica, Arial, sszh, msyh, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #fff;
    width: 1240px;
    height: 1754px;
    overflow-x: hidden;
  }
  .cert {
    background-image: url(../../images/bg.jpg);
    -webkit-background-size: 100% auto;
    background-size: 100% auto;
    width: 1240px;
    height: 1754px;
    margin: 0 auto;
  }
  .person-info {
    font-size: 32px;
    font-weight: bold;
    padding-top: 690px;
    overflow: hidden;
  }
  .person-info .name {
    float: left;
    margin-left: 188px;
    width: 370px;
    text-align: center;
  }
  .person-info .wcaid {
    float: right;
    margin-right: 194px;
  }
  .results {
    margin-top: 20px;
    margin-left: 100px;
    margin-right: 100px;
    overflow: hidden;
    padding-top: <?php echo $this->paddingTop; ?>px;
  }
  .no-detail {
    width: 50%;
    float: left;
  }
  .one-result {
    font-size: 1.6em;
    padding-top: 200px;
  }
  .one-result-detail {
    font-size: 1.2em;
  }
  table {
    clear: both;
    border-collapse: collapse;
    border-spacing: 0;
    font-size: 20px;
    width: 100%;
  }
  table th, table td {
    text-align: center;
    white-space: nowrap;
    padding-left: 7px;
    padding-right: 7px;
    /*border: 1px solid;*/
  }
  table th {
    line-height: 1;
  }
  table th.detail {
    /*width: 90%;*/
  }
  table th.detail, table td.detail {
    text-align: left;
  }
  table tr.normal-round {
    font-size: 12px;
  }
  table td {
    line-height: 1.2;
  }
  table td.round {
    font-size: 14px;
  }
  table td.best {
    font-size: 1.3em;
  }
  table pre {
    font-family: DejaVuSansMono;
    margin: 0;
    display: inline;
  }
  table .event-image {
    width: 60px;
  }
</style>
</head>
<body>
<div class="cert">
  <div class="person-info">
    <div class="name">
      <?php echo $this->user->country_id <= 4 && $this->user->name_zh ? $this->user->name_zh : $this->user->name; ?>
    </div>
    <div class="wcaid">
      <?php echo $this->user->wcaid; ?>
    </div>
  </div>
  <div class="results">
    <?php foreach ($results as $res): ?>
    <?php echo $this->render('results-table', [
      'results'=>$res,
      'hasDetail'=>$hasDetail,
    ]); ?>
    <?php endforeach; ?>
  </div>
</div>
</body>
</html>