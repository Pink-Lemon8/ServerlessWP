jQuery(($) => {
	$('a.show-alternatives').on('click', (ev) => {
		ev.preventDefault();
		if (ev.target.innerHTML === 'Show Alternatives') {
			$(`#${ev.target.id}-alternatives`).slideDown();
			$(ev.target).text('Hide Alternatives');
		} else {
			$(`#${ev.target.id}-alternatives`).slideUp();
			$(ev.target).text('Show Alternatives');
		}
	});
});
