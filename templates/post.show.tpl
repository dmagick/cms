            <div class="postpagination">
                ~post.previous.link~
                &nbsp;
                ~post.next.link~
            </div>

            <h2>~subject~</h2>
            ~content~
            <br/>

            <div id="comments-toggle">Comments?</div>
            <div class="comments">
                <div id="comments-list">
                    ~commentlist~
                </div>
                <br/>
                ~template::include::comment.form~
            </div>


