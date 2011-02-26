<div id="tl_buttons">
<a href="<?php echo $this->getReferer(true); ?>" class="header_back" title="<?php echo specialchars($GLOBALS['TL_LANG']['MSC']['backBT']); ?>" accesskey="b" onclick="Backend.getScrollOffset();"><?php echo $GLOBALS['TL_LANG']['MSC']['backBT']; ?></a>
</div>

<h2 class="sub_headline_all"><?php echo $this->subHeadline; ?></h2>
<?php echo $this->getMessages(); ?>

<form class="tl_form tableextended" method="post"
  action="<?php echo $this->action; ?>"
  id="<?php echo $this->tableEsc; ?>"
  enctype="<?php echo $this->enctype; ?>"
  <?php if($this->onsubmit): ?> onsubmit="<?php echo $this->onsubmit; ?>"<?php endif; ?>>
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="<?php echo $this->tableEsc; ?>" />
<?php if($this->error): ?>
  <p class="tl_error"><?php echo $GLOBALS['TL_LANG']['ERR']['general']; ?></p>
  <script type="text/javascript">
  <!--//--><![CDATA[//><!--
	window.addEvent('domready', function() {
	    Backend.vScrollTo(($('<?php echo $this->table; ?>').getElement('label.error').getPosition().y - 20));
	});
  //--><!]]>
  </script>
<?php endif; ?>

<?php $strClass = 'tl_tbox'; if($this->oldBE): foreach($this->rootPalettes as $arrRootPalette): ?>
<div class="<?php echo $strClass; ?> block">
  <?php foreach($arrRootPalette['palette'] as $arrFieldset): echo $arrFieldset['palette']; endforeach; ?>
</div>
<?php $strClass = 'tl_box'; endforeach; else: foreach($this->rootPalettes as $arrRootPalette): ?>
<fieldset class="<?php echo $strClass ?> block">
<legend><?php echo $arrRootPalette['title']; ?></legend>
  <?php foreach($arrRootPalette['palette'] as $arrFieldset): ?><div class="clr"><?php echo $arrFieldset['palette']; ?></div><?php endforeach; ?>
</fieldset>
<?php $strClass = 'tl_box'; endforeach; endif; ?>
</div>

<div class="tl_formbody_submit">
<div class="tl_submit_container">
<input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="<?php echo specialchars($GLOBALS['TL_LANG']['MSC']['save']); ?>" />
<input type="submit" name="saveNclose" id="saveNclose" class="tl_submit" accesskey="c" value="<?php echo specialchars($GLOBALS['TL_LANG']['MSC']['saveNclose']); ?>" />
</div>
</div>

</form>