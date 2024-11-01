<!-- lost-found-add-tpl.php -->
<br/>
<div id="lfupload" style="display: ">
    <form method="post" action="<?PHP echo $page_guid; ?>&action=submit" >
        <?php wp_nonce_field('submit', 'security'); ?>
        <table class="lostfound lfupload">
            <tr class="lostfound lfupload" >
                <td class="lostfound lfupload" id="itemname">
                    Item name:
                </td>
                <td class="lostfound lfupload" >
                    <input id="item_name" type="text" name="item_name" size="50" />
                </td>
            </tr>

            <tr class="lostfound lfupload">
                <td class="lostfound lfupload" id="lfuploadimage">
                    <!-- TODO: check for FORCE_SSL_ADMIN and adjust accordingly -->
                    Upload Image. Choose "insert into post" to add the image.
                </td>
                <td>
                    <label for="upload_image">
                        <input id="upload_image" type="text" size="50" name="upload_image" value="" />
                        <input id="upload_image_button" type="button" value="Select/Upload Image" />
                    </label>
                </td>
            </tr>
            <tr class="lostfound lfupload" >
                <td class="lostfound lfupload" >
                    Description of item:
                </td>
                <td class="lostfound lfupload" >
                    <textarea cols="50" rows="8" name="description" id="description" ></textarea>
                </td>
            </tr>
            <tr class="lostfound lfupload" >
                <td class="lostfound lfupload" colspan="2">
                    <input type="submit" name="submit" value="Submit item"/>
                </td>
            </tr>
        </table>
    </form>
</div><!-- lfupload -->

<!-- end lost-found-add-tpl.php -->