/**
 * Yalla Engage — AJAX nonce refresh for WP Rocket compatibility.
 */
jQuery(function($) {
	function refreshYenaNonces() {
		$.post(yena_ajax.ajax_url, {
			action: 'yena_refresh_nonce',
			yena_ajax_nonce: yena_ajax.nonce
		}, function(response) {
			if (response.success) {
				var data = response.data;
				$('input[name="yena_news_nonce"]').val(data.newsletter_nonce);
				$('input[name="yena_reg_nonce"]').val(data.registration_nonce);
			}
		});
	}

	// Refresh nonces every 10 minutes to avoid cached nonce issues with WP Rocket.
	setInterval(refreshYenaNonces, 10 * 60 * 1000);

	// Also refresh on page load.
	refreshYenaNonces();
});
