jQuery(function ($) {
	'use strict';

	if ($.fn.wpColorPicker) {
		$('.sbca-color-picker').wpColorPicker();
	}

	$('.sbca-media-btn').on('click', function (e) {
		e.preventDefault();
		var button = $(this);
		var targetId = button.data('target');
		var frame = wp.media({
			title: 'Select Background Image',
			multiple: false,
			library: { type: 'image' },
		});

		frame.on('select', function () {
			var attachment = frame.state().get('selection').first().toJSON();
			$('#' + targetId).val(attachment.url);
		});

		frame.open();
	});
});

