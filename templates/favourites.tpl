<script src="~url::baseurl~/web/js/jquery-1.8.0.min.js"></script>
<script src="~url::baseurl~/web/js/slides.jquery.js"></script>
        <script>
            $(function(){
                $("#slides").slides({
                    play: 5000,
                    pause: 5000,
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

~gallery~
