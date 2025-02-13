<!doctype html>
<html lang="en" data-critters-container>
<head>
  <meta charset="utf-8">
  <title>Instituto de Formación Artística</title>
  <base href="/">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/x-icon" href="favicon.ico">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css">
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>


    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" integrity="sha384-oS3vJWv+0UjzBfQzYUhtDYW+Pj2yciDJxpsK1OYPAYjqT085Qq/1cq5FLXAZQ7Ay" crossorigin="anonymous">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Theme style -->

    <!-- Google Font: Source Sans Pro -->
    <!-- <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet"> -->

    <!-- PARA ADMINLTE -->
    <!-- Google Font: Source Sans Pro -->
    <!-- <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback"> -->

<!-- <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Trirong"> -->

<link href="https://cdn.syncfusion.com/ej2/material.css" rel="stylesheet">

<link href="assets/angular/browser/assets/plugins/fontawesome-free/css/all.min.css" rel="stylesheet">
<link href="assets/angular/browser/assets/dist/css/adminlte.min.css" rel="stylesheet">
<link href="assets/angular/browser/assets/scripts_validation/estilos.min.css" rel="stylesheet">
<link rel="stylesheet" href="/assets/angular/browser/styles-2EHULSVP.css" media="print" onload="this.media='all'"><noscript><link rel="stylesheet" href="/assets/angular/browser/styles-2EHULSVP.css"></noscript></head>
<body>
  <app-root></app-root>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous">
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous">
  </script>

<!-- PARA ADMINLTE -->
<script src="assets/angular/browser/assets/plugins/jquery/jquery.min.js"></script>
<script src="assets/angular/browser/assets/dist/js/adminlte.min.js"></script>

<!-- REEMPLAZO FOR PRODUCTION -->
 <!-- TODOS LOS "assets" reemplazar por "assets/angular/browser/assets"
  YANO ES NECESARIA YA Q EN EL PROYECTO ANGULAR HICE Q TENGA LA MISMA RUTA Q EN EL PROD-->
<link rel="modulepreload" href="/assets/angular/browser/chunk-BOIRNZ36.js"><link rel="modulepreload" href="/assets/angular/browser/chunk-N6H3ZBOR.js"><link rel="modulepreload" href="/assets/angular/browser/chunk-YGQKKRMP.js"><link rel="modulepreload" href="/assets/angular/browser/chunk-O7S4L63H.js"><script src="/assets/angular/browser/polyfills-SCHOHYNV.js" type="module"></script><script src="/assets/angular/browser/main-4E5UB77S.js" type="module"></script></body>


<!-- NIEVE -->
<!-- <style>
  body {
    margin: 0;
  }
  #snowfall-wrapper {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
  }

  .flake {
    position: absolute;
    color: #fff;
    pointer-events: none;
    display: block;
  }
</style>
<script>
  (function ($) {
    $.snowfall = {
      intervals: [],
      $wrapper: null,
      start: function (options, $wrapper) {
        var options = $.extend({}, {
          size: { min: 10, max: 20 },
          interval: 500,
          color: '#fff',
          content: '<img src="path/to/your/snowflake.gif" alt="snowflake" />',
          disappear: 'linear'
        }, options);

        if ($wrapper == undefined) {
          $('body').append('<div id="snowfall-wrapper" />');
          $wrapper = $('#snowfall-wrapper');
          $wrapper.css({
            'overflow': 'hidden',
            'height': '100%',
            'width': '100%',
            'position': 'fixed',
            'top': '0',
            'left': '0'
          });
        }

        var $snowfall = $('<div class="flake" />').css({ 'position': 'fixed', 'top': '-50px' }).html(options.content);
        $.snowfall.$wrapper = $wrapper;
        $.snowfall.$wrapper.show();

        $.snowfall.intervals.push(setInterval(function () {
          var wrapperWidth = $wrapper.width(),
            wrapperHeight = $wrapper.height(),
            flakeSize = options.size.min + (Math.random() * options.size.max),
            duration = (wrapperHeight * 10) + (Math.random() * 2000),
            startPosition = (Math.random() * wrapperWidth) - 100;

          $snowfall.clone().appendTo($wrapper).css({
            'left': startPosition,
            'opacity': 0.5 + Math.random(),
            'font-size': flakeSize,
            'color': options.color
          }).animate({
            top: wrapperHeight - 40,
            left: (startPosition - 100) + (Math.random() * 200),
            opacity: 0.2
          }, duration, options.disappear, function () {
            $(this).remove();
          });
        }, options.interval));
      },
      stop: function () {
        $.snowfall.intervals.forEach(function (interval) {
          $.snowfall.$wrapper.hide();
          $.snowfall.$wrapper.children('div').each(function () {
            $(this).remove();
          });
          clearInterval(interval);
        });
      }
    };
  })(jQuery);

  $.snowfall.start({
    content: '<img src="/fijos/nieve.png" alt="snowflake" width="50px"/>',
    size: { min: 20, max: 50 }
  });
</script> -->

<!-- NIEVE HASTA ACA -->
</html>
