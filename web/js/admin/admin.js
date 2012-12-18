$(function() {
    $("input[type='button']").click(function() {
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

