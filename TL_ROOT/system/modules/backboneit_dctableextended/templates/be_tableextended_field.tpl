<div id="widget_<?php echo $strName; ?>" class="widget <?php echo $strClass; ?>">
  <input type="hidden" value="<?php echo $strName; ?>" name="FORM_INPUTS[]" />
  <?php echo $objWidget->parse(); if($strDatepicker): ?>
  <script type="text/javascript"><!--//--><![CDATA[//><!--
    window.addEvent('domready', function() { <?php echo $strDatepicker; ?> });
  //--><!]]></script>
  <?php endif; if($strUpdate): ?>
  <h3 style="padding-top:7px"><label for="ctrl_<?php echo $strName; ?>_update"><?php echo $GLOBALS['TL_LANG']['MSC']['updateMode']; ?></label></h3>
  <div id="ctrl_<?php echo $strName; ?>_update" class="tl_radio_container">
    <input type="radio" name="<?php echo $strName; ?>_update" id="opt_<?php echo $strName; ?>_update_1" class="tl_radio" value="add" onfocus="Backend.getScrollOffset();" /> <label for="opt_<?php echo $strName; ?>_update_1"><?php echo $GLOBALS['TL_LANG']['MSC']['updateAdd']; ?></label><br />
    <input type="radio" name="<?php echo $strName; ?>_update" id="opt_<?php echo $strName; ?>_update_2" class="tl_radio" value="remove" onfocus="Backend.getScrollOffset();" /> <label for="opt_<?php echo $strName; ?>_update_2"><?php echo $GLOBALS['TL_LANG']['MSC']['updateRemove']; ?></label><br />
    <input type="radio" name="<?php echo $strName; ?>_update" id="opt_<?php echo $strName; ?>_update_0" class="tl_radio" value="replace" checked="checked" onfocus="Backend.getScrollOffset();" /> <label for="opt_<?php echo $strName; ?>_update_0"><?php echo $GLOBALS['TL_LANG']['MSC']['updateReplace']; ?></label>
  </div>
  <?php endif; if($GLOBALS['TL_CONFIG']['oldBeTheme'] || !$objWidget->hasErrors()) echo $this->help(); ?>
</div>