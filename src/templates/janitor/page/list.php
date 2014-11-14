<?php
global $action;
global $IC;
global $model;
global $itemtype;

$all_items = $IC->getItems(array("itemtype" => $itemtype, "order" => "status DESC, published_at DESC"));
?>
<div class="scene defaultList <?= $itemtype ?>List">
	<h1>Employee</h1>

	<ul class="actions">
		<?= $HTML->link("New employee", "/janitor/".$itemtype."/new", array("class" => "button primary key:n", "wrapper" => "li.new")) ?>
	</ul>

	<div class="all_items i:defaultList taggable filters sortable" 
		data-csrf-token="<?= session()->value("csrf") ?>"
		data-save-order="<?= $this->validPath("/janitor/$itemtype/updateOrder") ?>" 
		data-get-tags="<?= $this->validPath("/janitor/admin/items/tags") ?>" 
		data-delete-tag="<?= $this->validPath("/janitor/admin/items/tags/delete") ?>"
		data-add-tag="<?= $this->validPath("/janitor/admin/items/tags/add") ?>"
		>
<?		if($all_items): ?>
		<ul class="items">
<?			foreach($all_items as $item): 
				$item = $IC->extendItem($item, array("tags" => true));
				$media = isset($item["single_media"]) ? $item["single_media"] : false; ?>
			<li class="item draggable item_id:<?= $item["id"] ?> <?= $media ? (" image:".$media["format"]." variant:".$media["variant"]) : "" ?> width:160">
				<div class="drag"></div>
				<h3><?= $item["name"] ?></h3>
<?				if($item["tags"]): ?>
				<ul class="tags">
<?					foreach($item["tags"] as $tag): ?>
					<li><span class="context"><?= $tag["context"] ?></span>:<span class="value"><?= $tag["value"] ?></span></li>
<?					endforeach; ?>
				</ul>
<?				endif; ?>

				<ul class="actions">
					<?= $HTML->link("Edit", "/janitor/".$itemtype."/edit/".$item["id"], array("class" => "button", "wrapper" => "li.edit")) ?>
					<?= $HTML->deleteButton("Delete", "/janitor/admin/items/delete/".$item["id"], array("js" => true)) ?>
					<?= $HTML->statusButton("Enable", "Disable", "/janitor/admin/items/status", $item, array("js" => true)) ?>
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No content.</p>
<?		endif; ?>
	</div>

</div>
