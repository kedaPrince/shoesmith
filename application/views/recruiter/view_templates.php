<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="hidden-templates">

    <!-- Image Cropper Popup Template -->
    <div class="image-popup-template">
        <div>
            <div class="image-popup-content"></div>
            <div class="image-popup-controls">
                <a class="finish-image-editing" title="Done"><i class="fa fa-check"></i></a>
                <a class="rotate-image" title="Rotate"><i class="fa fa-rotate-left"></i></a>
                <a class="close-image-popup" title="Cancel"><i class="fa fa-times"></i></a>
            </div>
        </div>
    </div>

    <!-- DZ Uploader Template -->
    <div class="dz-thumb-template">
        <div class="dz-preview dz-file-preview">
            <div class="dz-image"><img data-dz-thumbnail /></div>
            <div class="dz-details">
                <div class="dz-error-message"><span data-dz-name></span><span data-dz-errormessage></span></div>
                <div class="dz-filename"><span data-dz-name></span></div>
                <div class="dz-size"><span data-dz-size></span></div>
                <div class="dz-controls">
                    <div class="dz-pos-prev dz-btn"><i class="dz-icon-left fa fa-arrow-left"></i><i class="dz-icon-up fa fa-arrow-up"></i></div>
                    <div class="dz-pos-next dz-btn"><i class="dz-icon-right fa fa-arrow-right"></i><i class="dz-icon-down fa fa-arrow-down"></i></div>
                    <div title="Download" class="dz-download dz-btn"><i class="fa fa-download"></i></div>
                    <div title="Delete" class="dz-remove dz-btn"><i class="fa fa-trash-o"></i></div>
                    <div title="Edit" class="dz-edit dz-btn"><i class="fa fa-pencil"></i></div>
                </div>
            </div>
            <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>
            <div class="dz-success-mark"><i class="icon-check"></i></div>
            <div class="dz-error-mark"><i class="icon-close"></i></div>
        </div>
    </div>
</div>