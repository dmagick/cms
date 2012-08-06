
<div id="contact">
    ~contactheader~
    <form method="post" action="~url::baseurl~/contact/submit">
    <input type="hidden" name="token" value="~token~" />
    <div class="contact-question">
        Your email address
    </div>
    <div class="contact-answer">
        <input type="text" name="email" value="~email~" />
    </div>
    <div class="contact-question">
        How can we help you?
    </div>
    <div class="contact-answer">
        <textarea name="message">~message~</textarea>
    </div>
    <div class="contact-question">
        To prove you're not spam,<br/>
        please answer this simple question:<br/>
        ~spamcheck~
    </div>
    <div class="contact-answer">
        Answer: <input type="text" name="spamcheck" value="" />
    </div>
    <div class="contact-submit">
        <input type="submit" value="Send your message">
    </div>
    </form>
</div>
