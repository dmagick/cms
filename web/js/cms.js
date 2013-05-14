$(function() {
    $('#comments-toggle').click(function() {
        $('.comments').toggle();
    });

    $('#comment-send').click(function() {
        var response = $.ajax({
            type: 'POST',
            url: 'post/comment',
            data: {
                comment: $('#comment-comment').val(),
                email:   $('#comment-email').val(),
                name:    $('#comment-name').val(),
                postid:  $('#comment-postid').val(),
            },
        }).done(function( msg ) {
            $('#commentform-message').show();
        });
    });
});

