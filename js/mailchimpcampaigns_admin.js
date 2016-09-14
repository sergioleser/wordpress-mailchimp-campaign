jQuery(document).ready(function($) {
  var btn = $('#mailchimpcampaigns_import'),
        placeholder = $('#mailchimpcampaigns_placeholder'),
        dots = '<span class="dots"><span>.</span><span>.</span><span>.</span></span>';
  btn.on('click', function(e){
    e.preventDefault();
    btn.toggleClass('active');
    placeholder.addClass('notice');
    placeholder.html(
      '<span class="mailchimpcampaigns_ajax_loading">' +
        '<span class="dashicons dashicons-format-status"></span>' + ' Talking to the chimp' + dots + 
        '<br/>' + 
        'Please wait until all campaigns are imported.' +
        '<br/>' +
        'This may take a while if you have many campaigns in your Mailchimp account.' + 
        '</span>'
    );
    var data = {
      'action': 'mailchimpcampaigns_import'
    };
    $.post(ajaxurl, data, function(response) {
      btn.toggleClass('active');
      placeholder.removeClass('notice');
      placeholder.html( response );
    });
  });
});