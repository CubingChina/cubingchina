<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>参赛证书</title>
<style>
  @font-face {
    font-family: msyh;
    src: url('../../fonts/msyh.ttf');
  }
  body {
    font-family: "Helvetica Neue", Tahoma, Helvetica, Arial, msyh, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #fff;
    width: 1240px;
    height: 1754px;
    overflow-x: hidden;
  }
  .cert {
    background-image: url(../../images/bg-participations.jpg);
    -webkit-background-size: 100% auto;
    background-size: 100% auto;
    width: 1240px;
    height: 1754px;
    margin: 0 auto;
  }
  .person-info {
    font-size: 48px;
    /*font-weight: bold;*/
    padding-top: 1042px;
    overflow: hidden;
    text-align: center;
  }
</style>
</head>
<body>
<div class="cert">
  <div class="person-info">
    <?php echo $this->user->country_id <= 4 ? $this->user->getCompetitionName() : $this->user->name; ?>
  </div>
</div>
</body>
</html>