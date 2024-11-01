<!-- lost-found-header-tpl.php -->
<div class="lfheaderbar" >
    <div class="lfnavwrapper" >
        <div class="lfnav" >
            <h2 class="nav-tab-wrapper" >
                <a href="<?PHP echo $page_guid; ?>&action=show" class="nav-tab <?PHP if (isset($navtab)) echo $navtab['show']; ?>">
                    View item list
                </a>
            </h2>
        </div>

        <?PHP if ($admin or $oktoshow): ?>
            <div class="lfnav" >
                <h2 class="nav-tab-wrapper" >

    <?PHP if (true == force_ssl_admin() and false == is_ssl()): // need an ssl link  ?>
                        <a href="<?PHP echo str_replace("http", "https", $page_guid); ?>&action=new"
                           class="nav-tab <?PHP if (isset($navtab)) echo $navtab['new']; ?>">
                            Add new item
                        </a>
    <?PHP else: ?>
                        <a href="<?PHP echo $page_guid; ?>&action=new" class="nav-tab <?PHP if (isset($navtab)) echo $navtab['new']; ?>">
                            Add new item
                        </a>
    <?PHP endif; //ssl link  ?>
                </h2>
            </div>
<?PHP endif; ?>

        <?PHP if ($admin): ?>
            <div class="lfnav" >
                <h2 class="nav-tab-wrapper" >
                    <a href="<?PHP echo $page_guid; ?>&action=admin" class="nav-tab <?PHP if (isset($navtab)) echo $navtab['admin']; ?>">
                        Edit items/<br/>Process claims
                    </a>
                </h2>
            </div>
<?PHP endif; ?>




    </div><!-- lfnavwrapper -->
    <div class="lfpages" >
<?PHP if (true == $navigator): ?>
            <div class="lfnavarrows" >
                <a href="<?PHP echo $page_guid; ?>&page_number=first&page_size=<?PHP echo $link_page_size; ?>" >
                    <div class='lfdblleft lfnavarrow' id='projnav'></div>
                    <div class='lfleft lfnavarrow' id='projnav'>First</div>
                </a>
                <a href="<?PHP echo $page_guid; ?>&page_number=<?PHP echo $less_page ?>&page_size=<?PHP echo $link_page_size; ?>" >
                    <div class='lfleft lfnavarrow' id='projnav'>Previous</div>
                </a>
                <div class="lfpageinfo" style="<?PHP echo $nav_style; ?>"><?PHP echo $pages; ?></div>

                <a href="<?PHP echo $page_guid; ?>&page_number=<?PHP echo $more_page ?>&page_size=<?PHP echo $link_page_size; ?>" >
                    <div class='lfright lfnavarrow' id='projnav'>Next</div>
                </a>



                <a href="<?PHP echo $page_guid; ?>&page_number=last&page_size=<?PHP echo $link_page_size; ?>" >
                    <div class='lfdblright lfnavarrow' id='projnav'></div><div class='lfright lfnavarrow' id='projnav'>Last</div>
                </a>
            </div>
            <div class="itemsperpage" >
    <?PHP if ($page_size <> 5): ?>
                    <a href="<?PHP echo $page_guid; ?>&page_number=<?PHP echo $page_number ?>&page_size=5" >
                        (5)
                    </a>
    <?PHP else: ?>
                    (5)
                <?PHP endif; ?>
                <?PHP if ($page_size <> 10 and $item_count > 10): ?>
                    <a href="<?PHP echo $page_guid; ?>&page_number=<?PHP echo $page_number ?>&page_size=10" >
                        (10)
                    </a>
    <?PHP else: ?>
                    (10)
                <?PHP endif; ?>
                <?PHP if ($page_size <> 25 and $item_count > 25): ?>
                    <a href="<?PHP echo $page_guid; ?>&page_number=<?PHP echo $page_number ?>&page_size=25" >
                        (25)
                    </a>
    <?PHP else: ?>
                    (25)
                <?PHP endif; ?>
                <?PHP if ($link_page_size <> "ALL"): ?>
                    <a href="<?PHP echo $page_guid; ?>&page_number=<?PHP echo $page_number ?>&page_size=ALL" >
                        (All)
                    </a>
    <?PHP else: ?>
                    (All)
                <?PHP endif; ?>
                items per page.
            <?PHP endif; ?>
        </div>
    </div><!-- lfpages -->
    <br style="clear:left;">
</div><!-- lfheaderbar -->

<!-- END lost-found-header-tpl.php -->