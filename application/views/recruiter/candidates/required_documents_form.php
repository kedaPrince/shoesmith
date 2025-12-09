<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Required Documents</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <style>
    body {
        background-color: #f8f9fa;
        padding: 20px;
    }

    .required-documents-container {
        max-width: 800px;
        margin: 0 auto;
        background: white;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        padding: 30px;
    }

    .document-upload-row {
        background: #f8f9fa;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 15px;
        border: 1px solid #dee2e6;
        transition: all 0.3s;
    }

    .document-upload-row:hover {
        background: #e9ecef;
        border-color: #adb5bd;
    }

    .header-section {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        color: white;
        padding: 20px;
        border-radius: 10px 10px 0 0;
        margin-bottom: 30px;
    }

    .candidate-info-card {
        background: #e3f2fd;
        border-left: 4px solid #2196f3;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .file-requirements {
        background: #f1f8e9;
        border-left: 4px solid #7cb342;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .alert-custom {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .btn-action {
        padding: 10px 30px;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.3s;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 99999;
    }

    .spinner-large {
        width: 50px;
        height: 50px;
    }

    /* Fix for Bootstrap custom file input */
    .custom-file-label::after {
        content: "Browse";
    }

    /* Debug section */
    .debug-section {
        margin-top: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 5px;
        border: 1px dashed #6c757d;
    }
    </style>
</head>

