$(function() {
    $('#post-add').hide();
    $('#post-add-link').click(function() {
        $('#post-add').toggle();
    });

    $("#post-add-submit").click(function() {
        var response = $.ajax({
            type: 'POST',
            url: 'adminpost/add',
            data: {
                subject: $('#subject-new').val(),
                content: $('#content-new').val(),
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
                subject: $('#subject-' + postid).val(),
                content: $('#content-' + postid).val(),
                status: $('#status-' + postid + ':checked').val()
            },
        }).done(function( msg ) {
            $('#t-pm-details-' + postid).html(msg);
            $('#t-pm-' + postid).show('fast').delay(2000);
            $('#t-pm-' + postid).hide('fast');
        });
    });

});

