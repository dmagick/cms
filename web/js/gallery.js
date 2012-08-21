            $(document).ready(function() {
                $(".fancybox").fancybox();
                $("#show-gallery").click(function() {
                    $.fancybox.open([                        {
                            href  : 'http://localhost/~csmith/splashofphotography.com/gallery/_MG_9702_small.jpg',
                            title : 'My title',
                        },
                        {
                            href  : 'http://localhost/~csmith/splashofphotography.com/gallery/picture1.jpg',
                            title : 'My title',
                        },
                        {
                            href  : 'http://localhost/~csmith/splashofphotography.com/gallery/picture2.jpg',
                            title : 'My title',
                        },
                        {
                            href  : 'http://localhost/~csmith/splashofphotography.com/gallery/picture3.jpg',
                            title : 'My title',
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