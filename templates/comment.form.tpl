<div id="commentform-message">
    Thanks for your comment! It is awaiting moderation, check back shortly.
</div>

<div id="commentform">
    Fill in the form below to add your comment.<br/>
    <form id="comment-form" action="~url::baseurl~/post/comment" method="post">
        <input type="hidden" name="postid" id="comment-postid" value="~postid~" />
        <div class="commentform-desc">Your name:</div>
        <div class="commentform-val"><input type="text" name="name" id="comment-name" /></div>
        <br/>
        <div class="commentform-desc">Your email address:</div>
        <div class="commentform-val"><input type="text" name="email" id="comment-email" /></div>
        <br/>
        <div class="commentform-desc commentform-last">Your comment:</div>
        <div class="commentform-val commentform-last"><textarea name="comment" id="comment-comment" rows="7"></textarea></div>
        <br/>
        <div class="commentform-desc">&nbsp;</div>
        <div class="commentform-val"><input type="button" id="comment-send" value="Send comment" /></div><br/>
    </form>
</div>

