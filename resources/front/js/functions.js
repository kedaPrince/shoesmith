jQuery(document).ready(function(){
    var $ = jQuery;

     //Image uploader events
     $('form').on('click', '.image-grid, .new-image', function(){
        var target = $(this).closest('.image-uploader').attr('rel');
        $('.smart-uploader input[type="file"]').attr('data-target', target);
        $('.smart-uploader input[type="file"]').trigger('click');
    });

    $('form').on('click', '.delete-image', function(){
        var img = $(this).closest('.image-uploader');

        img.find('.image-grid').html('<i class="material-icons-outlined">add_photo_alternate</i>');
        img.find('.image-value').val('');
    });

    $('.image-popup').on('click', '.close-image-popup', function(){
        close_image_popup();
    });

    $('.image-popup').on('click', '.rotate-image', function() {
        rotate_image();
    });

    $('.image-popup').on('click', '.finish-image-editing', function() {
        crop_image();
    });

});

//Function for making ajax posts
function front_ajax_post(path, params, callback, options) {

    params[csrfName] = csrf;

    var callback = (typeof callback !== 'undefined') ? callback : null;
    var options = (typeof options !== 'undefined') ? options : {};

    options.url = (!options.hasOwnProperty('url')) ?  site_url+'/'+path : options.url;
    options.method = (!options.hasOwnProperty('method')) ? 'POST' : options.method;
    options.cache = (!options.hasOwnProperty('cache')) ? false : options.cache;
    options.data = (!options.hasOwnProperty('data')) ? params : options.data;
    
    $.ajax(options).done(function(d) {
        //Refresh csrf token
        csrf = d.csrf;

        //Also refresh the token for all forms
        $('form input[name="'+csrfName+'"]').each(function(){
            $(this).val(csrf);
        });

        //Run callback function
        if (typeof callback == 'function') {
            callback(d);
        }
        else if (typeof window[callback] == 'function') {
            window[callback](d);
        }
    }).fail(function() {
        console.log('Failed to do ajax post!');
    });
}

function rotate_image(){

    var conWidth    = cropper.containerData.width;
    var conHeight   = cropper.containerData.height;
    var imgWidth    = cropper.canvas.clientWidth;
    var imgHeight   = cropper.canvas.clientHeight;

    var newImgWidth     = 0;
    var newImgHeight    = 0;
    var newLeft         = 0;
    var newTop          = 0;

    if (imgWidth > imgHeight) {
        newImgHeight    = conHeight;
        newImgWidth     = null;

        //Make sure new image size still fits container
        checkWidth     = newImgHeight * (imgHeight/imgWidth);
        if (checkWidth > conWidth) {
            newImgHeight    = null;
            newImgWidth     = conWidth;
        }
    }
    else {
        newImgHeight    = null;
        newImgWidth     = conWidth;

        //Make sure new image size still fits container
        checkHeight     = newImgWidth * (imgWidth/imgHeight);
        if (checkHeight > conHeight) {
            newImgHeight    = conHeight;
            newImgWidth     = null;
        }
    }

    if (newImgHeight) {
        //Position center horizontally
        newLeft = (conWidth/2) - (newImgHeight * (imgHeight/imgWidth))/2;
    }
    else {
        //Position center vertically
        newTop = (conHeight/2) - (newImgWidth * (imgWidth/imgHeight))/2;
    }

    cropper = cropper.rotate(90);
    cropper = cropper.moveTo(0,0);
    cropper = cropper.setCanvasData({
        left: newLeft,
        top: newTop,
        height: newImgHeight,
        width: newImgWidth
    });

}

function edit_image(el) {

    var target  = $(el).attr('data-target');
    var ratio   = $('form .image-uploader[rel="'+target+'"]').attr('data-ratio');
    var sizes   = $('form .image-uploader[rel="'+target+'"]').attr('data-sizes');

    if ($.inArray(el.files[0].type, ['image/jpeg', 'image/png']) == -1) {
        show_error('You tried to upload an invalid file!');
        return false;
    }

    uploadedImages[target] = {};
    uploadedImages[target].name = el.files[0].name;

    if (sizes) {
        uploadedImages[target].sizes = sizes;
    }

    ImageTools.resize(el.files[0], {
        width: 5000, // maximum width
        height: 5000 // maximum height
    }, function(blob, didItResize) {
        show_image_popup('<img rel="'+target+'" class="cropper-image" src="'+window.URL.createObjectURL(blob)+'" />');

        $('.cropper-image').cropper({
            aspectRatio: ratio/1,
            responsive: false,
            rotatable: true,
            zoomable: false,
            zoomOnTouch: false,
            zoomOnWheel: false
        });
        
        cropper = $('.cropper-image').data('cropper');

    });
}

