@includeWhen(!empty($widget['wrapper']), 'backpack::widgets.inc.after_content_widgets')
<div class="{{ $widget['class'] ?? 'well mb-2' }}">
    Test
</div>
