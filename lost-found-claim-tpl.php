<div class="lfclaimform" >
    <h4 class="lostfound lfclaimform error">
        <?PHP echo $message; ?>
    </h4>
    <form method="post" action="<?PHP echo $page_guid; ?>&action=claim&item=<?PHP echo $item->id; ?>" >
        <?php wp_nonce_field('claim', 'security'); ?>
        <table class="lostfound lfclaimform" >
            <tr class="lostfound lfclaimform "  >
                <td class="lostfound lfclaimform " colspan="2"  >
                    <span class="itemname">Item #: <?PHP echo $item->id; ?><br/>
                        Name: <?PHP echo $item->name; ?></span>
                </td>
            </tr>
            <tr class="lostfound lfclaimform " >
                <td class="lostfound lfclaimform "  >
                    <span class="itemdescription">
                        <?PHP echo $item->description; ?>
                    </span>
                </td>
                <td class="lostfound lfclaimform"  >
                    <img src="<?PHP echo $lf_upload_dir['baseurl'] . $item->picname; ?>" width="270" >
                </td>
            </tr>
            <tr class="lostfound lfclaimform" >
                <td class="lostfound lfclaimform" colspan="2" >
                    Submitted: <?PHP echo date($lf_date_format, strtotime($item->submitted)); ?>
                </td>
            </tr>
            <tr class="lostfound lfclaimform" >
                <td class="lostfound lfclaimform" colspan="2" >
                    <h2 class="lostfound lfclaimform">
                        To claim this item please enter your name and either your email
                        address or phone number below.
                    </h2>

                </td>
            </tr>
            <tr class="lostfound lfclaimform" >
                <td class="lostfound lfclaimform"  >
                    Name:
                </td>
                <td class="lostfound lfclaimform"  >
                    <input name="claim_name" class="lostfound lfclaimform" />
                </td>
            </tr>
            <tr class="lostfound lfclaimform" >
                <td class="lostfound lfclaimform" >
                    Email Address:
                </td>
                <td class="lostfound lfclaimform" >
                    <input name="claim_email" class="lostfound lfclaimform" />
                </td>
            </tr>
            <tr class="lostfound lfclaimform" >
                <td class="lostfound lfclaimform" >
                    Phone Number:
                </td>
                <td class="lostfound lfclaimform" >
                    <input name="claim_phone" class="lostfound lfclaimform" />
                </td>
            </tr>
            <tr class="lostfound lfclaimform" >
                <td class="lostfound lfclaimform" colspan="2" >
                    <input type="submit" name="submit" value="Submit claim" class="button-primary"/>
                </td>
            </tr>
        </table>
    </form>
</div>