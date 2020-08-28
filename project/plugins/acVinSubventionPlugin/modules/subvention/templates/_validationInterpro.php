
		<?php echo $form->renderGlobalErrors(); ?>
		<?php echo $form->renderHiddenFields(); ?>
		<?php foreach($form as $categorie => $items): ?>
			<?php if(!$items instanceof sfFormFieldSchema): continue; endif; ?>
			<div class="panel panel-default">
					<div class="panel-heading">
						<h3 class="panel-title"><strong><?php echo $subvention->approbations->get($categorie)->getLibelle() ?></strong></h3>
					</div>
					<div class="panel-body">
						<?php foreach($items as $key => $item): ?>
							<div class="form-group">
								 <?php echo $item->renderError(); ?>
								 <?php echo $item->renderLabel(null, array("class" => "col-sm-7 control-label", "style" => "font-size:12px")); ?>
								 <div class="<?php if(get_class($item->getWidget()) == "bsWidgetFormInput"): ?>col-sm-5<?php else: ?>col-sm-1<?php endif;?>">
											<?php $unite = $subvention->approbations->get($categorie)->getSchemaItem($key, "unite") ?>
											<?php if($unite): ?><div class="input-group"><?php endif ?>
											<?php echo $item->render(); ?>
											<?php if($unite): ?>
													<span class="input-group-addon"><?php echo $unite; ?></span>
													</div>
											<?php endif; ?>
								 </div>
								 <div class="<?php if(get_class($item->getWidget()) == "bsWidgetFormInput"): ?>col-sm-12 text-right<?php else: ?> col-sm-4<?php endif; ?>"  >
										 <?php echo $item->renderHelp(); ?>
								 </div>
							</div>
						<?php endforeach; ?>
					</div>
			</div>
		<?php endforeach; ?>
