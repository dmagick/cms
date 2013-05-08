<h4 id="post-upload-link">Upload images to a post</h4>
<div id="pm-details-upload"></div>
<div id="post-upload">
    <form method="post" action="~url::adminurl~/adminpost/upload" enctype="multipart/form-data">
        <table border="1" cellspacing="5" cellpadding="5" width="100%">
            <tr>
                <td>
                    <select name="postid">
                        <option value="">Choose a post to upload to</option><br/>
                        ~uploadpostlist~
                    </select>
                </td>
                <td>
                    <input type="file" name="uploadimage[]" /><br/>
                    <input type="file" name="uploadimage[]" /><br/>
                    <input type="file" name="uploadimage[]" /><br/>
                    <input type="file" name="uploadimage[]" /><br/>
                    <input type="file" name="uploadimage[]" /><br/>
                </td>
            <tr>
                <td colspan="2">
                    <input type="submit" id="post-upload-submit" value="Upload">
                </td>
            </tr>
        </table>
    </form>
</div>

