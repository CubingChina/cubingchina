$(function() {
  $('input, textarea').placeholder();
  $.fn.dropdownHover && $('[data-hover="dropdown"]').dropdownHover();
  if (location.hostname === 'cubingchina.com'){
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
    ga('create', 'UA-3512083-6', 'cubingchina.com');
    ga('send', 'pageview');
  }
});