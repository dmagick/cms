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
            alert(msg);
        });
    });
});

