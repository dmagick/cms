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

    $(".post-add-button").click(function() {
        var response = $.ajax({
            type: 'POST',
            url: 'adminpost/add',
            data: {
                subject:  $('#subject-new').val(),
                content:  $('#content-new').val(),
                postdate: $('#postdate-new').val(),
            },
        }).done(function( msg ) {
            location.reload();
        });
    });

    $(".post-delete-button").click(function() {
        postid = this.id;
        var response = $.ajax({
            type: 'POST',
            url: 'adminpost/delete/' + postid,
        }).done(function( msg ) {
            location.reload();
        });
    });

    $(".post-update-button").click(function() {
        postid = this.id;
        var response = $.ajax({
            type: 'POST',
            url: 'adminpost/update/' + postid,
            data: {
                subject:  $('#subject-' + postid).val(),
                content:  $('#content-' + postid).val(),
                status:   $('#status-' + postid + ':checked').val()
            },
        }).done(function( msg ) {
            $('#t-pm-details-' + postid).html(msg);
            $('#t-pm-' + postid).show('fast').delay(2000);
            $('#t-pm-' + postid).hide('fast');
        });
    });

});

