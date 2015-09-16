(function(factory){if(typeof define==='function'&&define.amd){define(['jquery'],factory)}else if(typeof exports==='object'){module.exports=factory(require('jquery'))}else{factory(jQuery)}}(function($){var pluses=/\+/g;function encode(s){return config.raw?s:encodeURIComponent(s)}function decode(s){return config.raw?s:decodeURIComponent(s)}function stringifyCookieValue(value){return encode(config.json?JSON.stringify(value):String(value))}function parseCookieValue(s){if(s.indexOf('"')===0){s=s.slice(1,-1).replace(/\\"/g,'"').replace(/\\\\/g,'\\')}try{s=decodeURIComponent(s.replace(pluses,' '));return config.json?JSON.parse(s):s}catch(e){}}function read(s,converter){var value=config.raw?s:parseCookieValue(s);return $.isFunction(converter)?converter(value):value}var config=$.cookie=function(key,value,options){if(arguments.length>1&&!$.isFunction(value)){options=$.extend({},config.defaults,options);if(typeof options.expires==='number'){var days=options.expires,t=options.expires=new Date();t.setMilliseconds(t.getMilliseconds()+days*864e+5)}return(document.cookie=[encode(key),'=',stringifyCookieValue(value),options.expires?'; expires='+options.expires.toUTCString():'',options.path?'; path='+options.path:'',options.domain?'; domain='+options.domain:'',options.secure?'; secure':''].join(''))}var result=key?undefined:{},cookies=document.cookie?document.cookie.split('; '):[],i=0,l=cookies.length;for(;i<l;i++){var parts=cookies[i].split('='),name=decode(parts.shift()),cookie=parts.join('=');if(key===name){result=read(cookie,value);break}if(!key&&(cookie=read(cookie))!==undefined){result[name]=cookie}}return result};config.defaults={};$.removeCookie=function(key,options){$.cookie(key,'',$.extend({},options,{expires:-1}));return!$.cookie(key)}}));
$(function() {
  $('input, textarea').placeholder();
  $.fn.dropdownHover && $('[data-hover="dropdown"]').dropdownHover();
  $('.wrapper table:not(.table)').addClass('table table-bordered table-condensed').parent().addClass('table-responsive');
  if (!('ontouchstart' in window)) {
    (function() {
      var win = $(window);
      var winHeight = win.height();
      $('.table-responsive table').each(function() {
        var table = $(this);
        var tableParent = table.parent();
        var scroll = $('<div>');
        var scrollParent = $('<div class="table-responsive">');
        var tableWidth = table.width();
        var tableHeight = table.height();
        var tableParentWidth = tableParent.width();
        var tableParentHeight = tableParent.height();
        var offset = tableParent.offset();
        table.removeAttr('style width height');
        scroll.css({
          height: 1,
          width: tableWidth
        });
        scrollParent.append(scroll).insertAfter(tableParent).css({
          position: 'fixed'
        }).on('scroll', function() {
          tableParent[0].scrollLeft = this.scrollLeft;
        });
        tableParent.on('scroll', function() {
          scrollParent[0].scrollLeft = this.scrollLeft;
        });
        win.on('scroll', function() {
          if (tableWidth <= tableParentWidth || tableHeight < winHeight * 2 || winHeight + win.scrollTop() > offset.top + tableParentHeight) {
            scrollParent.hide();
          } else {
            scrollParent.show().scrollLeft(tableParent.scrollLeft());
          }
        }).on('resize', function() {
          scrollParent.css({
            width: tableParentWidth,
            bottom: -parseInt(tableParent.css('margin-bottom'))
          });
          win.trigger('scroll');
          winHeight = win.height();
          tableWidth = table.width();
          tableHeight = table.height();
          tableParentWidth = tableParent.width();
          tableParentHeight = tableParent.height();
          offset = tableParent.offset();
        }).trigger('resize');
      });
    })();
  }
  (function() {
    var mouseEvent;
    var lastLength = 0;
    var battleControl = $('<div id="battle-control">').appendTo(document.body);
    var listWrapper = $('<div class="battle-list">').appendTo(battleControl);
    var truncateButton = $('<button class="truncate"><i class="fa fa-close"></i></button>').appendTo(battleControl);
    var battleButton = $('<a target="_blank">GO</a>').appendTo($('<button class="go"></button').appendTo(battleControl));
    truncateButton.on('click', function() {
      $.each($.cookie(), function(key, value) {
        if (key.indexOf('battle_') === 0) {
          removeBattlePerson(key.substr('7'));
        }
      });
      updateBattleList();
    });
    $('<button class="rocket"><i class="fa fa-rocket"></i></button').appendTo(battleControl);
    battleControl.find('button.rocket').on('focus', function() {
      battleControl.addClass('active');
    }).on('blur', function() {
      battleControl.removeClass('active');
    });
    updateBattleList();
    $(document).on('click', 'input.battle-person', function(e) {
      var id = $(this).data('id');
      var name = $(this).data('name');
      if (this.checked) {
        if (getBattleList().length >= 4) {
          e.preventDefault();
          //todo notifycation
          return false;
        }
        event = e;
        addBattlePerson(id, name);
      } else {
        removeBattlePerson(id);
      }
    });
    function addBattlePerson(id, name) {
      var key = 'battle_' + id;
      $.cookie(key, name, {
        expires: 365,
        path: '/'
      });
      updateBattleList();
    }
    function removeBattlePerson(id) {
      var key = 'battle_' + id;
      $.removeCookie(key, {
        path: '/'
      });
      $('input.battle-person[data-id="' + id + '"]').prop('checked', false);
      updateBattleList();
    }
    function updateBattleList() {
      var list = getBattleList();
      var ids = [];
      if (list.length > 0){
        listWrapper.empty();
        list.forEach(function(person) {
          ids.push(person.id);
          $('<div class="battle-person">').append(
            $('<a>').attr({
              href: '/results/p/' + person.id,
              target: '_blank'
            }).text(person.name),
            $('<i class="fa fa-close">').on('click', function() {
              removeBattlePerson(person.id);
            })
          ).appendTo(listWrapper);
        });
        if (lastLength < list.length) {
          battleControl.stop().css({
            right: event ? $(window).width() - event.clientX : 200,
            bottom: event ? $(window).height() - event.clientY : 200
          }).animate({
            right: 5,
            bottom: 50
          }, 800, 'swing');
        }
        battleControl.show();
      } else {
        battleControl.hide();
      }
      if (list.length > 0) {
        battleButton.attr('href', '/results/battle?' + $.param({ids: ids}));
      } else {
        battleButton.attr('href', 'javascript: void(0)');
      }
      lastLength = list.length;
    }
    function getBattleList() {
      var list = [];
      $.each($.cookie(), function(key, value) {
        if (key.indexOf('battle_') === 0) {
          list.push({
            id: key.substr('7'),
            name: value
          });
        }
      });
      return list;
    }
  })();
  if (location.hostname === 'cubingchina.com'){
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
    ga('create', 'UA-3512083-6', 'cubingchina.com');
    ga('send', 'pageview');
    (function() {
      var hm = document.createElement("script");
      hm.src = "//hm.baidu.com/hm.js?2ba93b9ebfb91795df4f4859b4ec9716";
      var s = document.getElementsByTagName("script")[0];
      s.parentNode.insertBefore(hm, s);
    })();
  }
});