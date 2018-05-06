<?php
if($rs['table'])foreach ($rs['table'] as $key => $tval) {
  $tbn = $tval['table'];
?>
<div id="apps-add-<?php echo $key; ?>-field" class="app-table-list tab-pane">
  <?php if(iDB::check_table($tbn,false)){ ?>
  <table class="table table-hover table-bordered">
    <thead>
      <tr>
        <th style="width:100px;">字段</th>
        <th>数据类型</th>
        <th>长度</th>
        <th>主键</th>
        <th>非空</th>
        <th>Unsigned</th>
        <th>自增</th>
        <th>核对</th>
        <th>注释</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $orig_fields  = apps_db::fields($tbn);
      foreach ((array)$orig_fields as $field => $value) {
      ?>
      <tr>
        <td field="<?php echo $field; ?>"><b><?php echo $field; ?></b></td>
        <td><?php echo $value['type']; ?></td>
        <td><?php echo $value['length']; ?></td>
        <td><?php if($value['primary']){?>
          <font color="green"><i class="fa fa-check"></i></font>
        <?php }?></td>
        <td><?php echo $value['null']?'NULL':'NOT NULL'; ?></td>
        <td><?php echo strtoupper($value['unsigned']); ?></td>
        <td><?php if($value['auto_increment']){?>
          <font color="green"><i class="fa fa-check"></i></font>
        <?php }?></td>
        <td><?php echo $value['collation']; ?></td>
        <td><?php echo $value['comment']; ?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
  <div class="clearfloat mb10"></div>
  <div class="span4">
    <table class="table table-bordered bordered">
      <thead>
        <tr>
          <th colspan="2">索引</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $indexes  = apps_db::indexes($tbn);
        foreach ((array)$indexes as $ikey => $ivalue) {
        ?>
        <tr>
          <td><b><?php echo $ivalue['type']; ?></b></td>
          <td><?php echo implode(',', (array)$ivalue['columns']); ?></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
  <div class="span4 hide table_status">
    <table class="table table-bordered bordered">
      <thead>
        <tr>
          <th colspan="2">表信息</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $table_status  = apps_db::table_status($tbn);
        foreach ((array)$table_status as $tskey => $tsvalue) {
        ?>
        <tr>
          <td><b><?php echo $tskey; ?></b></td>
          <td><?php echo $tsvalue; ?></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
<?php
  }else{
    echo '<div class="alert alert-error">'.$tbn.' 表不存在</div>';
  }
?>
</div>
<?php }else{?>
<?php }?>
