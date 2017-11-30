if (!c5dk){ var c5dk = {}; }
if (!c5dk.blog)	{ c5dk.blog = {}; }
if (!c5dk.blog.service) { c5dk.blog.service = {}; }

c5dk.blog.service.thumbnailCropper = {
    
    
    init: function () {
        // Settings: Select action
        $('a[data-type=settings]').on('click', function (e) {
            e.preventDefault(); // Keeps page from scrolling up
            ConcreteFileManager.launchDialog(function (data) {
                ConcreteFileManager.getFileDetails(data.fID, function (r) {
                    jQuery.fn.dialog.hideLoader();
                    c5dk.blog.service.data.thumbnailCropper.file = r.files[0];
                    $('#thumbailID').val(c5dk.blog.service.data.thumbnailCropper.file.fID);

                    // Bind onLoad event so we can setup the thumbnails properties and then set the src to the thumbnail
                    $('#c5dk_crop_pic').bind('load', function () {
                        $('#pictureWidth').val($('#c5dk_crop_pic')[0].naturalWidth);
                        $('#pictureHeight').val($('#c5dk_crop_pic')[0].naturalHeight);
                        c5dk.blog.service.thumbnailCropper.useAsThumb(c5dk.blog.service.data.thumbnailCropper.file.fID, c5dk.blog.service.data.thumbnailCropper.file.url);
                        $('#c5dk_crop_pic').unbind('load');
                    }).attr('src', c5dk.blog.service.data.thumbnailCropper.file.url).show();
                });
            });
        });
        
        // Blog Post: Select thumbnail action
        $('a[data-type=post]').on('click', function () {
            c5dk.blog.post.image.showManager('thumbnail');
        });
        
        // $('#c5dk_bp').on('submit', function (e) {
            //     if (c5dk.blog.service.data.thumbnailCropper.crop_img) {
            //         c5dk.blog.service.data.thumbnailCropper.crop_img.cropper('getCroppedCanvas', { fillColor: c5dk.blog.service.data.thumbnailCropper.fillColor })
            //             .toBlob(function (blob) {
            //                 $('#c5dk_bp').append('croppedImage', blob);
                            
            //             }, 'image/jpeg', 80);

            //         if (c5dk.blog.service.data.thumbnailCropper.type == 'settings') {
            //             e.preventDefault();
            //             c5dk.blog.service.thumbnailCropper.save();

            //         } else {
            //             c5dk.blog.post.blog.save();
            //         }
            //         // [c5dk.blog.service.data.thumbnailCropper.onSaveCallback]();

            //     } else {
            //         return true;
            //     }
        // });
    },

    // save: function () {
        
        //     var blogID = $('#blogID').val()? $('#blogID').val() : 0;

        //     c5dk.blog.modal.waiting(c5dk.blog.service.data.thumbnailCropper.text.waiting);

        //     $.ajax(c5dk.blog.service.data.thumbnailCropper.url.save + blogID, {
        //         method: "POST",
        //         data: new FormData(document.forms["c5dk_blog_form"]),
        //         processData: false,
        //         contentType: false,
        //         success: function (result) {
        //             if (result.status) {
        //                 window.location = 'c5dk.blog.service.data.thumbnailCropper.url.webroot' + result.redirectLink;
        //             }
        //         },
        //         error: function () {
        //             console.log('Upload error');
        //         }
        //     });
    // },
    
    addToForm: function (form, callback) {
        if (c5dk.blog.service.data.thumbnailCropper.crop_img) {
            var canvas = c5dk.blog.service.data.thumbnailCropper.crop_img.cropper('getCroppedCanvas',
                {
                    fillColor: c5dk.blog.service.data.thumbnailCropper.fillColor
                });
            canvas.toBlob(function (blob) {
                c5dk.blog.settings.form.append('croppedImage', blob);
                callback();
            }, 'image/jpeg', 80);
        }
        return form;
    },

    remove: function () {
        $('#thumbnailID').val(-1);
        if (c5dk.blog.service.data.thumbnailCropper.crop_img) {
            c5dk.blog.service.data.thumbnailCropper.crop_img.cropper('destroy');
            c5dk.blog.service.data.thumbnailCropper.crop_img = null;
        }
        $('#c5dk_blog_thumbnail, #c5dk_crop_pic').attr('src', '').hide();

        // Hide Cropper buttons
        $('#c5dk_cropper_buttons').hide();

    },

    useAsThumb: function (fID, src) {

        document.getElementById('thumbnail').scrollIntoView();

        // Hide the slide-in Image manager
        // c5dk.blog.service.data.thumbnailCropper.image.hideManager();

        // Destroy old cropper instance if exist
        c5dk.blog.service.thumbnailCropper.remove();

        // Show Cropper buttons
        $('#c5dk_cropper_buttons').show();

        $('#thumbnailID').val(fID);

        // Update
        $('#c5dk_crop_pic').attr('src', src).show();

        c5dk.blog.service.data.thumbnailCropper.crop_img = $('#c5dk_crop_pic').cropper({
            aspectRatio: (c5dk.blog.service.data.thumbnailCropper.save.width / c5dk.blog.service.data.thumbnailCropper.save.height),
            responsive: true,
            preview: '#cropper_preview',
            crop: function(coords) {
                // Set form objects
                $('#thumbnailX').val(coords.x);
                $('#thumbnailY').val(coords.y);
                $('#thumbnailWidth').val(coords.width);
                $('#thumbnailHeight').val(coords.height);
                $('#pictureWidth').val($('#c5dk_crop_pic').width());
                $('#pictureHeight').val($('#c5dk_crop_pic').height());
            }
        });
    }
};

$(document).ready(function() { c5dk.blog.service.thumbnailCropper.init(); });
