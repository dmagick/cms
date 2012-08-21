            $(document).ready(function() {
                $(".fancybox").fancybox();
                $("#show-gallery").click(function() {
                    $.fancybox.open([
                        {
                            href : 'http://dev/5/picture.jpg',
                            title : 'My title'
                        },
                        {
                            href : 'http://dev/5/picture1.jpg',
                            title : '2nd title'
                        },
                        {
                            href : 'http://dev/5/picture2.jpg',
                            title : '2nd title'
                        },
                        {
                            href : 'http://dev/5/picture3.jpg',
                            title : '2nd title'
                        }
                        ],
                        {
                        helpers : {
                            title : {
                                type: 'outside'
                            },
                            thumbs : {
                                width: 75,
                                height: 50
                            },
                            overlay	: {
                                opacity: 0.8
                            }
                        },
                        prevEffect	: 'fade',
                        nextEffect	: 'fade'
                    });
                });

            });

