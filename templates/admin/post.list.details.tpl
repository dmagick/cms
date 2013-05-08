    <tr id="t-pm-~postid~" style="display: none;">
        <td colspan="7"><div id="t-pm-details-~postid~"></div></td>
    </tr>
    <tr id="t-p-~postid~">
        <td>
            ~postid~
        </td>
        <td>
            <input type="text" name="subject[~postid~]" id="subject-~postid~" value="~subject~">
        </td>
        <td>
            <textarea name="content[~postid~]" id="content-~postid~">~content~</textarea>
        </td>
        <td>
            ~postdate~
        </td>
        <td>
            <input type="radio" name="status[~postid~]" id="status-~postid~" value="live"~livechecked~>Live<br/>
            <input type="radio" name="status[~postid~]" id="status-~postid~" value="uc"~ucchecked~>U/C<br/>
        </td>
        <td>
            ~imagelist~
        </td>
        <td>
            <a href="~url::baseurl~/post/preview/~postid~" target="_blank">Preview</a><br/>
            <input type="button" id="~postid~" class="post-update-button" value="Update" /><br/>
            <input type="button" id="~postid~" class="post-delete-button" value="Delete" /><br/>
        </td>
    </tr>

