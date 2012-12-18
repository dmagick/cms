~flashmessage~
<h4 id="post-add-link">Add new post</h4>
<div id="pm-details-add"></div>
<div id="post-add">
    <form method="post" action="~url::adminurl~/adminpost/add">
        <table border="1" cellspacing="5" cellpadding="5" width="100%">
            <tr>
                <th>
                    Subject
                </th>
                <th>
                    Content
                </th>
                <th>
                    Date
                </th>
            </tr>
            <tr>
                <td>
                    <input type="text" name="subject" id="subject-new" value="" />
                </td>
                <td>
                    <textarea name="content" id="content-new"></textarea>
                </td>
                <td>
                    <input type="text" name="postdate" id="postdate-new" value="" />
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <input type="button" id="post-add-submit" value="Add new post">
                </td>
            </tr>
        </table>
    </form>
</div>

