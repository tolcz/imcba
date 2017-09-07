$(function() {

  function VoiceRecorder(formData) {

    // initialize default values
    var voiceData = false;
    var savingInProgress = false;
    var formID = (formData && 'formID' in formData) ? formData.formID : false;
    var onWizard = (formData && 'onWizard' in formData) ? formData.onWizard : false;
    var uploadSuccess = false;
    var recordingInProgress = false;
    var sendData = {};
    var audioBeepInstance = {};
    var debug = false;

    // recorder initialization
    var maxRecordtime = 600; // 10min by default
    var recordLength = 0;
    var params = JFCustomWidget.getWidgetSettings();

    // expose functions
    this.init = init;
    this.hasFlash = hasFlash;
    this.sendSubmitData = sendSubmitData;

    /**
     * Check if the browser is flashplayer compatible
     */
    function hasFlash() {
      return false;
    }

    /**
     * Initialize widget
     */
    function init() {
      debug && console.log('hasFlash', hasFlash());

      // resize the widget
      resizeWidget(250, 34);

      // disable record button
      $('#record').attr('disabled', false);

      // make sure maxtime setting is an +ve integer
      if ('maxTime' in params) {
        // max recording time set in widget (in seconds)
        maxRecordtime = Number(params.maxTime);
        $('#maxtime').html(' / ' + timecode(maxRecordtime * 1000)).show();
      }

      WebRecorder.on('record:completed', function(message, blob) {
        // upload audio when record completed
        debug && console.log("Got audio blob", blob, message);
        uploadVoiceData();
      }).on('record:timeout', function() {
        debug && console.log('Record timeout');
      }).on('record:error', function(message, errmessage) {
        debug && console.log(message, errmessage);
      }).on('media:error', function(message) {
        debug && console.log(message);
        showError(message);
      }).on('media:ready', function(message, isFallback) {
        debug && console.log(message, 'Fallback?', isFallback);
      }).initialize({
        debug: debug,
        timeLimit: maxRecordtime,
        encodeAfterRecord: true,
        forceFallback: false,
        fallback: {
          hasFlash: hasFlash(),
          swfSrc: 'js/flashRecorder/recorder.swf?v=' + (+new Date()),
          flashContainer: document.getElementById('flash-container')
        }
      });

      // initialize audiojs if beepNotif is set
      if (isBeepEnabled()) {
        audiojs.events.ready(function() {
          audioBeepInstance = audiojs.createAll();
          debug && console.log("audio instance ready", audioBeepInstance);
        });
      }

      // dont add event listener on form builder
      if (!onWizard) {
        addEventListeners();
      }
    }

    /**
     * A readable timecode converter
     */
    function timecode(ms) {
      var hms = {
        h: Math.floor(ms / (60 * 60 * 1000)),
        m: Math.floor((ms / 60000) % 60),
        s: Math.floor((ms / 1000) % 60)
      };

      var tc = [];

      if (hms.h > 0) {
        tc.push(hms.h);
      }

      tc.push((hms.m < 10 && hms.h > 0 ? "0" + hms.m : hms.m));
      tc.push((hms.s < 10 ? "0" + hms.s : hms.s));

      return tc.join(':');
    }

    /**
     * Resize the widget frame
     */
    function resizeWidget(width, height) {
      JFCustomWidget.requestFrameResize({
        height: height,
        width: width
      });
    }

    /**
     * Add event listeners
     */
    function addEventListeners() {
      // when record button clicked
      $('#record').click(record);

      // when the play button clicked
      $('#play').click(play);

      // when stop button clicked
      $('#stop').click(function() {
        WebRecorder.stop(true);
        recordingInProgress = false;
      });
    }

    /**
     * Record a voicedata
     */
    function record() {
      var el = this;

      // reset vars before recording again
      voiceData = false;
      savingInProgress = false;
      recordingInProgress = false;

      if (!$(el).hasClass('recording')) {
        // reset data if record clicked again
        JFCustomWidget.sendData({
          value: ''
        });

        // remove class and style of flash container
        $('#flash-container').removeAttr('class').removeAttr('style');

        // resize widget
        resizeWidget(250, 190);

        // start recording session
        WebRecorder.record({
          start: function() {
            resizeWidget(250, 54);
            showStatus('Recording in progress...');
            $(el).children('span').text('Stop');
            $(el).children('i').removeClass('fa-microphone').addClass('fa-microphone-slash');
            $('#play').attr('disabled', true);
            $(el).addClass('recording');
            // hide the flash container
            $('#flash-container').addClass('force-hide');
          },
          progress: function(milliseconds) {
            recordingInProgress = true;
            $('#time').text(timecode(milliseconds));
            recordLength = milliseconds;
          },
          timeout: function() {
            // lets save at end of max length
            debug && console.log('Time out called');

            // play a sound after recording, only if set
            if (isBeepEnabled()) {
              if (typeof audioBeepInstance[0] !== 'undefined') {
                audioBeepInstance[0].play();
              }
            }

            // WebRecorder.stop();
            resizeWidget(250, 54);
            voiceData = true;
            recordingInProgress = false;

            $(el).removeClass('recording');
            $(el).children('span').text('Record');
            $(el).children('i').removeClass('fa-microphone-slash').addClass('fa-microphone');

            $('#play').attr('disabled', false);

            // upload Voicedata
            // uploadVoiceData();

            /* WebRecorder.stop() ;
            recordingInProgress = false;
            $(el).removeClass('recording');
            $(el).children('span').text('Record');
            $(el).children('i').removeClass('fa-microphone-slash').addClass('fa-microphone');
            $('#play').attr('disabled', false); */
          }
        });
      } else {
        WebRecorder.stop();
        resizeWidget(250, 54);
        voiceData = true;
        recordingInProgress = false;

        $(el).removeClass('recording');
        $(el).children('span').text('Record');
        $(el).children('i').removeClass('fa-microphone-slash').addClass('fa-microphone');

        $('#play').attr('disabled', false);
        showSavingMsg();

        // upload Voicedata
        // uploadVoiceData();
      }
    }

    /**
     * Play the recordeddata
     */
    function play() {
      WebRecorder.play({
        progress: function(milliseconds) {
          $('#time').text(timecode(milliseconds));
        },
        finished: function() {
          $('#record').attr('disabled', false);
          $('#time').text(timecode(recordLength));

          if (!$('#play').is(':visible')) {
            $('#stop').hide();
            $('#play').show();
          }
        }
      });

      // hide play button and show stop button
      $('#play').hide();
      $('#stop').show();
    }

    /**
     * Will create a CORS request for ajax
     */
    function createCORSRequest(method, url) {
      var xhr = new XMLHttpRequest();

      if (xhr.withCredentials !== null) {
        xhr.open(method, url, true);
      } else if (typeof XDomainRequest !== "undefined") {
        xhr = new XDomainRequest();
        xhr.open(method, url);
      } else {
        xhr = null;
      }

      return xhr;
    }

    /**
     * Uploads the voice data
     */
    function uploadVoiceData(success, error) {
      showSavingMsg();

      savingInProgress = true;

      // disbale record button on saving voicedata
      recordButton('disable');

      var url = 'upload.php';
      WebRecorder.upload({
        xhr: function() {
          var xhr = createCORSRequest('POST', url);
          xhr.upload.addEventListener("progress", function(evt) {
            if (evt.lengthComputable) {
              var complete = parseInt((evt.loaded / evt.total * 100 || 0));
              // debug && console.log(complete + '%');
            }
          }, false);

          return xhr;
        },
        method: 'POST',
        url: url,
        audioParam: "track",
        params: {
          'action': 'uploadFile',
          'formID': formID
        },
        dataType: 'JSON',
        success: function(response) {
          savingInProgress = false;
          recordButton('enable');
          if (response.result) {
            uploadSuccess = true;
            sendData = {
              value: JFCustomWidgetUtils.getS3UriRelativePath(response.url),
              valid: true
            };
            JFCustomWidget.sendData(sendData);
            JFCustomWidget.hideWidgetError();

            showSaveSuccess();

            debug && console.log('Voice data successfully uploaded', response);

            success && success();
          } else {
            uploadSuccess = false;
            showSaveFailed();
            sendData = {
              value: '',
              valid: false
            };
            JFCustomWidget.sendData(sendData);

            error && error();
          }
        },
        error: function(err) {
          debug && console.log(err);
          uploadSuccess = false;
          savingInProgress = false;
          recordButton('enable');
          showSaveFailed();
          sendData = {
            value: '',
            valid: false
          };
          JFCustomWidget.sendData(sendData);

          error && error();
        }
      });
    }

    /**
     * Init the native file uploader
     */
    function initNativeUploader() {
      $('iframe#uploadTrg').load(function() {
        console.log('Iframe loaded', $(this));
        var content = $(this).contents().find('body').html();
        if (!content) {
          console.log('Iframe `uploadTrg` has no content loaded');
          return false;
        }

        var serverMessage = content.match(/\{.*\}/);
        if (serverMessage !== null) {
          serverMessage = serverMessage[0];
        }

        try {
          // alert(serverMessage);
          var response = JSON.parse(serverMessage);
          console.log('iframe response', response);
          if (response.result) {
            uploadSuccess = true;
            JFCustomWidget.sendData({
              value: JFCustomWidgetUtils.getS3UriRelativePath(response.url),
              valid: true
            });
          } else {
            uploadSuccess = false;
            JFCustomWidget.sendData({
              value: '',
              valid: false
            });
          }
        } catch(e) {
          console.log('The response is malformed', e.getMessage());
        }
      });

      // now append the form that will hold the file uploa
      var form = $('<form />', {
        action: 'upload.php',
        method: 'POST',
        enctype: 'multipart/form-data',
        target: 'uploadTrg'
      }).submit(function() {
        // alert('form has been submitted');
      }).prependTo('body');

      // create the file input that accept audio files only
      var input = $('<input />', {
        type: 'file',
        name: 'file',
        accept: 'audio/*',
        capture: 'microphone'
      }).change(function() {
        // alert('File has been change, submitting form...');
        form.submit();
      }).appendTo(form);

      // formID & action needed for the request
      $('<input />', {type: 'hidden', name: 'formID', value: formID}).appendTo(form);
      $('<input />', {type: 'hidden', name: 'action', value: 'iframeTest'}).appendTo(form);
    }

    /**
     * Enable or disable record button
     */
    function recordButton(status) {
      status = (status && status === 'enable') ? false : true;
      $('#record').attr('disabled', status);
    }

    /**
     * Send the widget data to form on submit
     */
    function sendSubmitData() {
      debug && console.log('voiceData', voiceData);
      debug && console.log('saving progress', savingInProgress);
      debug && console.log('recording progress', recordingInProgress);
      debug && console.log('upload success', uploadSuccess);

      // if record in progress, Stop the recording right away
      if (recordingInProgress) {
        JFCustomWidget.hideWidgetError().showWidgetError('Recording in progress');
        // WebRecorder.stop();
        // recordingInProgress = false;
        // voiceData = true;
        // $('#record').removeClass('recording');
        // $('#record').children('span').text('Record');
        // $('#record').children('i').removeClass('fa-microphone-slash').addClass('fa-microphone');

        // $('#play').attr('disabled', false);
      } else if (voiceData) {
        // if already had a voice data recorded save it

        // if not saving in progress
        if (!savingInProgress) {
          // if success upload
          if (uploadSuccess) {
            // submit data
            JFCustomWidget.hideWidgetError().sendSubmit(sendData);
          } else {
            uploadVoiceData(function success() {
              JFCustomWidget.sendSubmit(sendData);
            }, function error() {
              JFCustomWidget.sendSubmit(sendData);
            });
          }
        } else {
          JFCustomWidget.hideWidgetError().showWidgetError('Saving in progress');
        }
      } else {
        var valid = (formData.required) ? false : true;
        JFCustomWidget.sendSubmit({
          value: '',
          valid: valid
        });
      }
    }

    function detectFlash() {
      // return true if browser supports flash, false otherwise
      // Code snippet borrowed from: https://github.com/swfobject/swfobject
      var SHOCKWAVE_FLASH = "Shockwave Flash",
        SHOCKWAVE_FLASH_AX = "ShockwaveFlash.ShockwaveFlash",
        FLASH_MIME_TYPE = "application/x-shockwave-flash",
        win = window,
        nav = navigator,
        hasFlash = false;

      if (typeof nav.plugins !== "undefined" && typeof nav.plugins[SHOCKWAVE_FLASH] === "object") {
        var desc = nav.plugins[SHOCKWAVE_FLASH].description;
        if (desc && (typeof nav.mimeTypes !== "undefined" && nav.mimeTypes[FLASH_MIME_TYPE] && nav.mimeTypes[FLASH_MIME_TYPE].enabledPlugin)) {
          hasFlash = true;
        }
      } else if (typeof win.ActiveXObject !== "undefined") {
        try {
          var ax = new ActiveXObject(SHOCKWAVE_FLASH_AX);
          if (ax) {
            var ver = ax.GetVariable("$version");
            if (ver) hasFlash = true;
          }
        } catch (e) {;
        }
      }

      return hasFlash;
    }

    function isBeepEnabled() {
      return 'beepNotif' in params && params.beepNotif === 'true';
    }

    function showStatus(msgObj) {
      $('#VoiceRecStatus').html('');
      if (msgObj) {
        var spanAttrib = $('<span/>').css({
            color: '#808080',
            fontSize: '11px'
          }).html(msgObj);
        $('#VoiceRecStatus').append(spanAttrib);
      }
    }

    function showSaveFailed() {
      showStatus('Saving failed. <img src="http://cdn.jotfor.ms/images/delete.png" alt="failed" />');
    }

    function showSaveSuccess() {
      showStatus('Recording saved. <img src="//cdn.jotfor.ms/images/accept.png" alt="success" />');
    }

    function showSavingMsg() {
      showStatus('Saving... <img src="//www.jotform.com/images/loader.gif" />');
    }

    function showError(message) {
      var aClick = $('<a/>', {href: '#'}).text('here');
      if (!!~message.indexOf('PermissionDeniedError')) {
        var errors = message.split('PermissionDeniedError');
        $.each(errors, function(index, value) {
          errors[index] = $.trim(value.replace(/\:/g, ''));
        });
        message = errors.join('<br/>');
      } else if (!!~message.indexOf('NotSupportedError')) {
        aClick.click(function(e) {
          window.location.reload();
          e.preventDefault();
          e.stopPropagation();
        });
        message = $('<div/>').append('Could not access microphone. Refresh widget by clicking ').append(aClick);
      } else if (!!~message.indexOf('WebRTCUnavailable')) {
        // aClick.click(function(e) {
        //   initNativeUploader();
        //   $('#errorMsg').hide();
        //   e.preventDefault();
        //   e.stopPropagation();
        // });
        // message = $('<div/>').append('You can upload your file recording by clicking ').append(aClick);
        message = message.split('WebRTCUnavailable::')[1] + ' Please use another browser.';
      }

      $('#wrapper').remove();
      var errorEl = $('#errorMsg').show().append(message);
      resizeWidget(errorEl.outerWidth(true), errorEl.outerHeight(true));
    }
  }

  JFCustomWidget.subscribe('ready', function(data) {
    var widget = new VoiceRecorder(data);

    // replace widget with normal upload for mobile - for now
    // TODO - include this
    // if (JFCustomWidgetUtils.isMobile() || !widget.hasFlash()) {
    //   JFCustomWidget.replaceWidget({
    //     type: 'control_fileupload',
    //     isMobile: false
    //   });
    //   return;
    // }

    widget.init();

    JFCustomWidget.subscribe('submit', function(msg) {
      widget.sendSubmitData();
    });
  });
});
