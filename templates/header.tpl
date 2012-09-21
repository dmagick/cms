<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd"> 
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

        <link rel="stylesheet" href="~url::baseurl~/web/css/cms.css?v=0.0.2" type="text/css" media="screen" />

        <script src="~url::baseurl~/web/js/jquery-1.8.0.min.js"></script>

        <script src="~url::baseurl~/web/js/slides.jquery.js"></script>
    
        <script>
            $(function(){
                $("#slides").slides({
                    play: 5000,
                    pause: 5000,
                    hoverPause: true,
                    slideSpeed: 1500,
                    generatePagination: false,
                    next: 'slide-next',
                    prev: 'slide-prev',
                    animationStart: function(current){
                        $('.caption').animate({
                            top: 600
                        },100);
                    },
                    animationComplete: function(current){
                        $('.caption').animate({
                            top: 560
                        },200);
                    },
                    slidesLoaded: function() {
                        $('.caption').animate({
                            top: 560
                        },200);
                    }
                });
            });
        </script>

    </head>

    <body>
        <div id="header" class="round">
            <div id="logo">
                Splash Of Photography
            </div>
            <div id="menu">
                <ul>
                    ~menu~
                </ul>
            </div>
        </div>
        <div id="content" class="round">

