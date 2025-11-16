<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div id="main-content">
    <div class="container-fluid">
        <div class="row clearfix">
            <div class="col-12">
                <div class="card">
                    <div class="header">
                        <h2><?php echo $heading; ?></h2>
                        <a href="<?php echo site_url('agency/chat'); ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to Chat
                        </a>
                    </div>
                    <div class="body">
                        <?php if (!empty($recruiters)): ?>
                        <div class="row">
                            <!-- Chat Form (Left Side) -->
                            <div class="col-md-8 col-lg-9">
                                <div class="chat-form-container">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0" id="formTitle">Select a Recruiter to Start Chatting</h5>
                                            <small class="text-muted" id="formSubtitle">Choose a recruiter from the
                                                right to begin your conversation</small>
                                        </div>
                                        <div class="card-body">
                                            <!-- Default State - No Recruiter Selected -->
                                            <div id="defaultState" class="text-center p-5">
                                                <i class="fa fa-comments fa-4x text-muted mb-3"></i>
                                                <h5 class="text-muted">No Recruiter Selected</h5>
                                                <p class="text-muted">Please select a recruiter from the list on the
                                                    right to start a conversation.</p>
                                            </div>

                                            <!-- Conversation Form (Shows when recruiter is selected) -->
                                            <div class="conversation-form" id="conversationForm" style="display: none;">
                                                <div class="selected-recruiter-info mb-4 p-3 bg-light rounded">
                                                    <div class="d-flex align-items-center">
                                                        <div class="recruiter-avatar mr-3">
                                                            <div id="selectedRecruiterAvatar"
                                                                class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center"
                                                                style="width: 50px; height: 50px; font-size: 1.2rem;">
                                                                <i class="fa fa-user"></i>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1" id="selectedRecruiterName">Recruiter Name
                                                            </h6>
                                                            <small class="text-muted"
                                                                id="selectedRecruiterEmail">email@example.com</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <form id="startConversationForm">
                                                    <input type="hidden" id="recruiter_id" name="recruiter_id">

                                                    <div class="form-group">
                                                        <label for="initial_message" class="font-weight-bold">Your
                                                            Message</label>
                                                        <textarea class="form-control" id="initial_message"
                                                            name="initial_message" rows="6"
                                                            placeholder="Type your message here... What would you like to discuss?"
                                                            style="resize: none;" required></textarea>
                                                        <small class="form-text text-muted">
                                                            This will be the first message in your conversation. Be
                                                            clear and specific about what you'd like to discuss.
                                                        </small>
                                                    </div>

                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <button type="button" class="btn btn-outline-secondary"
                                                            onclick="cancelSelection()">
                                                            <i class="fa fa-times"></i> Cancel
                                                        </button>
                                                        <button type="submit" class="btn btn-primary btn-lg">
                                                            <i class="fa fa-paper-plane"></i> Start Conversation
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Recruiters List (Right Side) -->
                            <div class="col-md-4 col-lg-3">
                                <div class="recruiters-sidebar">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">Available Recruiters</h6>
                                            <small class="text-muted"><?php echo count($recruiters); ?>
                                                recruiter(s)</small>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="recruiters-list" style="max-height: 500px; overflow-y: auto;">
                                                <?php foreach ($recruiters as $recruiter): ?>
                                                <div class="recruiter-item p-3 border-bottom" style="cursor: pointer;"
                                                    onclick="selectRecruiter(<?php echo $recruiter->id; ?>, '<?php echo htmlspecialchars($recruiter->first_name . ' ' . $recruiter->last_name); ?>', '<?php echo htmlspecialchars($recruiter->email); ?>', '<?php echo !empty($recruiter->profile_pic) ? $recruiter->profile_pic : ''; ?>', '<?php echo htmlspecialchars($recruiter->first_name . ' ' . $recruiter->last_name); ?>')">
                                                    <div class="d-flex align-items-center">
                                                        <div class="recruiter-avatar mr-3">
                                                            <?php if (!empty($recruiter->profile_pic)): ?>
                                                            <img src="<?php echo image_url($recruiter->profile_pic); ?>"
                                                                class="rounded-circle" width="45" height="45"
                                                                alt="<?php echo htmlspecialchars($recruiter->first_name); ?>"
                                                                style="object-fit: cover;">
                                                            <?php else: ?>
                                                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center"
                                                                style="width: 45px; height: 45px; font-size: 1rem;">
                                                                <?php echo strtoupper(substr($recruiter->first_name, 0, 1) . substr($recruiter->last_name, 0, 1)); ?>
                                                            </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-1 recruiter-name">
                                                                <?php echo htmlspecialchars($recruiter->first_name . ' ' . $recruiter->last_name); ?>
                                                            </h6>
                                                            <small
                                                                class="text-muted d-block"><?php echo htmlspecialchars($recruiter->email); ?></small>
                                                            <?php if (isset($recruiter->has_conversation) && $recruiter->has_conversation > 0): ?>
                                                            <small class="text-success">
                                                                <i class="fa fa-comment"></i> Existing conversation
                                                            </small>
                                                            <?php else: ?>
                                                            <small class="text-muted">Click to start chat</small>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="ml-2">
                                                            <i class="fa fa-chevron-right text-muted"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php else: ?>
                        <div class="text-center p-4">
                            <i class="fa fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Recruiters Available</h5>
                            <p class="text-muted">There are no recruiters in your agency to start a conversation with.
                            </p>
                            <a href="<?php echo site_url('agency/chat'); ?>" class="btn btn-primary">
                                <i class="fa fa-arrow-left"></i> Back to Chat
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.recruiters-sidebar {
    border-left: 1px solid #dee2e6;
}

