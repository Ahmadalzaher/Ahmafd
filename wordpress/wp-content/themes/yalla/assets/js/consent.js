/**
 * Yalla — YouTube consent gate.
 *
 * Stores the decision in localStorage, injects the player only after
 * consent and removes it again when consent is withdrawn.
 */
(function () {
	'use strict';

	var KEY = 'yd_consent_youtube';
	var PROMPT_SEEN = 'yd_consent_prompt_seen';
	var modal = document.getElementById('yt-consent');

	var state = {
		lastFocus: null,
		mode: 'prompt'
	};

	/**
	 * Focus after the layout caused by opening/closing a dialog has settled.
	 * Calling focus() synchronously right after DOM/style writes forces a
	 * synchronous layout (Chrome traces it as Layout under open/close).
	 */
	function focusSoon(element) {
		if (!element) {
			return;
		}
		window.setTimeout(function () {
			if (element.isConnected) {
				element.focus({ preventScroll: true });
			}
		}, 0);
	}

	/* ------------------------------------------------------------ store -- */
	function read() {
		try {
			return window.localStorage.getItem(KEY);
		} catch (e) {
			return null;
		}
	}

	function write(value) {
		try {
			if (value) {
				window.localStorage.setItem(KEY, value);
			} else {
				window.localStorage.removeItem(KEY);
			}
		} catch (e) {
			/* Private mode: the choice simply does not persist. */
		}
	}

	function isGranted() {
		return 'granted' === read();
	}

	function isDenied() {
		return 'denied' === read();
	}

	/* ---------------------------------------------------------- players -- */
	function hydrate(scope, autoplay) {
		var items = (scope || document).querySelectorAll('[data-yd-embed]');

		Array.prototype.forEach.call(items, function (item) {
			var id = item.getAttribute('data-yd-embed');
			var frame = item.querySelector('.yt-embed__frame');

			if (!id || !frame || item.classList.contains('is-loaded')) {
				return;
			}

			var iframe = document.createElement('iframe');
			iframe.src = 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent(id) + '?rel=0' + (autoplay ? '&autoplay=1' : '');
			iframe.title = item.getAttribute('data-yd-title') || 'YouTube';
			iframe.loading = 'lazy';
			iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
			iframe.allowFullscreen = true;
			iframe.setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');

			frame.appendChild(iframe);
			item.classList.add('is-loaded');
		});
	}

	function removePlayers() {
		Array.prototype.forEach.call(document.querySelectorAll('[data-yd-embed]'), function (item) {
			var iframe = item.querySelector('iframe');

			if (iframe) {
				iframe.parentNode.removeChild(iframe);
			}
			item.classList.remove('is-loaded');
		});

		// A player opened in the theme's video modal must go as well.
		var videoModal = document.getElementById('video-modal');
		var videoBody = document.getElementById('video-modal-body');

		if (videoBody) {
			videoBody.innerHTML = '';
		}
		if (videoModal) {
			videoModal.classList.remove('is-open');
			videoModal.setAttribute('aria-hidden', 'true');
			document.body.classList.remove('is-locked');
		}
	}

	function refresh() {
		var granted = isGranted();

		document.documentElement.classList.toggle('yt-allowed', granted);
		document.documentElement.classList.toggle('yt-blocked', isDenied());

		if (granted) {
			hydrate(document);
		} else {
			removePlayers();
		}

		Array.prototype.forEach.call(document.querySelectorAll('[data-yd-embed]'), function (item) {
			var text = item.querySelector('[data-yd-embed-text]');

			if (!text) {
				return;
			}
			text.textContent = isDenied()
				? item.getAttribute('data-yd-denied') || text.textContent
				: item.getAttribute('data-yd-note') || text.textContent;
		});
	}

	/* ------------------------------------------------------------ modal -- */
	function focusables() {
		if (!modal) {
			return [];
		}
		return Array.prototype.filter.call(
			modal.querySelectorAll('a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])'),
			function (el) {
				return null !== el.offsetParent;
			}
		);
	}

	function statusText() {
		if (isGranted()) {
			return 'Aktuelle Auswahl: YouTube erlaubt.';
		}
		if (isDenied()) {
			return 'Aktuelle Auswahl: YouTube nicht erlaubt.';
		}
		return 'Aktuelle Auswahl: noch keine Entscheidung.';
	}

	function open(mode) {
		if (!modal) {
			return;
		}

		state.mode = 'settings' === mode ? 'settings' : 'prompt';
		state.lastFocus = document.activeElement;

		modal.hidden = false;
		modal.classList.add('is-open');
		modal.setAttribute('data-mode', state.mode);
		document.documentElement.classList.add('ytc-open');

		var status = modal.querySelector('[data-ytc-status]');

		if (status) {
			if ('settings' === state.mode) {
				status.textContent = statusText();
				status.hidden = false;
			} else {
				status.hidden = true;
				status.textContent = '';
			}
		}

		var card = modal.querySelector('.ytc__card');

		if (card) {
			focusSoon(card);
		}

		document.addEventListener('keydown', onKeydown, true);
	}

	function close() {
		if (!modal || !modal.classList.contains('is-open')) {
			return;
		}

		modal.classList.remove('is-open');
		modal.hidden = true;
		document.documentElement.classList.remove('ytc-open');
		document.removeEventListener('keydown', onKeydown, true);

		var restore = state.lastFocus;

		if (restore && restore !== document.body && document.contains(restore) && restore.focus) {
			focusSoon(restore);
		} else if (modal.contains(document.activeElement) && document.activeElement.blur) {
			// Keep the focus outside a hidden dialog.
			document.activeElement.blur();
		}
		state.lastFocus = null;
	}

	function dismiss() {
		try {
			window.sessionStorage.setItem(PROMPT_SEEN, '1');
		} catch (e) {
			/* ignore */
		}
		close();
	}

	function choose(value) {
		write(value);
		refresh();
		close();

		if ('function' === typeof window.dispatchEvent) {
			document.dispatchEvent(
				new CustomEvent('yd:consent', { detail: { youtube: value } })
			);
		}
	}

	function onKeydown(event) {
		if ('Escape' === event.key) {
			event.preventDefault();
			dismiss();
			return;
		}

		if ('Tab' !== event.key) {
			return;
		}

		var list = focusables();

		if (!list.length) {
			return;
		}

		var first = list[0];
		var last = list[list.length - 1];

		if (event.shiftKey && document.activeElement === first) {
			event.preventDefault();
			last.focus();
		} else if (!event.shiftKey && document.activeElement === last) {
			event.preventDefault();
			first.focus();
		}
	}

	if (modal) {
		var accept = modal.querySelector('[data-ytc-accept]');
		var deny = modal.querySelector('[data-ytc-deny]');
		var settings = modal.querySelector('[data-ytc-settings]');

		if (accept) {
			accept.addEventListener('click', function () {
				choose('granted');
			});
		}
		if (deny) {
			deny.addEventListener('click', function () {
				choose('denied');
			});
		}
		if (settings) {
			settings.addEventListener('click', function (event) {
				event.preventDefault();
				open('settings');
			});
		}

		modal.addEventListener('click', function (event) {
			if (event.target.closest('[data-ytc-dismiss]')) {
				dismiss();
			}
		});
	}

	/* ----------------------------------------------------------- events -- */
	document.addEventListener('click', function (event) {
		var opener = event.target.closest('[data-yd-consent-open]');

		if (opener) {
			event.preventDefault();
			open('settings');
			return;
		}

		var play = event.target.closest('[data-yd-embed-play]');

		if (play) {
			event.preventDefault();

			if (isGranted()) {
				hydrate(play.closest('[data-yd-embed]'), true);
			} else {
				open('prompt');
			}
		}
	});

	window.addEventListener('storage', function (event) {
		if (event.key === KEY) {
			refresh();
		}
	});

	/* ------------------------------------------------------------- init -- */
	refresh();

	if (!read() && !document.querySelector('.ytc.is-open')) {
		var dismissed = false;

		try {
			dismissed = '1' === window.sessionStorage.getItem(PROMPT_SEEN);
		} catch (e) {
			dismissed = false;
		}

		// Do not stack two consent dialogs if another banner is installed.
		var foreignBanner =
			document.getElementById('cookie-law-info-bar') ||
			document.querySelector('.cmplz-cookiebanner, #borlabs-cookie, .cc-window');
		if (!dismissed && !foreignBanner) {
			open('prompt');
		}
	}

	window.ydConsent = {
		get: read,
		isGranted: isGranted,
		isDenied: isDenied,
		open: open,
		close: close,
		refresh: refresh
	};
})();
