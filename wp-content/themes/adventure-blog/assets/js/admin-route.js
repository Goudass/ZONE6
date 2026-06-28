(function ($) {
  let gpxFrame;
  let galleryFrame;

  $('#adventure_gpx_upload').on('click', function (event) {
    event.preventDefault();

    if (gpxFrame) {
      gpxFrame.open();
      return;
    }

    gpxFrame = wp.media({
      title: 'Wybierz plik GPX',
      button: { text: 'Użyj pliku' },
      library: { type: '' },
      multiple: false,
    });

    gpxFrame.on('select', function () {
      const attachment = gpxFrame.state().get('selection').first().toJSON();
      $('#adventure_gpx_id').val(attachment.id);
      $('#adventure_gpx_filename').text(attachment.filename || attachment.title);
    });

    gpxFrame.open();
  });

  $('#adventure_gpx_remove').on('click', function (event) {
    event.preventDefault();
    $('#adventure_gpx_id').val('');
    $('#adventure_gpx_filename').text('Brak pliku GPX');
  });

  $('#adventure_gallery_upload').on('click', function (event) {
    event.preventDefault();

    if (galleryFrame) {
      galleryFrame.open();
      return;
    }

    galleryFrame = wp.media({
      title: 'Wybierz zdjęcia do galerii',
      button: { text: 'Dodaj do galerii' },
      multiple: true,
    });

    galleryFrame.on('select', function () {
      const selection = galleryFrame.state().get('selection');
      const ids = [];
      const preview = $('#adventure_gallery_preview');
      preview.empty();

      selection.each(function (attachment) {
        const data = attachment.toJSON();
        ids.push(data.id);
        if (data.sizes && data.sizes.thumbnail) {
          preview.append('<img src="' + data.sizes.thumbnail.url + '" alt="">');
        }
      });

      $('#adventure_gallery_ids').val(ids.join(','));
    });

    galleryFrame.open();
  });

  $('#adventure_gallery_clear').on('click', function (event) {
    event.preventDefault();
    $('#adventure_gallery_ids').val('');
    $('#adventure_gallery_preview').empty();
  });
})(jQuery);
