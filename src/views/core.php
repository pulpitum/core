<div class="row-fluid">
  <div class="panel panel-default">
    <div class="panel-heading">
      <div class="title_wrapper">
        <h4 class="pull-left"><?php echo ucfirst( $data->getEntidadeTitle() );?></h4>
        <div class="ActionBtn_<?php echo $data->getModelName();?>">
          <?php echo Theme::partial('topMenus', array('action_menu' => $data->actionsListBtn()) ); ?>
        </div>
        <div class="clearfix"></div>
      </div>
    </div>
    <div class="panel-body" id="demo">
      <?php echo View::make('datatables::grid', array('data' => $data, 'UserCache' => 'Dev3gntw\Datatables\Models\UsersCache', 'list'=>true ))->render(); ?>
    </div>
  </div>
</div>