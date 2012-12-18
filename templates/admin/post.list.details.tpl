    <tr id="t-pm-~postid~" style="display: none;">
        <td colspan="6"><div id="t-pm-details-~postid~"></div></td>
    </tr>
    <tr id="t-p-~postid~">
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
            ~postedby~
        </td>
        <td>
            <input type="radio" name="status[~postid~]" id="status-~postid~" value="live"~livechecked~>Live<br/>
            <input type="radio" name="status[~postid~]" id="status-~postid~" value="uc"~ucchecked~>U/C<br/>
        </td>
        <td>
            <input type="button" id="~postid~" value="Update" /><br/>
            <a href="~url::baseurl~/post/preview/~postid~" target="_blank">Preview</a><br/>
            <a href="~url::adminurl~/adminpost/delete/~postid~">Del</a>
        </td>
    </tr>