if (!HTMLCanvasElement.prototype.toBlob) {
    Object.defineProperty(HTMLCanvasElement.prototype, 'toBlob', {
        value: function (callback, type, quality) {
        var canvas = this;
        setTimeout(function() {

            var binStr = atob( canvas.toDataURL(type, quality).split(',')[1] ),
                len = binStr.length,
                arr = new Uint8Array(len);

            for (var i = 0; i < len; i++ ) {
            arr[i] = binStr.charCodeAt(i);
            }

            callback( new Blob( [arr], {type: type || 'image/png'} ) );

        });
        }
    });
}

function show_image_popup(html) {
    $('.image-popup-content').html(html);
    $('.image-popup').addClass('open');
}

function close_image_popup() {
    $('.image-popup').removeClass('open');
    $('.image-popup-content').html('');
}

function submit_images(el) {
    var toProcess = 0;
    for (var i in uploadedImages) {
        if ('canvas' in uploadedImages[i]) {
            toProcess++;

            var fd = new FormData();
            fd.append('fname', uploadedImages[i].name);
            fd.append('imagefile', uploadedImages[i].blob);
            fd.append(csrfName, csrf);
            fd.append('field_name', i);

            if ('sizes' in uploadedImages[i]) {
                fd.append('sizes', uploadedImages[i].sizes);
            }

            show_loader();
            $.ajax({
                type: 'POST',
                url: dynamicPath + '/ajax_upload_blob',
                data: fd,
                processData: false,
                contentType: false
            }).done(function(data) {
                toProcess--;
                csrf = data.csrf;

                if (data.success) {
                    $('form .image-uploader[rel="'+data.field_name+'"] .image-value').val(data.folder+'/'+data.file_name);

                    if (!toProcess) {
                        //$(el).closest('form').submit();
                        alert('success');
                    }
                }
                else {
                    hide_loader();
                    show_error('There was an error processing the image!');
                }
            });
        }
    }

    if (!toProcess) {
        alert('failed');
        //$(el).closest('form').submit();
    }
}

function crop_image() {
    var target = $('.cropper-image').attr('rel');

    uploadedImages[target].canvas = cropper.getCroppedCanvas({
        fillColor: '#fff',
        imageSmoothingEnabled: false,
        imageSmoothingQuality: 'high',
    });

    var ext = uploadedImages[target].name.split('.').pop().toLowerCase();

    var mime = '';
    if (ext == 'jpg' || ext == 'jpeg') {
        mime = 'image/jpeg';
    }
    else {
        mime = 'image/png';
    }

    //Create blob
    uploadedImages[target].canvas.toBlob(function(blob) {
        uploadedImages[target].blob = blob;
    }, mime, 1.00);

    var previewCrop = cropper.getCroppedCanvas({
        width: 200,
        height: 200,
        fillColor: '#fff',
        imageSmoothingEnabled: false,
        imageSmoothingQuality: 'high',
    });

    $('form .image-uploader[rel="'+target+'"] .image-grid').html(previewCrop);
    $('form .image-uploader[rel="'+target+'"] .image-value').val('');
    close_image_popup();
}

function setup_image_fields() {
    $('form .image-uploader').each(function(){
        var image = $(this).find('.image-value').val();

        if (image.length){
            $(this).find('.image-grid').html('<img src="'+site_url+'images/'+image+'" />');
        }
    });

    var uploadControlHtml = '<div class="smart-uploader" style="display:none">' + 
        '<input type="file" name="image_uploader" onchange="edit_image(this)" data-target="" />' +
    '</div>';
    
    $('body').append(uploadControlHtml);
}

function show_loader() {
    //Reserved for front-end loader
}

function hide_loader() {
    //Reserved for front-end loader
}