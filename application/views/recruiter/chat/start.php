<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div id="main-content">
    <div class="container-fluid">
        <div class="row clearfix">
            <div class="col-12">
                <div class="card">
                    <div class="header">
                        <h2><?php echo $heading; ?></h2>
                        <a href="<?php echo site_url('recruiter/chat'); ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to Chat
                        </a>
                    </div>
                    <div class="body">
                        <?php if (!empty($agencies)): ?>
                        <div class="row">
                            <!-- Chat Form (Left Side) -->
                            <div class="col-md-8 col-lg-9">
                                <div class="chat-form-container">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0" id="formTitle">Select an Agency to Start Chatting</h5>
                                            <small class="text-muted" id="formSubtitle">Choose an agency from the right
                                                to begin your conversation</small>
                                        </div>
                                        <div class="card-body">
                                            <!-- Default State - No Agency Selected -->
                                            <div id="defaultState" class="text-center p-5">
                                                <i class="fa fa-comments fa-4x text-muted mb-3"></i>
                                                <h5 class="text-muted">No Agency Selected</h5>
                                                <p class="text-muted">Please select an agency from the list on the right
                                                    to start a conversation.</p>
                                            </div>

                                            <!-- Conversation Form (Shows when agency is selected) -->
                                            <div class="conversation-form" id="conversationForm" style="display: none;">
                                                <div class="selected-agency-info mb-4 p-3 bg-light rounded">
                                                    <div class="d-flex align-items-center">
                                                        <div class="agency-avatar mr-3">
                                                            <div id="selectedAgencyAvatar"
                                                                class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center"
                                                                style="width: 50px; height: 50px; font-size: 1.2rem;">
                                                                <i class="fa fa-building"></i>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1" id="selectedAgencyName">Agency Name</h6>
                                                            <small class="text-muted"
                                                                id="selectedAgencyEmail">email@example.com</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <form id="startConversationForm">
                                                    <input type="hidden" id="agency_id" name="agency_id">

                                                    <div class="form-group">
                                                        <label for="initial_message" class="font-weight-bold">Your
                                                            Message</label>
                                                        <textarea class="form-control" id="initial_message"
                                                            name="initial_message" rows="6"
                                                            placeholder="Type your message here... What would you like to discuss?"
                                                            style="resize: none;" required></textarea>
                                                        <small class="form-text text-muted">
                                                            This will be the first message in your conversation.
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

                            <!-- Agencies List (Right Side) -->
                            <div class="col-md-4 col-lg-3">
                                <div class="agencies-sidebar">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">Available Agencies</h6>
                                            <small class="text-muted"><?php echo count($agencies); ?> agency(s)</small>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="agencies-list" style="max-height: 500px; overflow-y: auto;">
                                                <?php foreach ($agencies as $agency): ?>
                                                <div class="agency-item p-3 border-bottom" style="cursor: pointer;"
                                                    onclick="selectAgency(<?php echo $agency->id; ?>, '<?php echo htmlspecialchars($agency->name); ?>', '<?php echo htmlspecialchars($agency->email); ?>', '<?php echo !empty($agency->logo) ? $agency->logo : ''; ?>')">
                                                    <div class="d-flex align-items-center">
                                                        <div class="agency-avatar mr-3">
                                                            <?php if (!empty($agency->logo)): ?>
                                                            <img src="<?php echo image_url($agency->logo); ?>"
                                                                class="rounded-circle" width="45" height="45"
                                                                alt="<?php echo htmlspecialchars($agency->name); ?>"
                                                                style="object-fit: cover;">
                                                            <?php else: ?>
                                                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center"
                                                                style="width: 45px; height: 45px; font-size: 1rem;">
                                                                <?php echo strtoupper(substr($agency->name, 0, 2)); ?>
                                                            </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-1 agency-name">
                                                                <?php echo htmlspecialchars($agency->name); ?></h6>
                                                            <small
                                                                class="text-muted d-block"><?php echo htmlspecialchars($agency->email); ?></small>
                                                            <small class="text-muted">Click to start chat</small>
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
                            <i class="fa fa-building fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Agencies Available</h5>
                            <p class="text-muted">There are no agencies available to start a conversation with.</p>
                            <a href="<?php echo site_url('recruiter/chat'); ?>" class="btn btn-primary">
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
.agencies-sidebar {
    border-left: 1px solid #dee2e6;
}

.agency-item {
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
}

.agency-item:hover {
    background-color: #f8f9fa;
    border-left-color: #007bff;
}

.agency-item.active {
    background-color: #e3f2fd;
    border-left-color: #007bff;
}

.chat-form-container {
    min-height: 500px;
    display: flex;
    flex-direction: column;
}

.agencies-list {
    scrollbar-width: thin;
    scrollbar-color: #c1c1c1 #f1f1f1;
}

.agencies-list::-webkit-scrollbar {
    width: 6px;
}

.agencies-list::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.agencies-list::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.agencies-list::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

.selected-agency-info {
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
var selectedAgencyId = null;

function selectAgency(agencyId, agencyName, agencyEmail, logo) {
    // Remove active class from all agency items
    $('.agency-item').removeClass('active');

    // Add active class to selected agency
    $(event.currentTarget).addClass('active');

    // Set the selected agency data
    selectedAgencyId = agencyId;
    $('#agency_id').val(agencyId);
    $('#selectedAgencyName').text(agencyName);
    $('#selectedAgencyEmail').text(agencyEmail);

    // Update agency avatar
    var avatarContainer = $('#selectedAgencyAvatar');
    if (logo) {
        avatarContainer.html(`<img src="<?php echo image_url('` + logo + `'); ?>" 
                                 class="rounded-circle" width="50" height="50" 
                                 style="object-fit: cover;" alt="` + agencyName + `">`);
    } else {
        avatarContainer.html(agencyName.substring(0, 2).toUpperCase())
            .css({
                'width': '50px',
                'height': '50px',
                'font-size': '1.2rem'
            });
    }

    // Update form title and subtitle
    $('#formTitle').text('Start Conversation with ' + agencyName);
    $('#formSubtitle').text('Send your first message to ' + agencyName);

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

    // Remove active class from all agency items
    $('.agency-item').removeClass('active');

    // Clear the form
    $('#agency_id').val('');
    $('#initial_message').val('');
    selectedAgencyId = null;

    // Reset form title
    $('#formTitle').text('Select an Agency to Start Chatting');
    $('#formSubtitle').text('Choose an agency from the right to begin your conversation');
}

$(document).ready(function() {
    $('#startConversationForm').on('submit', function(e) {
        e.preventDefault();

        var agencyId = $('#agency_id').val();
        var initialMessage = $('#initial_message').val().trim();

        if (!agencyId) {
            alert('Please select an agency');
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
            url: '<?php echo site_url("recruiter/chat/ajax_start_conversation"); ?>',
            type: 'POST',
            data: {
                agency_id: agencyId,
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
});
</script>