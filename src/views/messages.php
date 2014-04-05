<?php if(Session::has('success')){ ?>
	<div class="alert alert-success alert-dismissable">
	  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
	  <?php echo Session::get('success');?>
	</div>
<?php } ?>

<?php if(Session::has('warning')){ ?>
	<div class="alert alert-warning alert-dismissable">
	  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
	  <?php echo Session::get('warning');?>
	</div>
<?php } ?>

<?php if(Session::has('danger')){ ?>
	<div class="alert alert-danger alert-dismissable">
	  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
	  <?php echo Session::get('danger');?>
	</div>
<?php } ?>

<?php if(Session::has('info')){ ?>
	<div class="alert alert-info alert-dismissable">
	  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
	  <?php echo Session::get('info');?>
	</div>
<?php } ?>