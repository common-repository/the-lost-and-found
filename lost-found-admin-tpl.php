<!-- lost-found-admin-tpl.php -->
<script type="text/javascript" >
    var nonce = "<?PHP wp_create_nonce('checkbox'); ?>";
    var please_wait_image = "<IMG src=\"<?PHP echo admin_url(); ?>images/loading.gif\" >";
    function ajax_checkbox(checked) {

        //put together the post data
        var data = {
            action: 'lost_found_email_checkbox',
            checkbox: checked,
            security: nonce,
            user: "<?PHP echo $user->ID; ?>"
        };
        document.getElementById("email_list").innerHTML = please_wait_image;

        jQuery.post(ajaxurl, data, function(response) {
            document.getElementById("email_list").innerHTML = response;
        });
    }

    function lfEditItems() {
        document.getElementById("lfnamespan").style.display = "none";
        document.getElementById("lfnameedit").style.display = "";
        document.getElementById("itemdescedit").style.display = "";
        document.getElementById("itemdescription").style.display = "none";
    }
</script>


<br/>
<div class="lfadmin" >
    <?PHP if ("item_choice" == $adminshow): ?>
        <form method="post" action="<?PHP echo $page_guid; ?>&action=admin&edit=true&item_choice=<?PHP echo $item->id; ?>" >
            <input type="hidden" name="admin_nonce" value="<?PHP echo $nonce; ?>" />
            <input type="hidden" name="lfid" value="<?PHP echo $item->id; ?>" />
        <?PHP endif; ?>
        <input type="checkbox" onChange="ajax_checkbox(this.checked)" <?PHP echo $on_email_list; ?> />
        I want to be emailed whenever there is a new item.<br/>
        <span id="email_list" ></span>
        <h2 class="lostfound lfitem" ><?PHP echo $message; ?></h2>
        <?PHP if ("item_choice" == $adminshow): ?>
            <table class="lostfound lfitem"  >
                <tr class="lostfound lfitem"  >
                    <td class="lostfound lfitem" colspan="3">
                        <span class="itemname">Item #: </span>
                        <?PHP echo $item->id; ?><br/>
                        <span id="lfnamespan" >
                            <span class="itemname">Name: </span>
                            <?PHP echo $item->name; ?>
                        </span>
                        <span id="lfnameedit" style="display: none;" >
                            <input size="50" name="lfname" value="<?PHP echo $item->name; ?>" />
                        </span>
                        <br/>
                        <span class="itemname">Status: </span>
                        <?PHP echo $statuses[$item->status]; ?>
                    </td>
                    <td class="lostfound lfitem" colspan="3">
                        <a id="fart" onclick="lfEditItems();" >Click to edit.</a>
                    </td>

                </tr>
                <tr class="lostfound lfitem" >
                    <td class="lostfound lfitem" colspan="3"  >
                        <span id="itemdescription" class="itemdescription">
                            <?PHP echo $item->description; ?>
                        </span>
                        <span id="itemdescedit" style="display: none;" >
                            <textarea cols="50" rows="8" name="lfdescription" id="description" ><?PHP echo $item->description; ?></textarea>
                            <input type="submit" class="button" value="Submit" name="Submit" />
                        </span>

                    </td>
                    <td class="lostfound lfitem" colspan="3" >
                        <img src="<?PHP echo $lf_upload_dir . $item->picname; ?>" width="270" >
                    </td>
                </tr>
                <tr class="lostfound lfitem" >
                    <td class="lostfound lfitem" colspan="6" >
                        Submitted: <?PHP echo date($lf_date_format, strtotime($item->submitted)); ?>
                    </td>
                </tr>
                <tr class="lostfound lfitem" >
                    <td class="lostfound lfitem lfname" colspan="2" >
                        <b class="lfadmin lostfound" >
                            Name:<br/>
                        </b>
                        <?PHP echo $item->claim_name; ?>
                    </td>
                    <td class="lostfound lfitem lfname" colspan="2" >
                        <b class="lfadmin lostfound lfname" >
                            Email address:<br/>
                        </b>
                        <?PHP echo $item->claim_email; ?>
                    </td>
                    <td class="lostfound lfitem" colspan="2" >
                        <b class="lfadmin lostfound" >
                            Phone:<br/>
                        </b>
                        <?PHP echo $item->claim_phone; ?>
                    </td>
                </tr>
                <tr class="lostfound lfitem" >
                    <td class="lostfound lfitem lfclaims" colspan="3" >
                        <?PHP if (2 == $item->status): ?>
                            <a href="<?PHP echo $page_guid; ?>&action=admin&item_claim=<?PHP echo $item->id ?>"
                               class="lostfound lfadmin">
                                Click to mark this as claimed
                            </a>
                        <?PHP else: ?>
                            <a href="<?PHP echo $page_guid; ?>&action=admin&item_unclaim=<?PHP echo $item->id ?>"
                               class="lostfound lfadmin">
                                Click to mark this as unclaimed
                            </a>
                        <?PHP endif; ?>
                    </td>
                    <td class="lostfound lfitem lfclaims" colspan="3" >
                        <a href="<?PHP echo $page_guid; ?>&action=admin&item_clearinfo=<?PHP echo $item->id ?>"
                           class="lostfound lfadmin">
                            Click to clear the claim info
                        </a>
                    </td>
                </tr>
                <tr>
                    <td class="lostfound lfitem lfclaims" colspan="6" >
                        <?PHP if (1 == $item->status or 2 == $item->status): ?>
                            <a href="<?PHP echo $page_guid; ?>&action=admin&item_delete=<?PHP echo $item->id ?>"
                               class="lostfound lfadmin">
                                Click to delete this item.
                            </a>
                        <?PHP else: ?>
                            <a href="<?PHP echo $page_guid; ?>&action=admin&item_undelete=<?PHP echo $item->id ?>"
                               class="lostfound lfadmin">
                                Click to undelete this item.
                            </a>
                        <?PHP endif; ?>
                    </td>
                </tr>
            </table>
            <?PHP //end item choice  ?>
        <?PHP else: //show item list as default  ?>
            <?PHP if (false == $result): ?>
                There are no items to manage.
            <?PHP else: ?>
                <?PHP if ("" <> $hidden): ?>
                    <a href="<?PHP echo $page_guid; ?>&action=admin&show_hidden=1"
                       class="lostfound lfadmin" >
                        Click to show completed items.
                    </a>
                <?PHP else: ?>
                    <a href="<?PHP echo $page_guid; ?>&action=admin"
                       class="lostfound lfadmin" >
                        Click to hide completed items.
                    </a>
                <?PHP endif; ?>
                <table class="lostfound lfadmin" >
                    <tr class="lostfound lfadmin" >
                        <th class="lostfound lfadmin" >
                            ID#
                        </th>
                        <th class="lostfound lfadmin" >
                            Item name:
                        </th>
                        <th class="lostfound lfadmin" >
                            Item description:
                        </th>
                        <th class="lostfound lfadmin" >
                            Status:
                        </th>
                        <th class="lostfound lfadmin" >
                            Name
                        </th>
                        <th class="lostfound lfadmin" >
                            Email
                        </th>
                        <th class="lostfound lfadmin" >
                            Phone
                        </th>
                    </tr>
                    <?PHP foreach ($result as $item): ?>
                        <tr class="lostfound lfadmin" >
                            <td class="lostfound lfadmin" >
                                <a href="<?PHP echo $page_guid; ?>&action=admin&item_choice=<?PHP echo $item->id ?>"
                                   class="lostfound lfadmin">
                                       <?PHP echo $item->id; ?>
                                </a>
                            </td>
                            <td class="lostfound lfadmin lfitemname" >
                                <a href="<?PHP echo $page_guid; ?>&action=admin&item_choice=<?PHP echo $item->id ?>"
                                   class="lostfound lfadmin">
                                       <?PHP echo $item->name; ?>
                                </a>
                            </td>
                            <td class="lostfound lfadmin" >
                                <a href="<?PHP echo $page_guid; ?>&action=admin&item_choice=<?PHP echo $item->id ?>"
                                   class="lostfound lfadmin">
                                       <?PHP echo $item->description; ?>
                                </a>
                            </td>
                            <td class="lostfound lfadmin" >
                                <?PHP echo $statuses[$item->status]; ?>
                            </td>
                            <td class="lostfound lfadmin" >
                                <?PHP echo $item->claim_name; ?>
                            </td>
                            <td class="lostfound lfadmin" >
                                <?PHP echo $item->claim_email; ?>
                            </td>
                            <td class="lostfound lfadmin" >
                                <?PHP echo $item->claim_phone; ?>
                            </td>
                        </tr>
                    <?PHP endforeach; ?>
                </table>

            <?PHP endif; // no results  ?>

        <?PHP endif; //item list ?>

    </form>
</div><!-- lfadmin -->

<!-- END lost-found-admin-tpl.php -->
