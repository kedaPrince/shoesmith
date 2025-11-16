<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div class="message-item mb-3 <?php echo $message->sender_type == $current_user_type ? 'message-sent' : 'message-received'; ?>"
    data-message-id="<?php echo $message->id; ?>">
    <div class="message-bubble <?php echo $message->sender_type == $current_user_type ? 'sent' : 'received'; ?>">
        <div class="message-text"><?php echo htmlspecialchars($message->message); ?></div>
        <div class="message-time text-muted">
            <small><?php echo date('H:i', strtotime($message->created_at)); ?></small>
            <?php if ($message->sender_type == $current_user_type): ?>
            <i class="fa fa-check<?php echo $message->is_read ? '-double text-info' : ''; ?> ml-1"></i>
            <?php endif; ?>
        </div>
    </div>
</div>