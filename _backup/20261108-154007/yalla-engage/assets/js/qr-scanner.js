/**
 * Yalla Engage — QR code scanner for the door check-in page.
 *
 * Decodes QR codes from the device camera entirely offline in the browser.
 * The bundled jsQR decoder (assets/js/jsqr.js) does the decoding; this file
 * only handles camera access, frame grabbing and result hand-off.
 *
 * @package Yalla_Engage
 */

/* global window, document, navigator, jsQR */

(function () {
	'use strict';

	var video       = null;
	var canvas      = document.createElement('canvas');
	var ctx         = canvas.getContext('2d', { willReadFrequently: true });
	var stream      = null;
	var running     = false;
	var lastResult  = '';
	var lastTime    = 0;
	var onResult    = null;
	var rafId       = null;

	/**
	 * Decode an ImageData object with the bundled jsQR decoder.
	 *
	 * @param {ImageData} imageData Frame to decode.
	 * @return {string|null} Decoded text or null.
	 */
	function decode( imageData ) {
		if ( typeof jsQR !== 'function' ) {
			return null;
		}

		var code = jsQR( imageData.data, imageData.width, imageData.height, {
			inversionAttempts: 'dontInvert'
		} );

		return code && code.data ? code.data : null;
	}

	/**
	 * Repeatedly grab frames from the video element and decode them.
	 */
	function tick() {
		if ( ! running || ! video ) {
			return;
		}

		if ( video.readyState === video.HAVE_ENOUGH_DATA ) {
			canvas.width  = video.videoWidth;
			canvas.height = video.videoHeight;

			if ( canvas.width > 0 && canvas.height > 0 ) {
				ctx.drawImage( video, 0, 0, canvas.width, canvas.height );

				var imageData = ctx.getImageData( 0, 0, canvas.width, canvas.height );
				var text      = decode( imageData );

				if ( text ) {
					var now = Date.now();
					if ( text !== lastResult || ( now - lastTime ) > 2500 ) {
						lastResult = text;
						lastTime   = now;
						if ( typeof onResult === 'function' ) {
							onResult( text );
						}
					}
				}
			}
		}

		rafId = window.requestAnimationFrame( tick );
	}

	var Scanner = {
		/**
		 * Whether the browser can scan at all.
		 *
		 * @return {boolean}
		 */
		isSupported: function () {
			return !!( navigator.mediaDevices && navigator.mediaDevices.getUserMedia );
		},

		/**
		 * Start scanning.
		 *
		 * @param {HTMLVideoElement} videoElement Target video element.
		 * @param {Function}         callback     Called with the decoded text.
		 * @return {Promise}
		 */
		start: function ( videoElement, callback ) {
			if ( ! Scanner.isSupported() ) {
				return Promise.reject( new Error( 'unsupported' ) );
			}
			if ( typeof jsQR !== 'function' ) {
				return Promise.reject( new Error( 'decoder-missing' ) );
			}

			video     = videoElement;
			onResult  = callback;
			lastResult = '';
			lastTime  = 0;

			return navigator.mediaDevices.getUserMedia( {
				video: { facingMode: { ideal: 'environment' } },
				audio: false
			} ).then( function ( mediaStream ) {
				stream = mediaStream;
				video.setAttribute( 'playsinline', 'true' );
				video.setAttribute( 'muted', 'true' );
				video.srcObject = mediaStream;

				return video.play().then( function () {
					running = true;
					rafId   = window.requestAnimationFrame( tick );
					return true;
				} );
			} );
		},

		/**
		 * Stop scanning and release the camera.
		 */
		stop: function () {
			running = false;

			if ( rafId ) {
				window.cancelAnimationFrame( rafId );
				rafId = null;
			}

			if ( stream ) {
				stream.getTracks().forEach( function ( track ) {
					track.stop();
				} );
				stream = null;
			}

			if ( video ) {
				video.srcObject = null;
			}
		}
	};

	window.YenaScanner = Scanner;
	window.yenaQRScan = decode;
})();
