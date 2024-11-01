<!-- lost-found-show-tpl.php -->

<div class="lfitemlist" >
    <h1 class="lostfound" >
        <?PHP echo $message; ?>
    </h1>
    <?PHP if (0 == count($results)): ?>
        <h2 class='lostfound'>There are no items.</h2>
    <?PHP else: ?>
        <?PHP foreach ($results as $item): ?>
            <table class="lostfound lfitem"  >
                <tr class="lostfound lfitem"  >
                    <td class="lostfound lfitem" >
                        <span class="itemname">Item #: <?PHP echo $item->id; ?><br/>
                            Name: <?PHP echo $item->name; ?></span>
                    </td>
                    <td class="lostfound lfitem" >
                        <a href="<?PHP echo $page_guid; ?>&action=claim&item=<?PHP echo $item->id ?>"
                           class="lostfound lfitem">
                            Click here to claim this item.
                        </a>
                    </td>
                </tr>
                <tr class="lostfound lfitem" >
                    <td class="lostfound lfitem"  >
                        <span class="itemdescription">
                            <?PHP echo $item->description; ?>
                        </span>
                    </td>
                    <td class="lostfound lfitem"  >
                        <img src="<?PHP echo $lf_upload_dir['baseurl'] . $item->picname; ?>" width="270" >
                    </td>
                </tr>
                <tr class="lostfound lfitem" >
                    <td class="lostfound lfitem" colspan="2" >
                        Submitted: <?PHP echo date($lf_date_format, strtotime($item->submitted)); ?>
                    </td>
                </tr>
            </table>
        <?PHP endforeach; ?>
    <?PHP endif; ?>

</div><!-- item list -->
<!-- comments page is too close -->
<div class="spacer" ></div>
<!-- end lost-found-show-tpl.php -->