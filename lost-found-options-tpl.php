<!-- lost-found-options-tpl.php -->
<?PHP
// $display_names[id] = display name
// $admin_names = formatted admin list with remove links via javascript 
// $email_names = formatted list of people receiving an email
?>
<script type="text/javascript" >
    var please_wait_image = "<IMG src=\"<?PHP echo admin_url(); ?>images/loading.gif\" >";
    var nonce = "<?PHP echo $nonce; ?>";
</script>
<script type="text/javascript" src="<?PHP echo plugin_dir_url(__FILE__); ?>lost-found.js" ></script>
<div class="lostfoundoptions" >
    <h2>Lost and found</h2>
    <h3>Options page</h3>

    <p>All configurable options are on this page.</p>

    <table class=" lostfound">
	<tr class="widefat lostfound">
	    <th class=" lostfound" style="width: 50%">
		Current lost and found admins
	    </th>
	    <th class=" lostfound" style="width: 50%">
		Lost and found email recipients
	    </th>
	</tr>
	<tr class="widefat lostfound">
	    <td class=" lostfound">
		<span id="lfadmins" >
		    <?PHP echo $admin_names; ?>
		</span>
	    </td>
	    <td class=" lostfound">
		<span id="lfemail_people">
		    <?PHP echo $email_names ?>
		</span>
	    </td>
	</tr>
	<tr class="widefat lostfound" >
	    <td class=" lostfound">
		Add user as admin: 
		<select id="lfselectadmins" name="lfadmins" onChange="add_user('admins')" >
		    <option value="0" > </option>
		    <?PHP
		    foreach ($display_names as $key => $name):
			echo "\t\t\t\t<option value='$key'>$name</option>\r\n";
		    endforeach;
		    ?>
		</select>
	    </td>
	    <td class=" lostfound">
		Add user to email list: 
		<select id="lfselectemail_people" name="lfemail_people" onChange="add_user('email_people')" >
		    <option value="0" > </option>
		    <?PHP
		    foreach ($display_names as $key => $name):
			echo "\t\t\t\t<option value='$key'>$name</option>\r\n";
		    endforeach;
		    ?>
		</select>
	    </td>
	</tr>
    </table>

</div><!-- lostfoundoptions -->
<!-- end lost-found-options-tpl.php -->