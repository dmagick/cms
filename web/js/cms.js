$(function() {
    $('#comments-toggle').click(function() {
        $('.comments').toggle();
    });

    $('#comment-send').click(function() {
        var response = $.ajax({
            type: 'POST',
            url: $('#comment-form').attr('action'),
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

