class ECMSUploadAdapter {
	constructor( loader, fieldName ) {
		this.loader = loader;
        this.fieldName = fieldName;
	}

	upload() {
        return this.loader.file
            .then(file => this._cropImage(file))
            .then(([croppedBlob, filename]) => this._uploadToServer(croppedBlob, filename))
            .then(response => ({ default: response.url }));
	}

    _cropImage(file) {
        return new Promise((resolve, reject) => {
            let optionsField = this.fieldName;

            var imagePopup = $('.image-popup-template').clone().attr('class', 'image-popup');

            imagePopup.on('click','.finish-image-editing', function() {

                var canvas = cropper.getCroppedCanvas({
                    fillColor: 'transparent',
                    imageSmoothingEnabled: false,
                    imageSmoothingQuality: 'high',
                });

                var ext = file.name.split('.').pop().toLowerCase();

                var mime = '';
                if (ext == 'jpg' || ext == 'jpeg' || ext == 'jfif') {
                    mime = 'image/jpeg';
                } else if(ext =='webp') {
                    mime = 'image/webp';
                } else {
                    mime = 'image/png';
                }

                //Create blob
                canvas.toBlob(function(blob) {

                    imagePopup.remove();
                    resolve([blob, file.name]);
                }, mime, 1.00);

                
            });

            imagePopup.on('click','.close-image-popup', function() {
                reject();

                //Close popup
                imagePopup.remove();
            });

            //Rotate image
            imagePopup.on('click', '.rotate-image', function() {
                dz_rotate_image(cropper);
            });
            
            var cropper;
            ImageTools.resize(file, {
                width: 5000, // maximum width
                height: 5000 // maximum height
            }, function(blob, didItResize) {
                imagePopup.appendTo('body');
                
                show_image_popup('<img class="cropper-image" src="'+window.URL.createObjectURL(blob)+'" />');

                let cropperOptions = {
                    // aspectRatio: 1,
                    responsive: false,
                    rotatable: true,
                    zoomable: false,
                    zoomOnTouch: false,
                    zoomOnWheel: false,
                    autoCropArea: 1
                }

                //Overide cropper options
                if (ecmsFieldOptions.hasOwnProperty(optionsField) && ecmsFieldOptions[optionsField].hasOwnProperty('cropper')) {
                    for (let o in ecmsFieldOptions[optionsField].cropper) {
                        cropperOptions[o] = ecmsFieldOptions[optionsField].cropper[o];
                    }
                }

                $('.cropper-image').cropper(cropperOptions);
                
                cropper = $('.cropper-image').data('cropper');

            });
        });
    }

    _uploadToServer(blob, filename) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', dynamicPath + '/ajax_ck_upload', true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.onload = () => {
                if (xhr.status === 200) {
                    resolve(JSON.parse(xhr.responseText));
                } else {
                    reject(new Error('Upload failed'));
                }
            };
            const formData = new FormData();
            formData.append('file', blob, filename);
            xhr.send(formData);
        });
    }

	// Aborts the upload process.
	abort() {
		// Reject the promise returned from the upload() method.
		server.abortUpload();
	}
}