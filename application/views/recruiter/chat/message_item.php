<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div
    class="message-item mb-3 d-flex <?php echo $message->sender_type == 'recruiter' ? 'justify-content-end' : 'justify-content-start'; ?>">


    <?php if ($message->sender_type != 'recruiter'): ?>

    <?php endif; ?>

    <div class="message-container" style="max-width: 70%;">
        <div class="d-flex align-items-center mb-1">
            <?php if ($message->sender_type != 'recruiter'): ?>
            <strong
                style="color: #ffffff; font-size: 0.9rem; margin-right: 5px;"><?php echo htmlspecialchars($message->sender_name ?? 'User'); ?></strong>
            <small class="text-muted"
                style="font-size: 0.7rem;"><?php echo date('M j, Y h:i A', strtotime($message->created_at)); ?></small>
            <?php else: ?>
            <small class="text-muted"
                style="font-size: 0.7rem; margin-right: 5px;"><?php echo date('M j, Y h:i A', strtotime($message->created_at)); ?></small>
            <?php endif; ?>
        </div>
        <div class="message-bubble p-3"
            style="background-color: <?php echo $message->sender_type == 'recruiter' ? '#5865F2' : '#2f3136'; ?>; border-radius: 8px; color: #ffffff; font-size: 0.9rem; line-height: 1.4;">
            <?php echo nl2br(htmlspecialchars($message->message)); ?>
        </div>
        <?php if ($message->sender_type == 'recruiter'): ?>
        <div class="text-right mt-1">
            <i class="fa fa-check<?php echo $message->is_read ? '-double text-info' : ''; ?>"
                style="color: <?php echo $message->is_read ? '#3ba55c' : '#b9bbbe'; ?>;"></i>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($message->sender_type == 'recruiter'): ?>

    <?php endif; ?>

</div>