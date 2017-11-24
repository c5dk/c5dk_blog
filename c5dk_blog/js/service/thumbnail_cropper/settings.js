if (!c5dk){ var c5dk = {}; }
if (!c5dk.blog)	{ c5dk.blog = {}; }
if (!c5dk.blog.service) { c5dk.blog.service = {}; }

c5dk.blog.service.thumbnailCropper = {


    init: function() {
        // Open filemanager
        $('a[data-launch=file-manager]').on('click', function(e) {
            e.preventDefault(); // Keeps page from scrolling up
                ConcreteFileManager.launchDialog(function (data) {
                    ConcreteFileManager.getFileDetails(data.fID, function(r) {
                        jQuery.fn.dialog.hideLoader();
                        c5dk.blog.service.data.thumbnailCropper.file = r.files[0];
                        $('#thumbailID').val(c5dk.blog.service.data.thumbnailCropper.file.fID);
                        $('#c5dk_crop_pic').bind('load', function(e){
                            $('#pictureWidth').val($('#c5dk_crop_pic')[0].naturalWidth);
                            $('#pictureHeight').val($('#c5dk_crop_pic')[0].naturalHeight);
                            c5dk.blog.service.data.thumbnailCropper.thumbnail.useAsThumb(c5dk.blog.service.data.thumbnailCropper.file.fID, c5dk.blog.service.data.thumbnailCropper.file.url, $('#c5dk_crop_pic')[0].width, $('#c5dk_crop_pic')[0].height);
                            $('#c5dk_crop_pic').unbind('load');
                        }).attr('src', c5dk.blog.service.data.thumbnailCropper.file.url).show();
                    });
                });
        });

        $('#c5dk_bp').on('submit', function() {
            if (c5dk.blog.service.data.thumbnailCropper.crop_img) {
                c5dk.blog.service.data.thumbnailCropper.crop_img.cropper('getCroppedCanvas', {fillColor: '<?= $C5dkConfig->blog_cropper_def_bgcolor; ?>'}).toBlob(function (blob) {

                    $('#c5dk_bp').append('croppedImage', blob);
                    return false;

                }, "image/jpeg", 80);
            } else {
                return true;
            }
        });
    },

    thumbnail: {


        remove:function () {
            $('#thumbnailID').val(-1);
            if (c5dk.blog.service.data.thumbnailCropper.crop_img) {
                c5dk.blog.service.data.thumbnailCropper.crop_img.cropper('destroy');
                c5dk.blog.service.data.thumbnailCropper.crop_img = null;
            }
            $('#c5dk_blog_thumbnail, #c5dk_crop_pic').attr('src', "").hide();

            // Hide Cropper buttons
            $("#c5dk_cropper_buttons").hide();

        },

        useAsThumb:function (fID, src, width, height) {

            document.getElementById('thumbnail').scrollIntoView();

            // Hide the slide-in Image manager
            // c5dk.blog.service.data.thumbnailCropper.image.hideManager();

            // Destroy old cropper instance if exist
            c5dk.blog.service.data.thumbnailCropper.thumbnail.remove();

            // Show Cropper buttons
            $("#c5dk_cropper_buttons").show();

            $('#thumbnailID').val(fID);

            // Update
            $('#c5dk_crop_pic').attr('src', src).show()

            c5dk.blog.service.data.thumbnailCropper.crop_img = $('#c5dk_crop_pic').cropper({
                aspectRatio: (c5dk.blog.service.data.thumbnailCropper.thumbnail.save.width / c5dk.blog.service.data.thumbnailCropper.thumbnail.save.height),
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
    }
};

$(document).ready(function() { c5dk.blog.service.thumbnailCropper.init(); });
