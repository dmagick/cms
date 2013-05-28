$(function() {
    $(".comment-delete-button").click(function() {
        commentid = this.id;
        var response = $.ajax({
            type: 'POST',
            url: 'admincomments/delete/' + commentid,
        }).done(function( msg ) {
            location.reload();
        });
    });

    $(".comment-update-button").click(function() {
        commentid = this.id;
        var response = $.ajax({
            type: 'POST',
            url: 'admincomments/update/' + commentid,
            data: {
                commentemail: $('#commentemail-' + commentid).val(),
                commentby:    $('#commentby-' + commentid).val(),
                content:      $('#content-' + commentid).val(),
                status:       $('#status-' + commentid + ':checked').val()
            },
        }).done(function( msg ) {
            $('#t-cm-details-' + commentid).html(msg);
            $('#t-cm-' + commentid).show('fast').delay(2000);
            $('#t-cm-' + commentid).hide('fast');
        });
    });

});

