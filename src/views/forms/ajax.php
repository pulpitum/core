<?php
	$tabs = $entidade->getTabs();
	$type = $model->exists ? "update" : "create";
	$rules = $model->exists ? $update_rules : $create_rules;
	$javascript = '';
	$first = key($tabs);
?>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                 <h4 class="modal-title"><?php echo $model->getEntidadeTitle();?></h4>
            </div>
            <div class="modal-body">
            	<div class="msg"></div>
					<?php
						$tkey = key($tabs);
						$canEdit = $entidade->getActionOption("edit", "permission");				
						$class= "";
							echo '<div class="'.$class.'" id="'.$tkey.'">';
								echo Former::horizontal_open()->secure()->rules($rules)->id("ajax_form");
								Former::populate($model);
								$html_odd = '';
								$html_even = '';
								$i = 1;
								$location = $tkey;
								$hiddenField = '';

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
													if($field['showInForm']==1){
														if($type == "create" && ( isset($field['hideInCreate']) && $field['hideInCreate']==1) ){
															$show = false;
														}elseif ($type == "update" && ( isset($field['hideInUpdate']) && $field['hideInUpdate']==1) ) {
															$show = false;
														}
													}else{
														$show = false;
													}
													if($show && $field['section'] == $key){
														
														$html .= '<div class="form-group">';
															$html .= '<div class="col-sm-12">';

																$disable = '';
																if(!Sentry::getUser()->hasAccess($canEdit))
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

																			$class = '';
																			$disabled = '';
																			$child = '';
																			$placeholder= 'Selecione uma opção';
																			if(method_exists($temp_model, "getChild")){
																				$class .= 'child ';
																				//$javascript .= "var ".$field['field'].'_linked ='.$temp_model->getChild().';'."\n";
																				$disabled = 'disabled';
																				$placeholder = 'Selecione uma opção acima';
																				$hiddenField.= Former::hidden($field['field'].'_linked', $temp_model->getChild())->id($field['field'].'_linked');
																			}

																			if(method_exists($temp_model, "isParent")){
																				$class .= 'parent ';
																				$child = $temp_model->isParent();
																			}																			

																			$options = $temp_model->getOptions($field);
																			if(count($options)>1){
																				$options = array(0=>$placeholder) + $options;
																			}

																			$html .= Former::select($field['field'])->id('edit_'.$field['field'])->addClass("form-select ".$class)->options($options, $model->$field['field'])->disabled($disabled)->label($field['label'])->data_child($child);

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
																		break;
																		/*$source = array();
																		if($field['source'] != '')
																			$source = new $field['source'];
																		$html .= Former::label($field['label']);
																		$html .= View::make($field['view'], array("data" => $model->$field['field'], "old_input"=>Input::old($field['field']), 'source' => $source, 'model'=>$model ))->render();
																		break;*/
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
								}
							?>
							<div class="row">
								<div class="col-md-12"><?php echo $html;?></div>
								<div class="clear"></div>
							</div>
							<?php
								echo  Former::hidden("return", $return);
								echo  Former::close();
							?>
						<?php echo '</div>'; 

						echo $hiddenField;
						?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo trans("core::all.close");?></button>
                <?php echo Former::button_submit(trans('lactiweb::form.save') )->addClass("submit_button"); ?>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
	<script type="text/javascript">
		jQuery(document).ready(function() {
			<?php echo $javascript;?>
		});
	</script>

	<div class="ajax_loader">
	    <div class="box">
	      <div class="clock"></div>
	    </div>
	</div>    
</div>
