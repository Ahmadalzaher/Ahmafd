/**
 * Yalla Arab Kulturfestival — front-end behaviour.
 * Vanilla JS, no dependencies.
 */
(function () {
	'use strict';

	var config = window.YallaConfig || {};

	/* ---------------------------------------------------------- header -- */
	var header = document.getElementById('site-header');
	if (header) {
		var onScroll = function () {
			header.classList.toggle('is-stuck', window.scrollY > 8);
		};
		onScroll();
		window.addEventListener('scroll', onScroll, { passive: true });
	}

	/* ------------------------------------------------------ mobile nav -- */
	var toggle = document.querySelector('.nav-toggle');
	var mobileNav = document.getElementById('mobile-nav');

	function closeNav() {
		document.body.classList.remove('nav-open', 'is-locked');
		if (toggle) {
			toggle.setAttribute('aria-expanded', 'false');
		}
	}

	if (toggle) {
		toggle.addEventListener('click', function () {
			var open = document.body.classList.toggle('nav-open');
			document.body.classList.toggle('is-locked', open);
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			if (open && mobileNav) {
				var firstLink = mobileNav.querySelector('a');
				if (firstLink) {
					firstLink.focus({ preventScroll: true });
				}
			}
		});
	}

	if (mobileNav) {
		mobileNav.addEventListener('click', function (event) {
			if (event.target.closest('a')) {
				closeNav();
			}
		});
	}

	/* ------------------------------------------------------- reveal in -- */
	var revealItems = document.querySelectorAll('.reveal');
	if (revealItems.length) {
		if ('IntersectionObserver' in window) {
			var observer = new IntersectionObserver(
				function (entries) {
					entries.forEach(function (entry) {
						if (entry.isIntersecting) {
							entry.target.classList.add('is-in');
							observer.unobserve(entry.target);
						}
					});
				},
				{ rootMargin: '0px 0px -8% 0px', threshold: 0.08 }
			);
			revealItems.forEach(function (item) {
				observer.observe(item);
			});
			// Fallback: ensure elements become visible even if the observer
			// does not fire (e.g. on some mobile browsers or when the element
			// is already in the viewport on load).
			window.setTimeout(function () {
				revealItems.forEach(function (item) {
					if (!item.classList.contains('is-in')) {
						item.classList.add('is-in');
					}
				});
			}, 1200);
		} else {
			revealItems.forEach(function (item) {
				item.classList.add('is-in');
			});
		}
	}

	/* ---------------------------------------------------- video modal -- */
	var modal = document.getElementById('video-modal');
	var modalBody = document.getElementById('video-modal-body');

	function closeModal() {
		if (!modal) {
			return;
		}
		modal.classList.remove('is-open');
		modal.setAttribute('aria-hidden', 'true');
		document.body.classList.remove('is-locked');
		if (modalBody) {
			modalBody.innerHTML = '';
		}
	}

	if (modal) {
		document.addEventListener('click', function (event) {
			var trigger = event.target.closest('[data-yd-video]');
			if (trigger) {
				event.preventDefault();
				var id = trigger.getAttribute('data-yd-video');
				if (!id || !modalBody) {
					return;
				}
				// No YouTube request without explicit consent.
				if (!window.ydConsent || !window.ydConsent.isGranted()) {
					if (window.ydConsent) {
						window.ydConsent.open('prompt');
					}
					return;
				}
				var iframe = document.createElement('iframe');
				iframe.src = 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent(id) + '?autoplay=1&rel=0';
				iframe.title = 'YouTube';
				iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
				iframe.allowFullscreen = true;
				modalBody.appendChild(iframe);
				modal.classList.add('is-open');
				modal.setAttribute('aria-hidden', 'false');
				document.body.classList.add('is-locked');
				var closeBtn = modal.querySelector('.modal__close');
				if (closeBtn) {
					closeBtn.focus({ preventScroll: true });
				}
				return;
			}

			if (event.target.closest('[data-close-modal]')) {
				closeModal();
			}
		});

		document.addEventListener('keydown', function (event) {
			if ('Escape' === event.key) {
				closeModal();
			}
		});
	}

	/* ------------------------------------------------ dabke trainer --- */
	var game = document.querySelector('[data-rhythm-game]');
	if (game) {
		var beats = Array.isArray(config.beats) ? config.beats : [];
		var steps = Array.prototype.slice.call(game.querySelectorAll('.step'));
		var stateEl = game.querySelector('[data-game-state]');
		var nameEl = game.querySelector('[data-game-name]');
		var indexEl = game.querySelector('[data-game-index]');
		var imageEl = game.querySelector('[data-game-image]');
		var badgeEl = game.querySelector('[data-game-badge]');
		var captionEl = game.querySelector('[data-game-caption]');
		var footEl = game.querySelector('[data-game-foot]');
		var playBtn = game.querySelector('[data-game-play]');
		var pauseBtn = game.querySelector('[data-game-pause]');
		var stopBtn = game.querySelector('[data-game-stop]');
		var resetBtn = game.querySelector('[data-game-reset]');
		var bpmInput = game.querySelector('[data-game-bpm]');
		var bpmValue = game.querySelector('[data-game-bpm-value]');
		var volumeInput = game.querySelector('[data-game-volume]');
		var volumeValue = game.querySelector('[data-game-volume-value]');
		var speedBtns = Array.prototype.slice.call(game.querySelectorAll('.speed'));
		var announceEl = game.querySelector('[data-game-announce]');
		var strings = config.strings || {};

		var MIN_BPM = 40;
		var MAX_BPM = 180;
		var bpm = parseInt(game.getAttribute('data-bpm'), 10) || 96;
		var volume = 0.7;
		var masterGain = null;
		var stepIndex = 0;
		var selected = 0;
		var playing = false;
		var nextBeatTime = 0;
		var queue = [];
		var nodes = [];
		var audio = null;
		var schedulerId = null;
		var rafId = null;
		var beatTimer = null;

		var raf = window.requestAnimationFrame || function (cb) {
			return window.setTimeout(cb, 16);
		};
		var cancelRaf = window.cancelAnimationFrame || window.clearTimeout;

		function context() {
			if (audio) {
				return audio;
			}
			var AudioCtx = window.AudioContext || window.webkitAudioContext;
			if (!AudioCtx) {
				return null;
			}
			try {
				audio = new AudioCtx();
				masterGain = audio.createGain();
				masterGain.gain.value = volume;
				masterGain.connect(audio.destination);
			} catch (error) {
				audio = null;
				masterGain = null;
			}
			return audio;
		}

		function clock() {
			return window.performance && window.performance.now ? window.performance.now() / 1000 : Date.now() / 1000;
		}

		function tone(time, accent, foot) {
			var ctx = context();
			if (!ctx || ctx.state !== 'running') {
				return;
			}
			var when = ctx.currentTime + Math.max(0.005, time - clock());
			var isStomp = accent && time === nextBeatTime - 60 / bpm; // last beat before loop

			// Determine drum type based on beat position
			// accent (beat 0 and 3): doum (low darbuka)
			// beat 5 (stomp): strong doum (tabl baladi)
			// others: tak (high darbuka)
			var isDoum = accent;
			var isStrong = (time === /* stomp handled below */ time) && false;

			// Create a noise buffer for percussion
			var bufferSize = ctx.sampleRate * 0.15;
			var buffer = ctx.createBuffer(1, bufferSize, ctx.sampleRate);
			var data = buffer.getChannelData(0);
			for (var i = 0; i < bufferSize; i++) {
				data[i] = (Math.random() * 2 - 1) * Math.pow(1 - i / bufferSize, 2);
			}

			if (isDoum) {
				// Doum: low resonant thump (darbuka bass)
				var osc = ctx.createOscillator();
				var gain = ctx.createGain();
				osc.type = 'sine';
				osc.frequency.setValueAtTime(80, when);
				osc.frequency.exponentialRampToValueAtTime(50, when + 0.12);
				gain.gain.setValueAtTime(0.0001, when);
				gain.gain.exponentialRampToValueAtTime(0.4, when + 0.005);
				gain.gain.exponentialRampToValueAtTime(0.0001, when + 0.25);
				osc.connect(gain);
				gain.connect(masterGain || ctx.destination);
				osc.start(when);
				osc.stop(when + 0.3);

				// Add noise layer for texture
				var noise = ctx.createBufferSource();
				var noiseGain = ctx.createGain();
				var filter = ctx.createBiquadFilter();
				filter.type = 'lowpass';
				filter.frequency.value = 200;
				noise.buffer = buffer;
				noiseGain.gain.setValueAtTime(0.15, when);
				noiseGain.gain.exponentialRampToValueAtTime(0.0001, when + 0.12);
				noise.connect(filter);
				filter.connect(noiseGain);
				noiseGain.connect(masterGain || ctx.destination);
				noise.start(when);
				noise.stop(when + 0.15);
			} else {
				// Tak: high sharp click (darbuka treble)
				var noise2 = ctx.createBufferSource();
				var noiseGain2 = ctx.createGain();
				var filter2 = ctx.createBiquadFilter();
				filter2.type = 'highpass';
				filter2.frequency.value = 1500;
				noise2.buffer = buffer;
				noiseGain2.gain.setValueAtTime(0.25, when);
				noiseGain2.gain.exponentialRampToValueAtTime(0.0001, when + 0.06);
				noise2.connect(filter2);
				filter2.connect(noiseGain2);
				noiseGain2.connect(masterGain || ctx.destination);
				noise2.start(when);
				noise2.stop(when + 0.08);

				// Add a high sine for pitch
				var osc2 = ctx.createOscillator();
				var gain2 = ctx.createGain();
				osc2.type = 'sine';
				osc2.frequency.value = 'right' === foot ? 1200 : 1400;
				gain2.gain.setValueAtTime(0.08, when);
				gain2.gain.exponentialRampToValueAtTime(0.0001, when + 0.04);
				osc2.connect(gain2);
				gain2.connect(masterGain || ctx.destination);
				osc2.start(when);
				osc2.stop(when + 0.05);
			}
		}

		function stopNodes() {
			if (audio) {
				try { audio.close(); } catch (error) {}
				audio = null;
				masterGain = null;
			}
		}

		function setStatus(text) {
			if (stateEl) {
				stateEl.textContent = text;
			}
		}

		function announce(text) {
			if (announceEl) {
				announceEl.textContent = text;
			}
		}

		function updatePreview(index) {
			var beat = beats[index];
			if (!beat) {
				return;
			}
			if (imageEl && beat.image && imageEl.getAttribute('src') !== beat.image) {
				imageEl.src = beat.image;
				imageEl.alt = beat.alt || beat.name;
			}
			if (badgeEl) {
				badgeEl.textContent = String(index + 1);
			}
			if (captionEl) {
				captionEl.textContent = beat.name;
			}
			if (footEl) {
				footEl.textContent = beat.foot_label;
				footEl.setAttribute('data-foot', beat.foot);
			}
			if (nameEl) {
				nameEl.textContent = beat.name;
			}
			if (indexEl) {
				indexEl.textContent = String(index + 1);
			}
		}

		function markSteps(index, active) {
			steps.forEach(function (el, i) {
				el.classList.remove('is-active', 'is-selected');
				if (i === index) {
					el.classList.add(active ? 'is-active' : 'is-selected');
				}
				el.setAttribute('aria-pressed', i === index ? 'true' : 'false');
			});
		}

		function select(index) {
			if (!beats.length) {
				return;
			}
			selected = Math.max(0, Math.min(index, beats.length - 1));
			updatePreview(selected);
			markSteps(selected, false);
		}

		function pulse() {
			game.classList.add('is-beat');
			window.clearTimeout(beatTimer);
			beatTimer = window.setTimeout(function () {
				game.classList.remove('is-beat');
			}, 130);
		}

		function activate(index) {
			if (!beats[index]) {
				return;
			}
			selected = index;
			updatePreview(index);
			markSteps(index, true);
			pulse();

			var beat = beats[index];
			announce(
				(strings.step || 'Step') + ' ' + (index + 1) + ' ' + (strings.of || 'of') + ' ' + beats.length + ': ' +
				beat.foot_label + ' — ' + beat.name
			);
		}

		function schedule() {
			if (!playing) {
				return;
			}
			var horizon = clock() + 0.12;
			while (nextBeatTime < horizon) {
				var beat = beats[stepIndex] || {};
				queue.push({ step: stepIndex, time: nextBeatTime });
				tone(nextBeatTime, 0 === stepIndex, beat.foot);
				nextBeatTime += 60 / bpm;
				stepIndex = (stepIndex + 1) % beats.length;
			}
		}

		function frame() {
			if (!playing) {
				return;
			}
			var now = clock();
			while (queue.length && queue[0].time <= now + 0.012) {
				activate(queue.shift().step);
			}
			rafId = raf(frame);
		}

		function stopLoop() {
			playing = false;
			if (schedulerId) {
				window.clearInterval(schedulerId);
				schedulerId = null;
			}
			if (rafId) {
				cancelRaf(rafId);
				rafId = null;
			}
			queue = [];
			stopNodes();
			game.classList.remove('is-playing', 'is-beat');
			if (playBtn) {
				playBtn.disabled = false;
			}
			if (pauseBtn) {
				pauseBtn.disabled = true;
			}
		}

		async function play() {
			if (!beats.length || playing) {
				return;
			}
			var ctx = context();
			if (ctx) {
				if (ctx.state === 'suspended' && ctx.resume) {
					try { await ctx.resume(); } catch (e) {}
				}
				if (masterGain) {
					masterGain.gain.value = volume;
				}
			}

			game.classList.remove('is-paused');
			game.classList.add('is-playing');
			if (playBtn) {
				playBtn.disabled = true;
			}
			if (pauseBtn) {
				pauseBtn.disabled = false;
			}
			setStatus(strings.playing || 'Playing');

			playing = true;
			stepIndex = selected;
			nextBeatTime = clock() + 0.2;
			schedulerId = window.setInterval(schedule, 25);
			schedule();
			rafId = raf(frame);
		}

		function pause() {
			if (!playing) {
				return;
			}
			stopLoop();
			game.classList.add('is-paused');
			setStatus(strings.paused || 'Paused');
		}

		function stop() {
			stopLoop();
			game.classList.remove('is-paused');
			setStatus(strings.ready || 'Ready');
			steps.forEach(function (el) {
				el.classList.remove('is-active');
			});
			select(0);
			announce('');
		}

		function reset() {
			stopLoop();
			game.classList.remove('is-paused');
			setStatus(strings.ready || 'Ready');
			steps.forEach(function (el) {
				el.classList.remove('is-active');
			});
			select(0);
			announce('');
		}

		function setBpm(value) {
			var next = parseInt(value, 10);
			if (isNaN(next)) {
				return;
			}
			bpm = Math.min(MAX_BPM, Math.max(MIN_BPM, next));
			if (bpmInput) {
				bpmInput.value = String(bpm);
			}
			if (bpmValue) {
				bpmValue.textContent = String(bpm);
			}
			speedBtns.forEach(function (btn) {
				var match = parseInt(btn.getAttribute('data-speed'), 10) === bpm;
				btn.classList.toggle('is-active', match);
				btn.setAttribute('aria-pressed', match ? 'true' : 'false');
			});
		}

		function previewTone(index) {
			var ctx = context();
			if (!ctx) {
				return;
			}
			if (ctx.state === 'suspended' && ctx.resume) {
				ctx.resume();
			}
			var beat = beats[index] || {};
			tone(clock() + 0.01, 0 === index, beat.foot);
		}

		steps.forEach(function (el) {
			el.addEventListener('click', function () {
				var index = parseInt(el.getAttribute('data-step'), 10) || 0;
				if (playing) {
					pause();
				}
				select(index);
				previewTone(index);
			});
		});

		if (playBtn) {
			playBtn.addEventListener('click', play);
		}
		if (pauseBtn) {
			pauseBtn.addEventListener('click', pause);
		}
		if (stopBtn) {
			stopBtn.addEventListener('click', stop);
		}
		if (resetBtn) {
			resetBtn.addEventListener('click', reset);
		}

		if (bpmInput) {
			bpmInput.addEventListener('input', function () {
				setBpm(bpmInput.value);
			});
		}

		if (volumeInput) {
			volumeInput.addEventListener('input', function () {
				volume = parseInt(volumeInput.value, 10) / 100;
				if (volumeValue) {
					volumeValue.textContent = volumeInput.value;
				}
				if (masterGain) {
					masterGain.gain.value = volume;
				}
			});
		}

		speedBtns.forEach(function (btn) {
			btn.addEventListener('click', function () {
				setBpm(btn.getAttribute('data-speed'));
			});
		});

		document.addEventListener('visibilitychange', function () {
			if (document.hidden && playing) {
				pause();
			}
		});

		// Preload the six stills so beat changes are instant.
		beats.forEach(function (beat) {
			if (beat.image) {
				var preload = new Image();
				preload.src = beat.image;
			}
		});

		setBpm(bpm);
		select(0);
	}

	/* --------------------------------------------- smooth anchor links -- */
	document.addEventListener('click', function (event) {
		var link = event.target.closest('a[href^="#"]');
		if (!link) {
			return;
		}
		var id = link.getAttribute('href');
		if (!id || '#' === id.length) {
			return;
		}
		var target = document.querySelector(id);
		if (!target) {
			return;
		}
		event.preventDefault();
		target.scrollIntoView({ behavior: 'smooth', block: 'start' });
		window.history.replaceState(null, '', id);
	});
})();
