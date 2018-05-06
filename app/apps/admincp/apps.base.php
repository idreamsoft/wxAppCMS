<div id="field-default">
  <div id="field_id">
    <div class="input-prepend input-append">
      <span class="add-on">id</span>
      <span class="input-xlarge uneditable-input">INT(10) UNSIGNED NOT NULL</span>
      <span class="add-on" style="width:auto">主键 自增ID</span>
    </div>
  </div>
  <div class="clearfloat mb10"></div>
  <?php if($base_fields)foreach ((array)$base_fields[1] as $key => $value) { ?>
  <div id="field_<?php echo $value; ?>">
    <div class="input-prepend input-append">
      <span class="add-on"><?php echo $value; ?></span>
      <span class="input-xlarge uneditable-input"><?php echo $base_fields[2][$key]; ?></span>
      <span class="add-on" style="width:auto"><?php echo $base_fields[4][$key]; ?></span>
    </div>
  </div>
  <div class="clearfloat mb10"></div>
  <?php } ?>
</div>