.recruiter-item {
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
}

.recruiter-item:hover {
    background-color: #f8f9fa;
    border-left-color: #007bff;
}

.recruiter-item.active {
    background-color: #e3f2fd;
    border-left-color: #007bff;
}

.chat-form-container {
    min-height: 500px;
    display: flex;
    flex-direction: column;
}

.recruiters-list {
    scrollbar-width: thin;
    scrollbar-color: #c1c1c1 #f1f1f1;
}

.recruiters-list::-webkit-scrollbar {
    width: 6px;
}

.recruiters-list::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.recruiters-list::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.recruiters-list::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

.selected-recruiter-info {
    border-left: 4px solid #007bff;
}

#defaultState {
    height: 400px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}
</style>

<script>
var selectedRecruiterId = null;

function selectRecruiter(recruiterId, recruiterName, recruiterEmail, profilePic, initials) {
    // Remove active class from all recruiter items
    $('.recruiter-item').removeClass('active');

    // Add active class to selected recruiter
    $(event.currentTarget).addClass('active');

    // Set the selected recruiter data
    selectedRecruiterId = recruiterId;
    $('#recruiter_id').val(recruiterId);
    $('#selectedRecruiterName').text(recruiterName);
    $('#selectedRecruiterEmail').text(recruiterEmail);

    // Update recruiter avatar
    var avatarContainer = $('#selectedRecruiterAvatar');
    if (profilePic) {
        avatarContainer.html(`<img src="<?php echo image_url('` + profilePic + `'); ?>" 
                                    class="rounded-circle" width="50" height="50" 
                                    style="object-fit: cover;" alt="` + recruiterName + `">`);
    } else {
        avatarContainer.html(initials.substring(0, 2).toUpperCase())
            .css({
                'width': '50px',
                'height': '50px',
                'font-size': '1.2rem'
            });
    }

    // Update form title and subtitle
    $('#formTitle').text('Start Conversation with ' + recruiterName);
    $('#formSubtitle').text('Send your first message to ' + recruiterName);

    // Show conversation form and hide default state
    $('#defaultState').hide();
    $('#conversationForm').slideDown();

    // Focus on the message textarea
    setTimeout(function() {
        $('#initial_message').focus();
    }, 300);
}

function cancelSelection() {
    // Hide conversation form and show default state
    $('#conversationForm').slideUp(function() {
        $('#defaultState').show();
    });

    // Remove active class from all recruiter items
    $('.recruiter-item').removeClass('active');

    // Clear the form
    $('#recruiter_id').val('');
    $('#initial_message').val('');
    selectedRecruiterId = null;

    // Reset form title
    $('#formTitle').text('Select a Recruiter to Start Chatting');
    $('#formSubtitle').text('Choose a recruiter from the right to begin your conversation');
}

$(document).ready(function() {
    $('#startConversationForm').on('submit', function(e) {
        e.preventDefault();

        var recruiterId = $('#recruiter_id').val();
        var initialMessage = $('#initial_message').val().trim();

        if (!recruiterId) {
            alert('Please select a recruiter');
            return;
        }

        if (initialMessage === '') {
            alert('Please enter a message');
            $('#initial_message').focus();
            return;
        }

        // Show loading state
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html(
            '<i class="fa fa-spinner fa-spin"></i> Starting Conversation...');

        $.ajax({
            url: '<?php echo site_url("agency/chat/ajax_start_conversation"); ?>',
            type: 'POST',
            data: {
                recruiter_id: recruiterId,
                initial_message: initialMessage
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Show success message
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Conversation started successfully!');
                    } else {
                        alert('Conversation started successfully!');
                    }

                    // Redirect to the conversation
                    setTimeout(function() {
                        window.location.href = response.redirect_url;
                    }, 1500);
                } else {
                    alert('Failed to start conversation: ' + response.message);
                    submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr, status, error) {
                alert('Error starting conversation. Please try again.');
                console.error('Error:', error);
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Add character counter for message textarea
    $('#initial_message').on('input', function() {
        var length = $(this).val().length;
        var counter = $(this).siblings('.char-counter');
        if (counter.length === 0) {
            $(this).after('<small class="form-text text-muted char-counter">Character count: ' +
                length + '</small>');
        } else {
            counter.text('Character count: ' + length);
        }
    });

    // Keyboard shortcuts
    $(document).keydown(function(e) {
        // Ctrl + Enter to submit form
        if (e.ctrlKey && e.keyCode === 13 && selectedRecruiterId) {
            $('#startConversationForm').submit();
        }
        // Escape to cancel selection
        if (e.keyCode === 27 && selectedRecruiterId) {
            cancelSelection();
        }
    });
});

// Load toastr if not already loaded
if (typeof toastr === 'undefined') {
    $.getScript('<?php echo site_url("resources/cms/plugins/theme/toastr/toastr.min.js"); ?>', function() {
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 3000
        };
    });
}
</script>