<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="text-center">
            <div class="spinner-border text-primary spinner-large" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <h4 class="mt-3">Uploading Documents...</h4>
            <p>Please wait while we process your files</p>
        </div>
    </div>

    <div class="required-documents-container">
        <div class="header-section">
            <h2><i class="fas fa-exclamation-triangle"></i> Submit Required Documents</h2>
            <p class="mb-0">Please upload the documents requested by the agency</p>
        </div>

        <!-- Candidate Information -->
        <div class="candidate-info-card">
            <h5><i class="fas fa-user"></i> Candidate Information</h5>
            <div class="row">
                <div class="col-md-6">
                    <strong>Candidate:</strong> <?= htmlspecialchars($candidate_name, ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div class="col-md-6">
                    <strong>Reference:</strong> <?= htmlspecialchars($candidate_ref, ENT_QUOTES, 'UTF-8') ?>
                </div>
            </div>
        </div>

        <!-- Documents Request Alert -->
        <div class="alert alert-warning mb-4">
            <h5><i class="fas fa-exclamation-triangle"></i> Documents Requested by Agency</h5>
            <p class="mb-2"><strong>Required Documents:</strong></p>
            <p class="mb-0"><?= nl2br(htmlspecialchars($documents_request_notes, ENT_QUOTES, 'UTF-8')) ?></p>
        </div>

        <!-- File Requirements -->
        <div class="file-requirements">
            <h6><i class="fas fa-info-circle"></i> File Requirements:</h6>
            <ul class="mb-0">
                <li>Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG</li>
                <li>Maximum file size: 10MB per document</li>
                <li>Make sure files are clear and readable</li>
                <li>You can upload multiple documents at once</li>
            </ul>
        </div>

        <!-- Upload Form - SIMPLIFIED STRUCTURE -->
        <div class="unified-documents-form">
            <form id="requiredDocumentsForm" method="POST" enctype="multipart/form-data">
                <!-- CSRF Token -->
                <input type="hidden" name="<?= $csrf_token_name ?>" value="<?= $csrf_token_hash ?>">
                <input type="hidden" name="candidate_id" value="<?= $candidate_id ?>">
                <input type="hidden" name="is_required_documents" value="1">
                <?php if (!empty($notification_id)): ?>
                <input type="hidden" name="notification_id" value="<?= $notification_id ?>">
                <?php endif; ?>

                <!-- Document Upload Fields -->
                <div class="document-upload-section mb-4">
                    <h4 class="mb-3"><i class="fas fa-upload"></i> Upload Documents</h4>

                    <div id="documentUploadContainer">
                        <!-- First document field - SIMPLIFIED ARRAY STRUCTURE -->
                        <div class="document-upload-row mb-3">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Document Name *</label>
                                        <input type="text" name="document_names[]" class="form-control document-name"
                                            placeholder="e.g., ID Copy, Degree Certificate" required>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label class="font-weight-bold">File *</label>
                                        <div class="custom-file">
                                            <input type="file" name="document_files[]"
                                                class="custom-file-input document-file"
                                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                                            <label class="custom-file-label">Choose file</label>
                                        </div>
                                        <small class="form-text text-muted">Max 10MB</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="font-weight-bold invisible">Actions</label>
                                        <button type="button" class="btn btn-outline-danger btn-block remove-document"
                                            style="margin-top: 32px;" disabled title="Cannot remove first document">
                                            <i class="fas fa-trash"></i> Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Description (Optional)</label>
                                        <textarea name="document_descriptions[]"
                                            class="form-control document-description" rows="2"
                                            placeholder="Brief description of this document..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add More Documents Button -->
                    <div class="text-center mb-4">
                        <button type="button" class="btn btn-outline-primary" id="addMoreDocuments">
                            <i class="fas fa-plus"></i> Add Another Document
                        </button>
                        <button type="button" class="btn btn-outline-secondary ml-2" id="clearAllDocuments">
                            <i class="fas fa-times"></i> Clear All
                        </button>
                    </div>
                </div>

                <!-- Submission Notes -->
                <div class="form-group mb-4">
                    <label class="font-weight-bold">Additional Notes for Agency (Optional)</label>
                    <textarea name="submission_notes" class="form-control" rows="3"
                        placeholder="Add any additional notes or comments for the agency..."></textarea>
                </div>

                <!-- Submit Button -->
                <div class="text-center">
                    <button type="submit" class="btn btn-success btn-lg px-5 btn-action" id="submitDocumentsBtn">
                        <i class="fas fa-paper-plane"></i> Submit All Documents to Agency
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-lg ml-2 btn-action"
                        onclick="window.close()">
                        <i class="fas fa-times"></i> Cancel & Close
                    </button>
                </div>
            </form>
        </div>

        <!-- Upload Progress -->
        <div class="progress mt-3" style="display: none;" id="uploadProgressBar">
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">
            </div>
        </div>

        <!-- Debug Section -->
        <div class="debug-section" style="display: none;" id="debugSection">
            <h6><i class="fas fa-bug"></i> Debug Information</h6>
            <button type="button" class="btn btn-sm btn-info" id="debugBtn">Check Form Data</button>
            <div id="debugOutput" class="mt-2 small"></div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    $(document).ready(function() {
        let documentCount = 1;

        // ========== CSRF COOKIE HELPER ==========
        function setCsrfCookie(token) {
            // Simple cookie setting for localhost
            document.cookie = `csrf_rfid_token=${token}; path=/`;
            console.log('CSRF cookie set');
        }

        // Set cookie when page loads
        const csrfToken = $('input[name="<?= $csrf_token_name ?>"]').val();
        if (csrfToken) {
            setCsrfCookie(csrfToken);
        }

        // Update custom file input labels
        $(document).on('change', '.custom-file-input', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });

        // Add more documents button
        $('#addMoreDocuments').click(function() {
            const container = $('#documentUploadContainer');
            const newRow = $(`
            <div class="document-upload-row mb-3">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label class="font-weight-bold">Document Name *</label>
                            <input type="text" name="document_names[]"
                                class="form-control document-name"
                                placeholder="e.g., ID Copy, Degree Certificate" required>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label class="font-weight-bold">File *</label>
                            <div class="custom-file">
                                <input type="file" name="document_files[]"
                                    class="custom-file-input document-file" 
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                                <label class="custom-file-label">Choose file</label>
                            </div>
                            <small class="form-text text-muted">Max 10MB</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="font-weight-bold invisible">Actions</label>
                            <button type="button" class="btn btn-outline-danger btn-block remove-document" 
                                    style="margin-top: 32px;" title="Remove this document">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label>Description (Optional)</label>
                            <textarea name="document_descriptions[]"
                                class="form-control document-description" rows="2"
                                placeholder="Brief description of this document..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
        `);

            container.append(newRow);
            documentCount++;

            // Enable remove button on first row if we have more than one
            if (documentCount > 1) {
                $('.remove-document').first().prop('disabled', false);
            }
        });

        // Clear all documents button
        $('#clearAllDocuments').click(function() {
            if (confirm('Are you sure you want to clear all document fields?')) {
                $('#documentUploadContainer').empty();
                documentCount = 0;
                $('#addMoreDocuments').click();
            }
        });

        // Remove document row
        $(document).on('click', '.remove-document', function() {
            if ($('.document-upload-row').length > 1) {
                $(this).closest('.document-upload-row').remove();
                documentCount = $('.document-upload-row').length;

                // Disable remove on first if only one row
                if (documentCount === 1) {
                    $('.remove-document').first().prop('disabled', true);
                }
            }
        });

        // ========== FIXED FORM SUBMISSION ==========
        $('#requiredDocumentsForm').submit(function(e) {
            e.preventDefault();

            console.log('Starting document upload...');

            // Get CSRF token
            const csrfToken = $('input[name="<?= $csrf_token_name ?>"]').val();

            // Ensure cookie is set
            setCsrfCookie(csrfToken);

            // Show loading
            $('#submitDocumentsBtn').prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin"></i> Uploading...');
            $('#uploadProgressBar').show();

            // Create FormData
            const formData = new FormData(this);

            // Use XMLHttpRequest directly (jQuery has issues with FormData)
            const xhr = new XMLHttpRequest();

            // Progress tracking
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percent = (e.loaded / e.total) * 100;
                    $('#uploadProgressBar .progress-bar').css('width', percent + '%');
                }
            });

            // Handle response
            xhr.onload = function() {
                console.log('Response received:', xhr.status);

                try {
                    const response = JSON.parse(xhr.responseText);

                    // Update CSRF token if provided
                    if (response.csrf_token) {
                        $('input[name="<?= $csrf_token_name ?>"]').val(response.csrf_token);
                        setCsrfCookie(response.csrf_token);
                    }

                    if (response.success) {
                        alert(response.message || 'Documents uploaded successfully!');

                        // Close popup after delay
                        setTimeout(function() {
                            if (window.opener && !window.opener.closed) {
                                window.opener.postMessage({
                                    type: 'documentsSubmitted',
                                    success: true
                                }, '*');
                            }
                            window.close();
                        }, 1500);
                    } else {
                        alert('Error: ' + response.message);
                        $('#submitDocumentsBtn').prop('disabled', false)
                            .html(
                                '<i class="fas fa-paper-plane"></i> Submit All Documents to Agency'
                                );
                    }
                } catch (error) {
                    console.error('Parse error:', error);
                    alert('Server error. Please try again.');
                    $('#submitDocumentsBtn').prop('disabled', false)
                        .html('<i class="fas fa-paper-plane"></i> Submit All Documents to Agency');
                }

                $('#uploadProgressBar').hide();
            };

            xhr.onerror = function() {
                alert('Network error. Please check your connection.');
                $('#submitDocumentsBtn').prop('disabled', false)
                    .html('<i class="fas fa-paper-plane"></i> Submit All Documents to Agency');
                $('#uploadProgressBar').hide();
            };

            // Send request
            xhr.open('POST', '<?= site_url("recruiter/candidates/submit_required_documents") ?>');
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
            xhr.send(formData);
        });

        // Debug button
        $('#debugBtn').click(function() {
            const debugOutput = $('#debugOutput');
            debugOutput.empty();

            // Check FormData
            const formData = new FormData($('#requiredDocumentsForm')[0]);
            debugOutput.append('<h6>FormData Contents:</h6>');
            for (let pair of formData.entries()) {
                debugOutput.append(
                    '<p><strong>' + pair[0] + ':</strong> ' +
                    (pair[1] instanceof File ? pair[1].name + ' (' + pair[1].size + ' bytes)' :
                        pair[1]) +
                    '</p>'
                );
            }
        });

        // Show debug section if holding Shift key
        $(document).keydown(function(e) {
            if (e.shiftKey && e.altKey && e.key === 'D') {
                $('#debugSection').toggle();
            }
        });
    });
    </script>
</body>

</html>