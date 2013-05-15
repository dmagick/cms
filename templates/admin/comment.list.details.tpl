    <tr id="t-cm-~commentid~" style="display: none;">
        <td colspan="7"><div id="t-cm-details-~commentid~"></div></td>
    </tr>
    <tr id="t-c-~commentid~">
        <td>
            ~commentid~
        </td>
        <td>
            <input type="text" name="commentemail[~commentid~]" id="commentemail-~commentid~" value="~commentemail~">
        </td>
        <td>
            <input type="text" name="commentby[~commentid~]" id="commentby-~commentid~" value="~commentby~">
        </td>
        <td>
            <textarea name="content[~commentid~]" id="content-~commentid~">~content~</textarea>
        </td>
        <td>
            ~commentdate~
        </td>
        <td>
            <input type="radio" name="status[~commentid~]" id="status-~commentid~" value="live"~livechecked~>Live<br/>
            <input type="radio" name="status[~commentid~]" id="status-~commentid~" value="uc"~ucchecked~>U/C<br/>
        </td>
        <td>
            <a href="~url::baseurl~/post/preview/~postid~" target="_blank">Preview Post</a><br/>
            <input type="button" id="~commentid~" class="comment-update-button" value="Update" /><br/>
            <input type="button" id="~commentid~" class="comment-delete-button" value="Delete" /><br/>
        </td>
    </tr>

