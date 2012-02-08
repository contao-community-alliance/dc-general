<div id="widget_<?php echo $strName; ?>" class="widget <?php echo $strClass; ?>">
  <input type="hidden" value="<?php echo $strName; ?>" name="FORM_INPUTS[]" />
  <?php echo $objWidget->parse(); ?>
  <?php if($strDatepicker): ?>
  	<?php if(version_compare(VERSION, '2.10', '>=')): ?>
 	  <img src="plugins/datepicker/icon.gif" width="20" height="20" id="toggle_<?php echo $objWidget->id; ?>" style="vertical-align:-6px;">
  	<?php endif; ?>
    <script type="text/javascript"><!--//--><![CDATA[//><!--
      window.addEvent('domready', function() { <?php echo $strDatepicker; ?> });
    //--><!]]></script>
  <?php endif; ?>
  <?php if($strUpdate): ?>
  <h3 style="padding-top:7px"><label for="ctrl_<?php echo $strName; ?>_update"><?php echo $GLOBALS['TL_LANG']['MSC']['updateMode']; ?></label></h3>
  <div id="ctrl_<?php echo $strName; ?>_update" class="tl_radio_container">
    <input type="radio" name="<?php echo $strName; ?>_update" id="opt_<?php echo $strName; ?>_update_1" class="tl_radio" value="add" onfocus="Backend.getScrollOffset();" /> <label for="opt_<?php echo $strName; ?>_update_1"><?php echo $GLOBALS['TL_LANG']['MSC']['updateAdd']; ?></label><br />
    <input type="radio" name="<?php echo $strName; ?>_update" id="opt_<?php echo $strName; ?>_update_2" class="tl_radio" value="remove" onfocus="Backend.getScrollOffset();" /> <label for="opt_<?php echo $strName; ?>_update_2"><?php echo $GLOBALS['TL_LANG']['MSC']['updateRemove']; ?></label><br />
    <input type="radio" name="<?php echo $strName; ?>_update" id="opt_<?php echo $strName; ?>_update_0" class="tl_radio" value="replace" checked="checked" onfocus="Backend.getScrollOffset();" /> <label for="opt_<?php echo $strName; ?>_update_0"><?php echo $GLOBALS['TL_LANG']['MSC']['updateReplace']; ?></label>
  </div>
  <?php endif; if($GLOBALS['TL_CONFIG']['oldBeTheme'] || !$objWidget->hasErrors()) echo $this->help(); ?>
</div>