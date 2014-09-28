(function($, win, doc) {
  function LuckyDraw(names) {
    this.KEY_PREFIX = 'luckyDraw_';
    this.ALL_NAMES_KEY = this.KEY_PREFIX + 'all';
    this.allNames = names || [];
    this.names = [];
    this.drawnNames = [];
    if (this.allNames.length === 0) {
      this.allNames = store.get(this.ALL_NAMES_KEY) || [];
    } else {
      store.set(this.ALL_NAMES_KEY, this.allNames);
    }
    var self = this;
    this.allNames.forEach(function(name) {
      if (store.get(self.KEY_PREFIX + name)) {
        self.drawnNames.push(name);
      } else {
        self.names.push(name);
      }
    });
  }
  LuckyDraw.prototype = {
    constructor: LuckyDraw,
    next: function() {
      var index = Math.random() * this.names.length | 0;
      var name = this.names[index];
      if (!name) {
        return false;
      }
      this.names.splice(index, 1);
      this.drawnNames.push(name);
      store.set(this.KEY_PREFIX + name, name);
      return {
        index: index,
        name: name
      };
    },
    reset: function() {
      this.names = this.allNames.slice(0);
      this.drawnNames = [];
      for (var key in store.getAll()) {
        if (key.indexOf(this.KEY_PREFIX) > -1) {
          store.remove(key);
        }
      }
      store.set(this.ALL_NAMES_KEY, this.allNames);
    },
    update: function(names) {
      this.allNames = names;
      this.reset();
    },
    getRemained: function() {
      return this.names;
    },
    getDrawn: function() {
      return this.drawnNames;
    },
    getAll: function() {
      return this.allNames;
    }
  };
  $(doc).on('keyup', function(event) {
    if (event.which != 32) {
      return;
    }
    if (luckyDraw.getRemained().length == 0) {
      return;
    }
    event.preventDefault();
    switch (status) {
      case 0:
        TagCanvas.Update(id);
        status = 1;
        tc.yaw = 2.1913498628418893;
        tc.pitch = 4.494216926075424;
        break;
      case 1:
        status = 2;
        var next = luckyDraw.next();
        var a = tags.find('a').eq(next.index).css({
          'font-size': '4.5em',
          'color': 'red'
        });
        drawn.prepend($('<li>').text(next.name));
        //先停止转动
        tc.yaw = tc.pitch = 0;
        TagCanvas.Update(id);
        window.setTimeout(function() {
          TagCanvas.TagToFront(id, {
            index: next.index,
            time: 300,
            callback: function() {
              a.remove();
              status = 0;
            }
          });
        }, 0);
        break;
    }
  }).on('keydown', function(event) {
    if (event.which == 32) {
      event.preventDefault();
    }
  }).on('click', '#reset', function() {
    luckyDraw.reset();
    restart();
  }).on('click', '#settings', function() {
    $('#luckyDrawNames').val(luckyDraw.getAll().join('\n'));
    status = 2;
  }).on('click', '#save', function() {
    var names = $('#luckyDrawNames').val().split('\n').filter(function(name) {
      return $.trim(name) != '';
    });
    var title = $('#luckyDrawTitle').val();
    var logo = $('#luckyDrawLogo').val();
    luckyDraw.update(names);
    setTitle(title);
    setLogo(logo);
    $('#drawModal').modal('hide');
    restart();
  }).on('hidden.bs.modal', '#drawModal', function() {
    status = 0;
  }).on('change', '#luckyDrawCompetition', function() {
    var id = $(this).val();
    if (id) {
      $.ajax({
        type: 'get',
        url: $(this).data('url'),
        data: {
          id: id,
        },
        dataType: 'json',
        success: function(json) {
          $('#luckyDrawNames').val(json.data.join('\n'));
        }
      })
    }
  });
  var luckyDraw = new LuckyDraw();
  var tags = $('<div id="tags">').appendTo($('body')).hide();
  var drawn = $('#drawn');
  var id = 'canvas';
  var options = {};
  var status;
  setOptions();
  setTitle();
  setLogo();
  restart();
  if (luckyDraw.getAll().length === 0) {
    $('#drawModal').modal('show');
  }
  function buildTags() {
    tags.empty();
    luckyDraw.getRemained().forEach(function(name, i) {
      $('<a href="javascript:;">')
        .text(name)
        .css({
          'font-size': '1em'
        })
        .attr('id', 'tag-' + i)
        .appendTo(tags);
    });
    $('<a href="javascript:;">')
      .text(' ')
      .css({
        'font-size': '4.5em'
      })
      .attr('id', 'tag-placeholder')
      .appendTo(tags);
  }
  function buildDrawn() {
    drawn.empty();
    luckyDraw.getDrawn().forEach(function(name, i) {
      drawn.prepend($('<li>').text(name));
    });
  }
  function restart() {
    status = 0;
    buildTags();
    buildDrawn();
    TagCanvas.Start(id, 'tags');
    tc = TagCanvas.tc[id];
  }
  function setOptions() {
    TagCanvas.textColour = null;
    TagCanvas.textHeight = 40;
    TagCanvas.outlineMethod = 'colour';
    TagCanvas.fadeIn = 800;
    TagCanvas.outlineColour = '#039';
    TagCanvas.outlineOffset = 0;
    TagCanvas.depth = 0.97;
    TagCanvas.minBrightness = 0.2;
    TagCanvas.reverse = true;
    TagCanvas.shadowBlur = 2;
    TagCanvas.shadowOffset = [1, 1];
    TagCanvas.wheelZoom = false;
    TagCanvas.lock = 'xy';
    TagCanvas.weight = true;
    TagCanvas.weightMode = 'size';
    TagCanvas.minSpeed = 3;
    TagCanvas.maxSpeed = 5;
    TagCanvas.initial = [0, 0];
  }
  function setTitle(title) {
    title = title || store.get('luckyDrawTitle');
    if (title) {
      $('#title').text(title);
      $('#luckyDrawTitle').val(title);
      store.set('luckyDrawTitle', title);
    }
  }
  function getTitle() {
    return store.get('luckyDrawTitle');
  }
  function setLogo(logo) {
    logo = logo || store.get('luckyDrawLogo');
    if (logo) {
      $('#logo').attr('src', logo);
      $('#luckyDrawLogo').val(logo);
      store.set('luckyDrawLogo', logo);
    }
  }
  function getLogo() {
    return store.get('luckyDrawLogo');
  }
})(jQuery, window, document);