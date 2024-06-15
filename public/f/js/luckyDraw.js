(function ($, win, doc) {
  class LuckyDraw {
    KEY_PREFIX = 'luckyDraw_v2_';
    ALL_USERS_KEY = 'luckyDraw_v2_all';
    DRAWN_USERS_KEY = 'luckyDraw_v2_drawn';
    allUsers = [];
    users = [];
    drawnUsers = [];

    constructor(allUsers) {
      this.allUsers = allUsers || [];

      if (this.allUsers.length === 0) {
        this.allUsers = store.get(this.ALL_USERS_KEY) || [];
      } else {
        store.set(this.ALL_USERS_KEY, this.allUsers);
      }
      const storedDrawnUsers = store.get(this.DRAWN_USERS_KEY) || [];
      for (const name of this.allUsers) {
        if (storedDrawnUsers.includes(name)) {
          this.drawnUsers.push(name);
        } else {
          this.users.push(name);
        }
      }
    }

    next() {
      const index = Math.random() * this.users.length | 0;
      const user = this.users[index];
      if (!user) {
        return false;
      }
      this.users.splice(index, 1);
      this.drawnUsers.push(user);
      store.set(this.DRAWN_USERS_KEY, this.drawnUsers);
      return {
        index,
        user
      };
    }

    reset() {
      this.users = this.allUsers.slice(0);
      this.drawnUsers = [];
      store.remove(this.DRAWN_USERS_KEY);
      store.set(this.ALL_USERS_KEY, this.allUsers);
    }

    update(users) {
      this.allUsers = users;
      this.reset();
    }

    getRemained() {
      return this.users;
    }

    getDrawn() {
      return this.drawnUsers;
    }

    getAll() {
      return this.allUsers;
    }
  }
  $(doc).on('click', '#settings', function () {
    $('#luckyDrawNames').val(luckyDraw.getAll().map(user => {
      return `${user.index ? user.index + ': ' : ''}${user.name}`
    }).join('\n'));
  }).on('click', '#save', function () {
    var users = $('#luckyDrawNames').val().split('\n').filter(function (name) {
      return $.trim(name) != '';
    }).map(function (user) {
      const [index, name] = user.split(':');
      if (!name) {
        return {
          name: user.trim(),
        }
      }
      return {
        name: name.trim(),
        index: index.trim(),
        number: `No.${index.trim()}`,
      }
    });
    const title = $('#luckyDrawTitle').val();
    const logo = $('#luckyDrawLogo').val();
    luckyDraw.update(users);
    setTitle(title);
    setLogo(logo);
    $('#drawModal').modal('hide');
    restart();
  }).on('change', '#luckyDrawCompetition', function () {
    var name = $(this).find('option:selected').text();
    $('#luckyDrawTitle').val(name);
    var id = $(this).val();
    if (id) {
      $.ajax({
        type: 'get',
        url: $(this).data('url'),
        data: {
          id: id,
        },
        dataType: 'json',
        success: function (json) {
          $('#luckyDrawNames').val(json.data.map((name, index) => `${index + 1}: ${name}`).join('\n'));
        }
      });
    }
  });
  const NUMBER_MATRIX = [
    [
      // 0
      [0, 0],
      [1, 0],
      [2, 0],
      [0, 1],
      [2, 1],
      [0, 2],
      [2, 2],
      [0, 3],
      [2, 3],
      [0, 4],
      [1, 4],
      [2, 4]
    ],
    [
      // 1
      [1, 0],
      [0, 1],
      [1, 1],
      [1, 2],
      [1, 3],
      [0, 4],
      [1, 4],
      [2, 4]
    ],
    [
      // 2
      [0, 0],
      [1, 0],
      [2, 0],
      [2, 1],
      [0, 2],
      [1, 2],
      [2, 2],
      [0, 3],
      [0, 4],
      [1, 4],
      [2, 4]
    ],
    [
      // 3
      [0, 0],
      [1, 0],
      [2, 0],
      [2, 1],
      [0, 2],
      [1, 2],
      [2, 2],
      [2, 3],
      [0, 4],
      [1, 4],
      [2, 4]
    ],
    [
      // 4
      [0, 0],
      [2, 0],
      [0, 1],
      [2, 1],
      [0, 2],
      [1, 2],
      [2, 2],
      [2, 3],
      [2, 4]
    ],
    [
      // 5
      [0, 0],
      [1, 0],
      [2, 0],
      [0, 1],
      [0, 2],
      [1, 2],
      [2, 2],
      [2, 3],
      [0, 4],
      [1, 4],
      [2, 4]
    ],
    [
      // 6
      [0, 0],
      [1, 0],
      [2, 0],
      [0, 1],
      [0, 2],
      [1, 2],
      [2, 2],
      [0, 3],
      [2, 3],
      [0, 4],
      [1, 4],
      [2, 4]
    ],
    [
      // 7
      [0, 0],
      [1, 0],
      [2, 0],
      [2, 1],
      [2, 2],
      [2, 3],
      [2, 4]
    ],
    [
      // 8
      [0, 0],
      [1, 0],
      [2, 0],
      [0, 1],
      [2, 1],
      [0, 2],
      [1, 2],
      [2, 2],
      [0, 3],
      [2, 3],
      [0, 4],
      [1, 4],
      [2, 4]
    ],
    [
      // 9
      [0, 0],
      [1, 0],
      [2, 0],
      [0, 1],
      [2, 1],
      [0, 2],
      [1, 2],
      [2, 2],
      [2, 3],
      [0, 4],
      [1, 4],
      [2, 4]
    ]
  ];

  const luckyDraw = new LuckyDraw();
  const container = $('#luckydraw-container')
  let status = 0;
  const ROTATE_TIME = 3000;
  const ROTATE_LOOP = 1000;

  let TOTAL_CARDS,
    btns = {
      enter: document.querySelector('#enter'),
      lotteryBar: document.querySelector('#lotteryBar'),
      lottery: document.querySelector('#lottery')
    },
    ROW_COUNT = 15,
    COLUMN_COUNT = 21,
    HIGHLIGHT_CELL = [],
    // 当前的比例
    Resolution = 1;

  let camera,
    scene,
    renderer,
    controls,
    threeDCards = [],
    targets = {
      table: [],
      sphere: []
    };

  let rotateObj;

  let selectedCardIndex = [],
    isLotting = false,
    currentLuckys = [];
  setTitle(getTitle());
  setLogo(getLogo());
  restart();
  if (luckyDraw.getAll().length === 0) {
    $('#drawModal').modal('show');
  }
  initAll();

  /**
   * 初始化所有DOM
   */
  function initAll() {
    const sqrt = Math.floor(Math.sqrt(luckyDraw.allUsers.length))
    ROW_COUNT = sqrt > 15 ? 15 : sqrt;
    COLUMN_COUNT = sqrt > 21 ? 21 : sqrt;
    HIGHLIGHT_CELL = createHighlight();
    TOTAL_CARDS = ROW_COUNT * COLUMN_COUNT;
    initCards();
    animate();
    shineCard();
  }

  function initCards() {
    let allUsers = luckyDraw.allUsers.slice(),
      length = allUsers.length;

    let isBold = false,
      showTable = luckyDraw.users.length === luckyDraw.allUsers.length,
      index = 0,
      position = {
        x: (140 * COLUMN_COUNT - 20) / 2,
        y: (180 * ROW_COUNT - 20) / 2
      };

    camera = new THREE.PerspectiveCamera(
      40,
      window.innerWidth / window.innerHeight,
      1,
      10000
    );
    camera.position.z = 3000;

    scene = new THREE.Scene();

    for (let i = 0; i < ROW_COUNT; i++) {
      for (let j = 0; j < COLUMN_COUNT; j++) {
        isBold = HIGHLIGHT_CELL.includes(j + '-' + i);
        var element = createCard(
          allUsers[index % length],
          isBold,
          index,
          showTable
        );

        var object = new THREE.CSS3DObject(element);
        object.position.x = Math.random() * 4000 - 2000;
        object.position.y = Math.random() * 4000 - 2000;
        object.position.z = Math.random() * 4000 - 2000;
        scene.add(object);
        threeDCards.push(object);
        //

        var object = new THREE.Object3D();
        object.position.x = j * 140 - position.x;
        object.position.y = -(i * 180) + position.y;
        targets.table.push(object);
        index++;
      }
    }

    // sphere

    var vector = new THREE.Vector3();

    for (var i = 0, l = threeDCards.length; i < l; i++) {
      var phi = Math.acos(-1 + (2 * i) / l);
      var theta = Math.sqrt(l * Math.PI) * phi;
      var object = new THREE.Object3D();
      object.position.setFromSphericalCoords(800 * Resolution, phi, theta);
      vector.copy(object.position).multiplyScalar(2);
      object.lookAt(vector);
      targets.sphere.push(object);
    }

    renderer = new THREE.CSS3DRenderer();
    renderer.setSize(window.innerWidth, window.innerHeight);
    container.append(renderer.domElement);

    controls = new THREE.TrackballControls(camera, renderer.domElement);
    controls.rotateSpeed = 0.5;
    controls.minDistance = 500;
    controls.maxDistance = 6000;
    controls.addEventListener('change', render);

    bindEvent();

    if (showTable) {
      switchScreen('enter');
    } else {
      switchScreen('lottery');
    }
  }

  function setLotteryStatus(status = false) {
    isLotting = status;
  }

  /**
   * 事件绑定
   */
  function bindEvent() {
    document.querySelector('#menu').addEventListener('click', function (e) {
      e.stopPropagation();
      // 如果正在抽奖，则禁止一切操作
      if (isLotting) {
        if (e.target.id === 'lottery') {
          rotateObj.stop();
        }
        return false;
      }

      let target = e.target.id;
      switch (target) {
        // 显示数字墙
        case 'welcome':
          switchScreen('enter');
          rotate = false;
          break;
        // 进入抽奖
        case 'enter':
          removeHighlight();
          // rotate = !rotate;
          rotate = true;
          switchScreen('lottery');
          break;
        // 重置
        case 'reset':
          let doREset = window.confirm(
            '是否确认重置数据，重置后，当前已抽的奖项全部清空？'
          );
          if (!doREset) {
            return;
          }
          addHighlight();
          resetCard();
          // 重置所有数据
          currentLuckys = [];
          luckyDraw.reset();

          switchScreen('enter');
          break;
        // 抽奖
        case 'lottery':
          setLotteryStatus(true);
          // 每次抽奖前先保存上一次的抽奖数据
          saveData();
          //更新剩余抽奖数目的数据显示
          resetCard().then(res => {
            // 抽奖
            lottery();
          });
          break;
        // 重新抽奖
        case 'reLottery':
          if (currentLuckys.length === 0) {
            return;
          }
          setErrorData(currentLuckys);
          setLotteryStatus(true);
          // 重新抽奖则直接进行抽取，不对上一次的抽奖数据进行保存
          // 抽奖
          resetCard().then(res => {
            // 抽奖
            lottery();
          });
          break;
      }
    });

    window.addEventListener('resize', onWindowResize, false);
  }

  function switchScreen(type) {
    switch (type) {
      case 'enter':
        btns.enter.classList.remove('none');
        btns.lotteryBar.classList.add('none');
        transform(targets.table, 2000);
        break;
      default:
        btns.enter.classList.add('none');
        btns.lotteryBar.classList.remove('none');
        transform(targets.sphere, 2000);
        break;
    }
  }

  /**
   * 创建元素
   */
  function createElement(css, text) {
    let dom = document.createElement('div');
    dom.className = css || '';
    dom.innerHTML = text || '';
    return dom;
  }

  /**
   * 创建名牌
   */
  function createCard(user, isBold, id, showTable) {
    var element = createElement();
    element.id = 'card-' + id;

    if (isBold) {
      element.className = 'element lightitem';
      if (showTable) {
        element.classList.add('highlight');
      }
    } else {
      element.className = 'element';
      element.style.backgroundColor =
        'rgba(0,127,127,' + (Math.random() * 0.7 + 0.25) + ')';
    }
    element.appendChild(createElement('name', user.name));

    element.appendChild(createElement('details', user.number));
    return element;
  }

  function removeHighlight() {
    document.querySelectorAll('.highlight').forEach(node => {
      node.classList.remove('highlight');
    });
  }

  function addHighlight() {
    document.querySelectorAll('.lightitem').forEach(node => {
      node.classList.add('highlight');
    });
  }

  /**
   * 渲染地球等
   */
  function transform(targets, duration) {
    // TWEEN.removeAll();
    for (var i = 0; i < threeDCards.length; i++) {
      var object = threeDCards[i];
      var target = targets[i];

      new TWEEN.Tween(object.position)
        .to(
          {
            x: target.position.x,
            y: target.position.y,
            z: target.position.z
          },
          Math.random() * duration + duration
        )
        .easing(TWEEN.Easing.Exponential.InOut)
        .start();

      new TWEEN.Tween(object.rotation)
        .to(
          {
            x: target.rotation.x,
            y: target.rotation.y,
            z: target.rotation.z
          },
          Math.random() * duration + duration
        )
        .easing(TWEEN.Easing.Exponential.InOut)
        .start();
    }

    new TWEEN.Tween(this)
      .to({}, duration * 2)
      .onUpdate(render)
      .start();
  }

  function rotateBall() {
    return new Promise((resolve, reject) => {
      scene.rotation.x = 0;
      scene.rotation.y = 0;
      scene.rotation.z = 0;
      rotateObj = new TWEEN.Tween(scene.rotation);
      rotateObj
        .to(
          {
            x: Math.PI * 2 * ROTATE_LOOP,
            y: Math.PI * 5 * ROTATE_LOOP,
            z: Math.PI * 7 * ROTATE_LOOP,
          },
          ROTATE_TIME * ROTATE_LOOP
        )
        .onUpdate(render)
        // .easing(TWEEN.Easing.Linear)
        .start()
        .onStop(() => {
          new TWEEN.Tween(scene.rotation).to({
            x: 0,
            y: 0,
            z: 0
          }, 1000)
            .onUpdate(render)
            .onComplete(() => {
              resolve();
            })
            .start();
        })
        .onComplete(() => {
          scene.rotation.x = 0;
          scene.rotation.y = 0;
          scene.rotation.z = 0;
          rotateObj.start();
        });
    });
  }

  function onWindowResize() {
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
    render();
  }

  function animate() {
    // 让场景通过x轴或者y轴旋转
    // rotate && (scene.rotation.y += 0.088);

    requestAnimationFrame(animate);
    TWEEN.update();
    controls.update();

    // 渲染循环
    // render();
  }

  function render() {
    renderer.render(scene, camera);
  }

  function selectCard(duration = 600) {
    rotate = false;
    let width = 140,
      tag = -(currentLuckys.length - 1) / 2,
      locates = [];

    // 计算位置信息, 大于5个分两排显示
    if (currentLuckys.length > 5) {
      let yPosition = [-87, 87],
        l = selectedCardIndex.length,
        mid = Math.ceil(l / 2);
      tag = -(mid - 1) / 2;
      for (let i = 0; i < mid; i++) {
        locates.push({
          x: tag * width * Resolution,
          y: yPosition[0] * Resolution
        });
        tag++;
      }

      tag = -(l - mid - 1) / 2;
      for (let i = mid; i < l; i++) {
        locates.push({
          x: tag * width * Resolution,
          y: yPosition[1] * Resolution
        });
        tag++;
      }
    } else {
      for (let i = selectedCardIndex.length; i > 0; i--) {
        locates.push({
          x: tag * width * Resolution,
          y: 0 * Resolution
        });
        tag++;
      }
    }

    selectedCardIndex.forEach((cardIndex, index) => {
      changeCard(cardIndex, currentLuckys[index]);
      var object = threeDCards[cardIndex];
      new TWEEN.Tween(object.position)
        .to(
          {
            x: locates[index].x,
            y: locates[index].y * Resolution,
            z: 2200
          },
          Math.random() * duration + duration
        )
        .easing(TWEEN.Easing.Exponential.InOut)
        .start();

      new TWEEN.Tween(object.rotation)
        .to(
          {
            x: 0,
            y: 0,
            z: 0
          },
          Math.random() * duration + duration
        )
        .easing(TWEEN.Easing.Exponential.InOut)
        .start();

      object.element.classList.add('prize');
      tag++;
    });

    new TWEEN.Tween(this)
      .to({}, duration * 2)
      .onUpdate(render)
      .start()
      .onComplete(() => {
        // 动画结束后可以操作
        setLotteryStatus();
        console.log(2222)
      });
    console.log(111)
  }

  /**
   * 重置抽奖牌内容
   */
  function resetCard(duration = 500) {
    if (currentLuckys.length === 0) {
      return Promise.resolve();
    }

    selectedCardIndex.forEach(index => {
      let object = threeDCards[index],
        target = targets.sphere[index];

      new TWEEN.Tween(object.position)
        .to(
          {
            x: target.position.x,
            y: target.position.y,
            z: target.position.z
          },
          Math.random() * duration + duration
        )
        .easing(TWEEN.Easing.Exponential.InOut)
        .start();

      new TWEEN.Tween(object.rotation)
        .to(
          {
            x: target.rotation.x,
            y: target.rotation.y,
            z: target.rotation.z
          },
          Math.random() * duration + duration
        )
        .easing(TWEEN.Easing.Exponential.InOut)
        .start();
    });

    return new Promise((resolve, reject) => {
      new TWEEN.Tween(this)
        .to({}, duration * 2)
        .onUpdate(render)
        .start()
        .onComplete(() => {
          selectedCardIndex.forEach(index => {
            let object = threeDCards[index];
            object.element.classList.remove('prize');
          });
          resolve();
        });
    });
  }

  /**
   * 抽奖
   */
  function lottery() {
    rotateBall().then(() => {
      // 将之前的记录置空
      currentLuckys = [];
      selectedCardIndex = [];
      let cardIndex = random(TOTAL_CARDS);
      while (selectedCardIndex.includes(cardIndex)) {
        cardIndex = random(TOTAL_CARDS);
      }
      selectedCardIndex.push(cardIndex);
      const next = luckyDraw.next()
      currentLuckys.push(next.user);
      selectCard();
    });
  }

  /**
   * 保存上一次的抽奖结果
   */
  function saveData() {
  }

  /**
   * 随机抽奖
   */
  function random(num) {
    // Math.floor取到0-num-1之间数字的概率是相等的
    return Math.floor(Math.random() * num);
  }

  /**
   * 切换名牌人员信息
   */
  function changeCard(cardIndex, user) {
    let card = threeDCards[cardIndex].element;

    card.innerHTML = `<div class='name'>${user.name}</div>
    <div class='details'>${user.number}</div>`;
  }

  /**
   * 切换名牌背景
   */
  function shine(cardIndex, color) {
    let card = threeDCards[cardIndex].element;
    card.style.backgroundColor =
      color || 'rgba(0,127,127,' + (Math.random() * 0.7 + 0.25) + ')';
  }

  /**
   * 随机切换背景和人员信息
   */
  function shineCard() {
    let maxCard = ROW_COUNT * COLUMN_COUNT,
      maxUser;
    let shineCard = Math.max(maxCard, 20 + random(maxCard / 10));

    setInterval(() => {
      // 正在抽奖停止闪烁
      if (isLotting) {
        // return;
      }
      maxUser = luckyDraw.users.length;
      for (let i = 0; i < shineCard; i++) {
        let index = random(maxUser),
          cardIndex = random(TOTAL_CARDS);
        // 当前显示的已抽中名单不进行随机切换
        if (selectedCardIndex.includes(cardIndex)) {
          continue;
        }
        shine(cardIndex);
        changeCard(cardIndex, luckyDraw.users[index]);
      }
    }, 500);
  }

  function createHighlight() {
    if (ROW_COUNT < 5 || COLUMN_COUNT < 5) {
      return [];
    }
    let year = new Date().getFullYear().toString();
    let step = 4,
      xoffset = (COLUMN_COUNT - year.length * step + 1) / 2 | 0,
      yoffset = (ROW_COUNT - 5) / 2 | 0;
    highlight = [];

    year.split('').forEach(n => {
      highlight = highlight.concat(
        NUMBER_MATRIX[n].map(item => {
          return `${item[0] + xoffset}-${item[1] + yoffset}`;
        })
      );
      xoffset += step;
    });

    return highlight;
  }
  function processDraw() {
    if (luckyDraw.getRemained().length == 0) {
      return;
    }
    switch (status) {
      case 0:
        status = 1;
        break;
      case 1:
        status = 2;
        const next = luckyDraw.next();
        status = 0;
        break;
    }
  }
  function restart() {
    status = 0;
  }
  function setTitle(title) {
    $('#title').text(title);
    $('#luckyDrawTitle').val(title);
    store.set('luckyDrawTitle', title);
  }
  function getTitle() {
    return store.get('luckyDrawTitle');
  }
  function setLogo(logo) {
    $('#logo').attr('src', logo);
    $('#luckyDrawLogo').val(logo);
    store.set('luckyDrawLogo', logo);
  }
  function getLogo() {
    return store.get('luckyDrawLogo');
  }
})(jQuery, window, document);
