(function() {
	var albumBucketName = 'upload.imcba';

	// Initialize the Amazon Cognito credentials provider
	AWS.config.region = 'us-east-1'; // Region
	AWS.config.credentials = new AWS.CognitoIdentityCredentials({
		IdentityPoolId: 'us-east-1:8481ec77-6e38-4af6-9658-aad56ec1fb13',
	});

	//alert(AWS.config.credentials.identityId);

	var s3 = new AWS.S3({
	  apiVersion: 'latest',
	  params: {Bucket: albumBucketName}
	});
	
  function WebRecorder() {
    var audioRecorder = {};
    var audioBlob; // this is empty if fallback
    var audioSource;
    var isModernSupported = false;
    var isFallback = false;
    var userMediaReady = false;
    var progressInterval;
    var playerInstance;
    var mediaDevices = false;
    var mediaAudioContext = false;
    var hooks = {};
    var settings = {};
    var self = this;

    // exposed functions
    this.version = '2.0.2';
    this.initialize = initialize;
    this.record = record;
    this.stop = stop;
    this.play = play;
    this.upload = upload;
    this.on = on;
    this.off = off;
    this.dispatch = dispatch;

    /**
     * Initialize
     */
    function initialize(_options) {
      settings = _options;

      // if forceFallback settings
      var forceFallback = ('forceFallback' in settings) ? settings.forceFallback : false;

      // Setup getUserMedia, with polyfill for older browsers
      // Adapted from: https://developer.mozilla.org/en-US/docs/Web/API/MediaDevices/getUserMedia
      if (!forceFallback) {
        mediaDevices = (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) ?
          navigator.mediaDevices : ((navigator.getUserMedia ||
            navigator.mozGetUserMedia || navigator.webkitGetUserMedia || navigator.msGetUserMedia) ? {
            getUserMedia: function(c) {
              return new Promise(function(y, n) {
                (navigator.getUserMedia ||
                  navigator.mozGetUserMedia ||
                  navigator.webkitGetUserMedia ||
                  navigator.msGetUserMedia).call(navigator, c, y, n);
              });
            }
          } : false);

        settings.debug && console.log("Media Devices", mediaDevices);
      }

      // by default it uses html audio api
      // if not suppoerted fallback to flash
      if (!forceFallback && mediaDevices && isMediaAudioContextAvailable()) {
        isModernSupported = true;
        settings.debug && console.log("mediaDevices and audioContext supported");
        // initUserMedia();
      } else {
        // fallback to flash
        if ('fallback' in settings && typeof settings.fallback === 'object') {
          if ('hasFlash' in settings && settings.hasFlash) {
            // init flash recorder
            Recorder.initialize(extend({
              onFlashSecurity: function() {
                isFallback = true;
                dispatch('media:ready', 'Media ready', isFallback);
                settings.debug && console.log("Media fallback to flash");
              }
            }, settings.fallback));
          } else {
            if (!forceFallback) {
              dispatch('media:error', 'WebRTCUnavailable::Browser does not appear to be WebRTC-capable.');
            } else {
              dispatch('media:error', 'No flash has been detected.');
            }
          }
        } else {
          dispatch('media:error', 'fallback property must be an object');
        }
      }
    }

    /**
     * Checks if the audio context is available
     */
    function isMediaAudioContextAvailable() {
      return !!getMediaAudioContext();
    }

    /**
     * Gets the media audio context
     */
    function getMediaAudioContext() {
      return (window.AudioContext || window.webkitAudioContext || window.mozAudioContext);
    }

    function initUserMedia(cb) {
      // use default html5 audio api
      mediaDevices.getUserMedia({
        "audio": true
      }).then(function(stream) {
        // fix problem on firefox
        // http://stackoverflow.com/questions/22860468/html5-microphone-capture-stops-after-5-seconds-in-firefox
        var mediaCtx = getMediaAudioContext();
        mediaAudioContext = new mediaCtx();
        settings.debug && console.log("Audio Context", mediaAudioContext);

        audioSource = mediaAudioContext.createMediaStreamSource(stream);

        // audio recorder object
        audioRecorder = new window.WebAudioRecorder(audioSource, {
          workerDir: 'js/WebAudioRecorder/',
          encoding:  'wav',
          numChannels: 1,     // number of channels
          options: {
            timeLimit: settings.timeLimit, // seconds
            encodeAfterRecord: settings.encodeAfterRecord,
            progressInterval: 1 * 1000 // milliseconds
          },
          onEncoderLoading: eventHelpers.encoderLoadingDispatch,
          onEncoderLoaded: eventHelpers.encoderLoadedDispatch,
          onEncodingProgress: eventHelpers.encodingProgressDispatch,
          onComplete: eventHelpers.completeDispatch,
          onTimeout: eventHelpers.timeoutDispatch,
          onError: eventHelpers.errorDispatch
        });

        settings.debug && console.log('Audio Recorder', audioRecorder);

        cb && cb();

        dispatch('media:ready', 'Media ready', isFallback);
      })['catch'](function(err) {
        // bracket notation of the "catch" for ie8 http://stackoverflow.com/a/23105306/1460756
        return dispatch('media:error', "Could not access microphone: " + err.name + ": " + err.message, err);
      });
    }

    /**
     * Make sure that usermedia is ready
     * before recording
     */
    function startRecording(cb) {
      initUserMedia(function() {
        audioRecorder.startRecording();
        cb && cb();
      });
    }

    /**
     * Record
     */
    function record(options) {
      var timeout = false;
      if (isFallback && !isModernSupported) {
        Recorder.record({
          start: options.start,
          progress: function(milliseconds) {
            options.progress(milliseconds);
            // stop if more than timelimit
            var maxRecordLength = settings.timeLimit * 1000;
            if (!timeout && milliseconds >= maxRecordLength) {
              stop();
              options.timeout();
              timeout = true;
            }
          }
        });
      } else {
        startRecording(function() {
          // start callback
          if ('start' in options) {
            options.start();
          }

          // progress callback
          if ('progress' in options) {
            // setProgress(0);
            progressInterval = window.setInterval(function progress() {
              var sec = audioRecorder.recordingTime() || 0;
              var milliseconds = sec * 1000;
              options.progress(milliseconds);

              // stop if more than timelimit
              var maxRecordLength = settings.timeLimit * 1000;
              if (!timeout && milliseconds >= maxRecordLength) {
                stop();
                options.timeout();
                timeout = true;
              }
            }, 500);
          }

          // timeout callback
          if ('timeout' in options) {
            on('record:timeout', function() {
              if (!timeout) {
                stop();
                options.timeout();
                timeout = true;

                // remove progress interval if set
                removeRecordProgressInterval();
              }
            });
          }
        });
      }
    }

    /**
     * Remove the progress interval
     * for non-fallback version
     */
    function removeRecordProgressInterval() {
      // remove interval
      if (progressInterval) {
        clearInterval(progressInterval);
      }
    }

    /**
     * Stop
     */
    function stop(isPlaying) {
      if (isFallback && !isModernSupported) {
        Recorder.stop();

        // only top the audio when playback
        if (isPlaying) {
          return;
        }

        setTimeout(function() {
          eventHelpers.completeDispatch(null, 'No Blob for flash fallback');
        }, 100);
      } else {
        // remove progress interval if set
        removeRecordProgressInterval();

        // if playing stop the playback
        if (isPlaying) {
          playerInstance.pause();
          playerInstance.currentTime = 0;
          return;
        }

        // stop recording
        audioRecorder.finishRecording();

        // stop audio context
        mediaAudioContext.close();
      }
    }

    /**
     * Play
     */
    function play(options) {
      if (isFallback && !isModernSupported) {
        Recorder.play(options);
      } else {
        playerInstance = new window.Audio();
        if ('progress' in options) {
          playerInstance.ontimeupdate = function() {
            options.progress(playerInstance.currentTime * 1000);
          };
        }

        if ('finished' in options) {
          playerInstance.onpause = function() {
            options.finished();
          };
        }

        playerInstance.src = window.URL.createObjectURL(audioBlob);
        playerInstance.play();
      }
    }

    /**
     * Upload
     */
    function upload(options) {
      if (isFallback && !isModernSupported) {
        if ('success' in options) {
          var oldsuccess = options.success;
          options.success = function(response) {
            settings.debug && console.log('Response', response);
            if (response) {
              oldsuccess($.parseJSON(response));
            } else {
              settings.debug && console.log('Unable to parse response json', response);
            }
          };
        }
        Recorder.upload(options);
      } else {
        delete options.audioParam;
        delete options.dataType;

        // append each params as formData
        var data = new FormData();
        for (var pkey in options.params) {
          data.append(pkey, options.params[pkey]);
        }
        //data.delete("action");
        //data.append("action", "adupa");
        //data.append("key", "${filename}");
        //data.append("acl", "public-read");
        
        // add file blob
        data.append('audioFile', audioBlob);

        // remove old params
        options.data = data;
        options.processData = false;
        options.contentType = false;
//        options.append("x-amz-server-side-encryption", "AES256");
        delete options.params;
        return $.ajax(options);
      }
    }

    /**
     * listen to dispatch events
     */
    function on(name, callback) {
      // set callback hook
      name = name.replace(/^on/i, '').toLowerCase();
      if (!hooks[name]) hooks[name] = [];
      hooks[name].push(callback);

      return self;
    }

    /**
     * stop listening to dispatch events
     */
    function off(name, callback) {
      // remove callback hook
      name = name.replace(/^on/i, '').toLowerCase();
      if (hooks[name]) {
        if (callback) {
          // remove one selected callback from list
          var idx = hooks[name].indexOf(callback);
          if (idx > -1) hooks[name].splice(idx, 1);
        } else {
          // no callback specified, so clear all
          hooks[name] = [];
        }
      }
    }

    /**
     * Dispatch an specific event
     */
    function dispatch() {
      // fire hook callback, passing optional value to it
      var name = arguments[0].replace(/^on/i, '').toLowerCase();
      var args = Array.prototype.slice.call(arguments, 1);

      if (hooks[name] && hooks[name].length) {
        for (var idx = 0, len = hooks[name].length; idx < len; idx++) {
          var hook = hooks[name][idx];

          if (typeof(hook) == 'function') {
            // callback is function reference, call directly
            hook.apply(this, args);
          } else if ((typeof(hook) == 'object') && (hook.length == 2)) {
            // callback is PHP-style object instance method
            hook[0][hook[1]].apply(hook[0], args);
          } else if (window[hook]) {
            // callback is global function name
            window[hook].apply(window, args);
          }
        } // loop
        return true;
      } else if (name == 'error') {
        // default error handler if no custom one specified
        alert("WebRecorder.js Error: " + args[0]);
      }

      return false; // no hook defined
    }

    /**
     * Extend function tool
     */
    function extend() {
      var a = arguments[0];
      for (var i = 1, len = arguments.length; i < len; i++) {
        var b = arguments[i];
        for (var prop in b) {
          a[prop] = b[prop];
        }
      }
      return a;
    }

    /**
     * Event helpers
     */
    var eventHelpers = {
      encoderLoadedDispatch: function() {
        dispatch('encoder:loaded', "Encoders loaded");
      },
      encoderLoadingDispatch: function(recorder, encoding) {
        dispatch('encoder:loading', "Encoders loading", encoding);
      },
      completeDispatch: function(recorder, blob) {
        audioBlob = blob;
        dispatch('record:completed', "Record Completed", blob);
      },
      encodingProgressDispatch: function(recorder, progress) {
        dispatch('encode:progress', "Encode Progress", progress);
      },
      timeoutDispatch: function() {
        dispatch('record:timeout', "Record timeout");
      },
      errorDispatch: function(recorder, errmessage) {
        dispatch('record:error', "Record Error", errmessage);
      }
    };
  }

  // shims
  window.URL = window.URL || window.webkitURL || window.mozURL || window.msURL;

  // the recorder
  window.WebRecorder = new WebRecorder();
})();
