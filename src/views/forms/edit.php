<div class="row-fluid">
	<h2> <?php echo $model->getEntidadeTitle();?> </h2>
</div>
<div class="row-fluid">
	<?php
		$tabs = $entidade->getTabs();
		$type = $model->exists ? "update" : "create";
		$rules = $model->exists ? $update_rules : $create_rules;
		$primaryKey = $entidade->getPrimaryKey();
		$primaryKeyValue = $model->$primaryKey;
		$javascript = '';
		$first = key($tabs);
		if(count($tabs)>1) { ?>
			<div class="tabs_wrapper">
			<!-- Nav tabs -->
			<ul class="nav nav-tabs">
				<?php 
					$tabs = $entidade->getTabs();
					echo Dev3gntw\Core\Models\Helpers\Tools::renderTab($tabs, $type);
					$tabs = Dev3gntw\Core\Models\Helpers\Tools::reorderTab($tabs);
				?>
			</ul>
	<?php  } ?>  	
    <div class="panel panel-default">
      <div class="panel-heading">
			<!-- Tab panes -->
			<div class="tab-content">
				<?php
					foreach ($tabs as $tkey => $tab) {
						if($type == "create" && (isset($tab['hideInCreate']) && $tab['hideInCreate']) ){
							continue;
						}elseif ($type == "update" && (isset($tab['hideInUpdate']) && $tab['hideInUpdate']) ) {
							continue;
						}
						if($tab['type']=="parent" && isset($tab['childrens']) ){
							continue;
						}
						$canEdit = $entidade->getActionOption("edit", "permission");				
						$class= "";
						if($tkey == $first) $class= "in active";				

								echo '<div class="tab-pane fade '.$class.'" id="'.$tkey.'">';

										if($tab['type']=="section"){
											echo Former::horizontal_open()->secure()->rules($rules);
											Former::populate($model);
											$html_odd = '';
											$html_even = '';
											$i = 1;
											$location = isset($tab['model']) ? $tab['model'] : $tkey;
											echo '<div class="row">
												<div class="col-md-12">
													<div class="title_wrapper">
											        	<h4 class="pull-left">'.ucfirst($tab['label']).'</h4>
														<div class="ActionBtn_'.$location.'">';
															if($tkey == $first)	echo Theme::partial('topMenus', array('action_menu' => $model->actionsEditBtn()) );
															if($tab['type']=="section") echo '<div class="btn-group pull-right">'.Former::actions(Former::button_submit(trans('lactiweb::form.save') )->addClass("submit_button")->disabled("disabled") ).'</div>';
											echo '		</div>
														<div class="clear"></div>
													</div>		        
											    </div>
											</div>';
											foreach($entidade->getSections() as $key=>$section) {
												$html = '';
												if($section['tab'] != $tkey) continue;
												if($type == "create" && (isset($section['hideInCreate']) && $section['hideInCreate']) ){
													continue;
												}elseif ($type == "update" && (isset($section['hideInUpdate']) && $section['hideInUpdate']) ) {
													continue;
												}
												$html .= '<fieldset>';
													$html .= '<div class="fieldset_wrapper">';
														$html .= Former::legend(trans($section['label']));
															foreach ($entidade->getColumnsList() as $attribute => $field) {
																
																$show = true;
																if($field['showInForm']){
																	if($type == "create" && ( isset($field['hideInCreate']) && $field['hideInCreate']) ){
																		$show = false;
																	}elseif ($type == "update" && ( isset($field['hideInUpdate']) && $field['hideInUpdate']) ) {
																		$show = false;
																	}
																}else{
																	$show = false;
																}
																
																if($show && $field['section'] == $key){
																	
																	$html .= '<div class="form-group">';
																		$html .= '<div class="col-sm-12">';
																			$disable = '';
																			if(!$field['editable'] or !Sentry::getUser()->hasAccess($canEdit))
																				$field['input'] = 'disabled';
																			switch ($field['input']) {
																				case 'text':
																					$html .= Former::text($field['field'])->addClass("form-control")->id($field['field'])->label($field['label']);
																					
																					if($field['mask'] != ''){
																						$javascript .= 'jQuery("#'.$field['field'].'").mask("'.$field['mask'].'", {maxlength: false});'."\n";
																					}
																					break;
																				case 'textarea':
																					$html .= Former::textarea($field['field'])->addClass("form-control")->id($field['field'])->label($field['label'])->rows(10);
																					break;
																				case 'password':
																					$html .= Former::password($field['field'])->addClass("form-control")->label($field['label']); $pass = true;
																					break;
																				case 'checkbox':
																					$former_field = Former::checkboxes($field['field'])->addClass("form-checkbox")->data_text_label($field['label'])->raw();
																					if($field['options']!=""){
																						foreach ($field['options'] as $o_key => $o_value) {
																							$former_field->$o_key($o_value);
																						}
																					}
																					$html .= $former_field;
																					break;
																                case 'select':
																                  	$temp_model = class_exists($field['source']) ? new $field['source'] : new stdClass;
																                  	if(method_exists($temp_model, 'getOptions')){
																						$options = $temp_model->getOptions($field);
																						$html .= Former::select($field['field'])->addClass("form-select")->options($options, $model->$field['field'])->label($field['label']);
																              		}else{
																              			$html .= Former::text($field['field'])->addClass("form-control")->label($field['label']);
																              		}
																                  break;
																				case 'date':
																					$model->$field['field'] = date('Y-m-d', strtotime($model->$field['field']));
																					$html .= Former::text($field['field'])->addClass("form-control")->id($field['field'])->label($field['label']);
																			        if($field['mask'] != ''){
																			        	$javascript .= 'jQuery("#'.$field['field'].'").mask("'.$field['mask'].'", {maxlength: false});'."\n";
																			        }
																					break;
																				case 'datehour':
																					$model->$field['field'] = date('Y-m-d H:i:s', strtotime($model->$field['field']));
																					$html .= Former::text($field['field'])->addClass("form-control")->id($field['field'])->label($field['label']);
																			        if($field['mask'] != ''){
																			        	$javascript .= 'jQuery("#'.$field['field'].'").mask("'.$field['mask'].'", {maxlength: false});'."\n";
																			        }
																					break;												
																				case 'partial':
																					$source = array();
																					if($field['source'] != '')
																						$source = new $field['source'];
																					$html .= Former::label($field['label']);
																					$html .= View::make($field['view'], array("data" => $model->$field['field'], "old_input"=>Input::old($field['field']), 'source' => $source, 'model'=>$model ))->render();
																					break;
																				case 'disabled':
																					//Apply Source Fields
																					if($field['source'] != ''){
																						if (strpos($field['source'],'Values') !== false) {
																						    $source = new $field['source'];
																						    $model->$field['field'] = $source->getValue($model->$field['field']);
																						}
																					}
																					$html .= Former::text($field['field'])->addClass("form-control")->label($field['label'])->disabled('disabled');
																					break;												
																			}
																			$disable = '';
																		$html .= '</div>';
															  		$html .= '</div>';

															  		if(isset($pass)){
															  			$html .= '<div class="form-group">';
																  			$html .= '<div class="col-sm-12">';
																				$html .= Former::password($field['field']."_confirmation")->addClass("form-control")->label(trans('auth::form.confirm_password'));
																			$html .= '</div>';
																		$html .= '</div>';
																		unset($pass);
															  		}
																}
															}
													$html .= "</div>";
												$html .= '</fieldset>';

												if($i % 2 == 0)
													$html_even .= $html;
												else
													$html_odd .= $html;

												$i++;												
											}
										?>
										<div class="row">
											<div class="col-md-6"><?php echo $html_odd;?></div>
											<div class="col-md-6"><?php echo $html_even;?></div>
											<div class="clear"></div>
										</div>
										<?php
											echo  Former::hidden("return", $return);
											echo  Former::close();
										?>

									<?php 
									}elseif($tab['type']=="remote"){
										$mm =  Config::get('core::masterModels');
										if(array_key_exists($tab['model'], $mm)){
											$_data = new $mm[$tab['model']];
											$filters = array();
											foreach ($tab['filters'] as $filter => $alias) {
												$filters[$alias] = $model->$filter;
											}
											$location = isset($tab['model']) ? $tab['model'] : $tkey;
											echo '<div class="row">
												<div class="col-md-12">
													<div class="title_wrapper">
											        	<h4 class="pull-left">'.ucfirst($tab['label']).'</h4>
														<div class="ActionBtn_'.$location.'">';
															echo Theme::partial('topMenus', array('action_menu' => $_data->actionsRemoteBtn()) );
													echo '</div>
														<div class="clear"></div>
													</div>		        
											    </div>
											</div>';
											echo View::make($tab['view'], array('data' => $_data, 'parentId' => $primaryKeyValue, 'parentEntidade' => $entidade, 'filters' => $filters, 'UserCache' => 'Dev3gntw\Datatables\Models\UsersCache' ))->render();
										}else{
											echo trans("Não foi possivel carregar os dados em questão.");
										}
									}
						echo '</div>';
					}
				?>
			</div>
		</div>
	</div>
	<?php if(count($tabs)>1) { ?>
		</div>
	<?php } ?>
</div>

<script type="text/javascript">
	jQuery(document).ready(function() {
		<?php echo $javascript;?>
	    if (jQuery(".nav-tabs").length > 0) {
	        var activeTab, lastTab;
	        activeTab = jQuery("[href=" + location.hash + "]");
	        activeTab && activeTab.tab("show");
	        $("a[data-toggle=\"tab\"]").on("shown.bs.tab", function(e) {
	            return localStorage.setItem("lastTab", $(this).attr('href'));
	        });
	        lastTab = localStorage.getItem("lastTab");
	        if (lastTab) {
	            var tab = jQuery("a[href=\"" + lastTab + "\"]");
	            tab.click();
	            if (!jQuery(tab).hasClass('loaded') && jQuery(tab).is('[data-run]')) {
	                var run = jQuery(tab).attr("data-run");
	                jQuery(tab).addClass("loaded");
	                eval(run + '()');
	            }
	        }
	    }		
	});
</script>