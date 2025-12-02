<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div class="d-flex <?php echo $message->sender_type == 'agency' ? 'justify-content-end' : 'justify-content-start'; ?> mb-2"
    data-message-id="<?php echo $message->id; ?>">

    <div class="message-bubble <?php echo $message->sender_type == 'agency' ? 'sent' : 'received'; ?>"
        style="max-width: 65%; position: relative;">

        <div class="message-content">
            <!-- Message text -->
            <div class="message-text" style="font-size: 14.2px; color: #303030; line-height: 1.28; 
                font-family: 'Segoe UI', 'Helvetica Neue', sans-serif; word-wrap: break-word;
                padding: 6px 7px 0px 9px;">
                <?php echo nl2br(htmlspecialchars($message->message)); ?>
            </div>

            <!-- Message metadata -->
            <div class="message-meta d-flex justify-content-end align-items-center"
                style="padding: 2px 7px 4px 9px; min-height: 15px;">

                <small class="message-time" style="font-size: 11px; color: #667781; white-space: nowrap;">
                    <?php echo date('g:i A', strtotime($message->created_at)); ?>
                </small>

                <?php if ($message->sender_type == 'agency'): ?>
                <span class="message-status" style="margin-left: 4px; display: inline-flex; align-items: center;">
                    <i class="fa fa-check<?php echo $message->is_read ? '-double' : ''; ?>"
                        style="font-size: 10px; color: <?php echo $message->is_read ? '#128C7E' : '#667781'; ?>;"></i>
                </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- WhatsApp-style tail -->
        <?php if ($message->sender_type == 'agency'): ?>
        <div class="bubble-tail" style="position: absolute; bottom: 0; right: -8px; width: 0; height: 0;
            border-left: 8px solid #dcf8c6; border-top: 8px solid transparent; border-bottom: 8px solid transparent;">
        </div>
        <?php else: ?>
        <div class="bubble-tail" style="position: absolute; bottom: 0; left: -8px; width: 0; height: 0;
            border-right: 8px solid #ffffff; border-top: 8px solid transparent; border-bottom: 8px solid transparent;">
        </div>
        <?php endif; ?>
    </div>
</div>