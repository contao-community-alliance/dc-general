<?php if($this->versions): ?>
<div class="tl_version_panel">
<form action="<?php echo ampersand($this->Environment->request, true); ?>" id="tl_version" class="tl_form" method="post">
<div class="tl_formbody">
<input type="hidden" name="FORM_SUBMIT" value="tl_version" />
<input type="hidden" name="REQUEST_TOKEN" value="<?php echo REQUEST_TOKEN; ?>" />
<select name="version" class="tl_select">
<?php foreach($this->versions as $arrVersion): ?>
	<option value="<?php echo $arrVersion['version']; ?>"<?php if($arrVersion['active']): ?> selected="selected"<?php endif; ?>>
	<?php echo $GLOBALS['TL_LANG']['MSC']['version']; ?> <?php echo $arrVersion['version']; ?> (<?php echo $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $arrVersion['tstamp']); ?>) <?php echo $arrVersion['username']; ?>
	</option>
<?php endforeach; ?>
</select> <input type="submit" name="showVersion" id="showVersion" class="tl_submit" value="<?php echo specialchars($GLOBALS['TL_LANG']['MSC']['restore']); ?>" />
</div>
</form>
</div>
<?php endif; ?>
<div id="tl_buttons">
<a href="<?php echo $this->getReferer(true); ?>" class="header_back" title="<?php echo specialchars($GLOBALS['TL_LANG']['MSC']['backBT']); ?>" accesskey="b" onclick="Backend.getScrollOffset();"><?php echo $GLOBALS['TL_LANG']['MSC']['backBT']; ?></a>
</div>

<h2 class="sub_headline"><?php echo $this->subHeadline; ?></h2>
<?php echo $this->getMessages(); ?>

<form class="tl_form tableextended" method="post"
  action="<?php echo ampersand($this->Environment->request, true); ?>"
  id="<?php echo $this->table; ?>"
  enctype="<?php echo $this->enctype; ?>"
  <?php if($this->onsubmit): ?> onsubmit="<?php echo $this->onsubmit; ?>"<?php endif; ?>>
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="<?php echo specialchars($this->table); ?>" />
<input type="hidden" name="REQUEST_TOKEN" value="<?php echo REQUEST_TOKEN; ?>" />
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

<?php if($this->oldBE): foreach($this->fieldsets as $arrFieldset): ?>
<div class="<?php echo $arrFieldset['class']; ?> block">
  <?php echo $arrFieldset['palette']; ?>
</div>
<?php endforeach; else: foreach($this->fieldsets as $arrFieldset): if($arrFieldset['legend']): ?>
<fieldset id="pal_<?php echo specialchars($arrFieldset['legend']); ?>" class="<?php echo $arrFieldset['class']; ?> block">
<legend><?php echo $arrFieldset['label']; ?></legend>
  <?php echo $arrFieldset['palette']; ?>
</fieldset>
<?php else: ?>
<fieldset class="<?php echo $arrFieldset['class']; ?> block nolegend">
  <?php echo $arrFieldset['palette']; ?>
</fieldset>
<?php endif; endforeach; endif; ?>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
<?php foreach($this->buttons as $strButton => $strLabel): ?>
<input type="submit" name="<?php echo $strButton; ?>"
	id="<?php echo $strButton; ?>" class="tl_submit" accesskey="s"
	value="<?php echo $strLabel ?>" /> 
<?php endforeach; ?>
</div>

</div>
</form>
<script>
window.addEvent('domready', function() {
  $$(<?php echo json_encode($this->table); ?> + ' input[type="text"]').focus();
});
</script>