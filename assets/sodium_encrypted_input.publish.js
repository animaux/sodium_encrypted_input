jQuery(function() {
	jQuery('.field-sodium_encrypted_input.file').each(function() {		
		// masquerade as a file upload for styling purposes
		var field = jQuery(this);
		var frame = jQuery(this).find('.frame');
		var action = jQuery('<em>Change</em>').appendTo(frame);
		action.bind('click', function(e) {
			e.preventDefault();
			// clone the hidden fields
			var input = field.find('input').clone();
			field.find('input[type="hidden"]').remove();
			frame.remove();
			// append a new input field ready for new value
			field.removeClass('file').find('label').append(input.attr('type', 'text').val(''));
			// hide the nonce field
			field.find('input + input').hide();
		});
	});
});