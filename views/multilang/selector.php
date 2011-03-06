<ul class="multilang-selector">
	<?php foreach($languages as $code => $language): ?>
		<?php if($language['uri']): ?>
		<li class="multilang-selectable multilang-<?php echo $code; ?>">
			<?php echo HTML::anchor($language['uri'], $language['label'], array('title' => $language['label'])); ?>
		</li>
		<?php else: ?>
		<li class="multilang-selected multilang-<?php echo $code; ?>">
			<span><?php echo $language['label']; ?></span>
		</li>
		<?php endif; ?>
		<li cllass="clearer"></li>
	<?php endforeach; ?>
</ul>