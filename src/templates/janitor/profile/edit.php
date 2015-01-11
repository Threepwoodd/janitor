<?php
global $action;
global $model;

// get current user
$item = $model->getUser();

// get languages for select
$language_options = $model->toOptions($this->languages(), "id", "name");

// get addresses
$addresses = $item["addresses"];

// get newsletters
$all_newsletters = $model->getNewsletters();
$user_newsletters = $item["newsletters"];

?>
<div class="scene defaultEdit userEdit profileEdit">
	<h1>User profile</h1>

	<ul class="views">
		<?= $HTML->link("Profile", "/janitor/admin/profile", array("wrapper" => "li.profile.selected")) ?>
<?		if(defined("SITE_SHOP") && SITE_SHOP): ?>
		<?= $HTML->link("Content and orders", "/janitor/admin/profile/content", array("wrapper" => "li.content")) ?>
<?		else: ?>
		<?= $HTML->link("Content", "/janitor/admin/profile/content", array("wrapper" => "li.content")) ?>
<?		endif; ?>
	</ul>

	<div class="item i:editProfile">
		<h2>Name and default language</h2>
		<?= $model->formStart("update", array("class" => "labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("nickname", array("value" => $item["nickname"])) ?>
				<?= $model->input("firstname", array("value" => $item["firstname"])) ?>
				<?= $model->input("lastname", array("value" => $item["lastname"])) ?>
				<?= $model->input("language", array("type" => "select", "value" => $item["language"], "options" => $language_options)) ?>
			</fieldset>

			<ul class="actions">
				<?= $model->submit("Update", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>

	<div class="usernames i:usernamesProfile">
		<h2>Email and Mobile number</h2>
		<p>Your email and mobile number are your unique usernames and can be used for login.</p> 

		<?= $model->formStart("updateEmail", array("class" => "email labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("email", array("value" => $item["email"])) ?>
			</fieldset>
			<ul class="actions">
				<?= $model->submit("Update email", array("class" => "primary", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>

		<?= $model->formStart("updateMobile", array("class" => "mobile labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("mobile", array("value" => $item["mobile"])) ?>
			</fieldset>
			<ul class="actions">
				<?= $model->submit("Update mobile", array("class" => "primary", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>

	</div>

	<div class="password i:passwordProfile">
		<h2>Password</h2>
		<div class="password_state set">
			<p class="password_set">Your password is encrypted and cannot be shown here. <a>Change password</a></p>
		</div>
		<div class="new_password">
			<p>Type your new password:</p>

			<?= $model->formStart("/janitor/admin/profile/setPassword") ?>
				<fieldset>
					<?= $model->input("password") ?>
				</fieldset>
				<ul class="actions">
					<?= $model->submit("Update password", array("class" => "primary", "wrapper" => "li.save")) ?>
				</ul>
			<?= $model->formEnd() ?>
		</div>
	</div>

	<div class="addresses">
		<h2>Addresses</h2>
<?		if($addresses): ?>
		<ul class="addresses">
<?			foreach($addresses as $address): ?>
			<li>
				<h3 class="address_label"><?= $address["address_label"] ?></h3>
				<div class="address_name"><?= $address["address_name"] ?></div>
				<?= $address["att"] ? ('<div class="att">Att: ' . $address["att"] . '</div>') : '' ?>
				<div class="address1"><?= $address["address1"] ?></div>
				<?= $address["address2"] ? ('<div class="address2">' . $address["address2"] . '</div>') : '' ?>
				<div class="postal_city">
					<span class="postal"><?= $address["postal"] ?></span>
					<span class="city"><?= $address["city"] ?></span>
				</div>
				<?= $address["state"] ? ('<div class="state">' . $address["state"] . '</div>') : '' ?>
				<div class="country"><?= $address["country_name"] ?></div>

				<ul class="actions">
					<?= $model->link("Edit", "/janitor/admin/profile/edit_address/".$address["id"], array("class" => "button", "wrapper" => "li.edit")) ?>
				</ul>
			</li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>You don't have any addresses associated with your account</p>
<?		endif; ?>

		<ul class="actions">
			<?= $model->link("Add new address", "/janitor/admin/profile/new_address", array("class" => "button primary", "wrapper" => "li.add")) ?>
		</ul>
	</div>

	<div class="newsletters i:newslettersProfile">
		<h2>Newsletters</h2>

<?		if($all_newsletters): ?>
		<ul class="newsletters">
<?			foreach($all_newsletters as $newsletter): ?>
			<li class="<?= arrayKeyValue($user_newsletters, "newsletter", $newsletter["newsletter"]) !== false ? "subscribed" : "" ?>">
				<ul class="actions">
					<?= $JML->deleteButton("Unsubscribe", "/janitor/admin/profile/updateNewsletter/".urlencode($newsletter["newsletter"])."/0") ?>
					<li class="subscribe">
					<?= $model->formStart("/janitor/admin/profile/updateNewsletter/".urlencode($newsletter["newsletter"])."/1") ?>
						<?= $model->submit("Subscribe", array("class" => "primary")) ?>
					<?= $model->formEnd() ?>
					</li>
				</ul>
				<h3><?= $newsletter["newsletter"] ?></h3>
			</li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>You don't have any newsletter subscriptions for your account</p>
<?		endif; ?>
	</div>

</div>