$(function() {
  var options = window.registrationOptions;
  var regulations = options.regulations;
  var specialRegulations = {}
  var fee = $('#fee');
  $(document).on('change', '.registration-agreements', function() {
    if ($('#submit-button').hasClass('disabled')) {
      return
    }
    var checked = true
    $('.registration-agreements').each(function() {
      checked = checked && this.checked
    });
    $('#submit-button').prop('disabled', !checked);
  }).on('change', '.registration-events, #Registration_location_id', function() {
    updateFee();
  }).on('change', '#Registration_has_entourage', function() {
    $('.entourage-info')[this.value == 1 ? 'removeClass' : 'addClass']('hide');
    updateFee();
  }).on('change', '#Registration_staff_type', function() {
    $('.staff-info')[this.value > 0 ? 'removeClass' : 'addClass']('hide');
    updateFee();
  }).on('click', '#submit-button', function(e) {
    var checkedEvents = $('.registration-events:checked');
    if (options.showRegulations) {
      e.preventDefault();
      var specialRegulations = {}
      checkedEvents.each(function() {
        var that = $(this);
        var event = that.val();
        switch (event) {
          case '333ft':
          case 'clock':
            specialRegulations[event] = true;
            break;
          case '333bf':
            specialRegulations.bf = true;
            break;
          case '444bf':
          case '555bf':
            specialRegulations.bf = true;
            specialRegulations.lbf = true;
            specialRegulations.bbf = true;
            break;
          case '333mbf':
            specialRegulations.bf = true;
            specialRegulations.lbf = true;
            break;
        }
      });
      var message = [];
      if (options.showRegulations) {
        var ol = $('<ol>');
        options.regulations.common.forEach(function(r) {
          ol.append($('<li>').html(r));
        });
        $.each(options.regulations.special, function(k, r) {
          if (specialRegulations[k]) {
            ol.append($('<li>').html(r));
          }
        });
        message.push('<h4>' + options.regulationDesc + '</h4>');
        message.push($('<div>').append(ol).html());
      }
      CubingChina.utils.confirm(message, {
        type: 'type-warning'
      }).then(function() {
        $('#registration-form').submit();
      });
    }
  });
  function updateFee() {
    var totalFee = options.basicFee;
    if (options.complexMultiLocation) {
      totalFee = $('#Registration_location_id option:selected').data('fee') || 0;
      $('#entry-fee').text(totalFee || '-')
    }
    if ($('#Registration_has_entourage').val() == 1) {
      totalFee += options.entourageFee;
    }
    $('.registration-events:checked').each(function() {
      totalFee += $(this).data('fee');
    });
    if (options.multiCountries) {
      totalFee = $('#Registration_location_id option:selected').data('display-fee');
    }
    if (totalFee && totalFee != 0) {
      fee.removeClass('hide').find('#totalFee').html(totalFee);
    } else {
      fee.addClass('hide');
    }
  }
  $('.registration-events').trigger('change');
  $('#Registration_has_entourage').trigger('change');
  $('#Registration_staff_type').trigger('change');

  $.each(options.unmetEvents, function(event, qualifyingTime) {
    $('.registration-events[value="' + event + '"]').parent().addClass('bg-danger').data('qualifyingTime', qualifyingTime);
  })
})
